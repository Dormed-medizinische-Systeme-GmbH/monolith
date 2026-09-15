<script lang="ts">
    import { Link } from '@inertiajs/svelte';
    import * as Avatar from '@/components/ui/avatar';
    import { Button, buttonVariants } from '@/components/ui/button';
    import * as Select from '@/components/ui/select';
    import { Separator } from '@/components/ui/separator';
    import * as Sidebar from '@/components/ui/sidebar';
    import { cn } from '@/lib/utils';
    import type { PageAction, PagePicker } from './page-actions';

    /**
     * Die Kopfzeile über dem Inhalt: Umschalter für die Seitenleiste, der Name
     * der aktuellen Fläche und rechts deren Aktionen.
     *
     * Die Aktionen stehen hier und nicht in der Seite, damit sie immer an
     * derselben Stelle sitzen — unabhängig davon, wie weit man gescrollt hat
     * und wie die Fläche darunter aufgebaut ist.
     */
    let {
        title,
        actions = [],
        picker,
    }: { title?: string; actions?: PageAction[]; picker?: PagePicker } = $props();

    const gewaehlt = $derived(picker?.options.find((o) => o.value === picker?.value) ?? null);

    function initialen(label: string): string {
        return label
            .split(' ')
            .map((teil) => teil[0] ?? '')
            .slice(0, 2)
            .join('')
            .toUpperCase();
    }

    /**
     * Aufeinanderfolgende Aktionen mit demselben Gruppennamen bilden eine
     * Schaltergruppe. Bewusst nur AUFEINANDERFOLGENDE: so bestimmt die
     * Reihenfolge in der Seite, was zusammengehört, und eine Gruppe kann nicht
     * versehentlich quer durch die Leiste zusammengezogen werden.
     */
    type Segment = { gruppe: string | null; eintraege: PageAction[] };

    const segmente = $derived.by(() => {
        const liste: Segment[] = [];

        for (const action of actions) {
            const letztes = liste.at(-1);

            if (letztes && action.group && letztes.gruppe === action.group) {
                letztes.eintraege.push(action);
            } else {
                liste.push({ gruppe: action.group ?? null, eintraege: [action] });
            }
        }

        return liste;
    });

    /** Kanten glätten, wo Schalter aneinanderstoßen. */
    function kanten(index: number, anzahl: number): string | undefined {
        if (anzahl < 2) {
            return undefined;
        }

        if (index === 0) {
            return 'rounded-e-none';
        }

        if (index === anzahl - 1) {
            return '-ms-px rounded-s-none';
        }

        return '-ms-px rounded-none';
    }
</script>

<header
    class="flex h-14 shrink-0 items-center gap-2 border-b bg-background px-4 transition-[width,height] ease-linear"
>
    <Sidebar.Trigger class="-ms-1" />
    {#if title}
        <Separator orientation="vertical" class="mx-2 data-[orientation=vertical]:h-4" />
        <span class="text-sm font-medium">{title}</span>
    {/if}

    <div class="ms-auto flex items-center gap-2">
        {#if picker}
            <Select.Root
                type="single"
                value={picker.value}
                onValueChange={(v) => picker?.onSelect(v ?? '')}
            >
                <Select.Trigger size="sm" class="w-52">
                    {#if gewaehlt}
                        <span class="flex items-center gap-2">
                            <Avatar.Root class="size-5">
                                {#if gewaehlt.imageUrl}
                                    <Avatar.Image
                                        src={gewaehlt.imageUrl}
                                        alt={gewaehlt.label}
                                    />
                                {/if}
                                <Avatar.Fallback class="text-[9px]">
                                    {initialen(gewaehlt.label)}
                                </Avatar.Fallback>
                            </Avatar.Root>
                            {gewaehlt.label}
                        </span>
                    {:else}
                        <span class="text-muted-foreground">{picker.placeholder}</span>
                    {/if}
                </Select.Trigger>

                <Select.Content align="end">
                    <Select.Item value="" label={picker.placeholder}>
                        {picker.placeholder}
                    </Select.Item>
                    {#each picker.options as option (option.value)}
                        <Select.Item value={option.value} label={option.label}>
                            <span class="flex items-center gap-2">
                                <Avatar.Root class="size-6">
                                    {#if option.imageUrl}
                                        <Avatar.Image
                                            src={option.imageUrl}
                                            alt={option.label}
                                        />
                                    {/if}
                                    <Avatar.Fallback class="text-[10px]">
                                        {initialen(option.label)}
                                    </Avatar.Fallback>
                                </Avatar.Root>
                                {option.label}
                            </span>
                        </Select.Item>
                    {/each}
                </Select.Content>
            </Select.Root>
        {/if}

        {#if actions.length > 0}
            {#each segmente as segment, s (segment.gruppe ?? s)}
                <div class="flex items-center">
                    {#each segment.eintraege as action, i (action.label)}
                        {@const rand = kanten(i, segment.eintraege.length)}
                        {#if action.href}
                            <Link
                                href={action.href}
                                class={cn(
                                    buttonVariants({
                                        variant: action.variant ?? 'outline',
                                        size: action.iconOnly ? 'icon-sm' : 'sm',
                                    }),
                                    action.destructive &&
                                        'border-destructive text-destructive hover:border-destructive hover:bg-destructive/10 hover:text-destructive',
                                    rand,
                                )}
                                aria-label={action.iconOnly ? action.label : undefined}
                            >
                                {#if action.icon}
                                    <action.icon class="size-4" />
                                {/if}
                                {#if !action.iconOnly}
                                    {action.label}
                                {/if}
                            </Link>
                        {:else}
                            <Button
                                variant={action.variant ?? 'outline'}
                                size={action.iconOnly ? 'icon-sm' : 'sm'}
                                class={cn(
                                    action.destructive &&
                                        'border-destructive text-destructive hover:border-destructive hover:bg-destructive/10 hover:text-destructive',
                                    rand,
                                )}
                                aria-label={action.iconOnly ? action.label : undefined}
                                onclick={action.onSelect}
                            >
                                {#if action.icon}
                                    <action.icon class="size-4" />
                                {/if}
                                {#if !action.iconOnly}
                                    {action.label}
                                {/if}
                            </Button>
                        {/if}
                    {/each}
                </div>
            {/each}
        {/if}
    </div>
</header>
