<script lang="ts">
    import { Link } from '@inertiajs/svelte';
    import { Button, buttonVariants } from '@/components/ui/button';
    import { Separator } from '@/components/ui/separator';
    import * as Sidebar from '@/components/ui/sidebar';
    import { cn } from '@/lib/utils';
    import type { PageAction } from './page-actions';

    /**
     * Die Kopfzeile über dem Inhalt: Umschalter für die Seitenleiste, der Name
     * der aktuellen Fläche und rechts deren Aktionen.
     *
     * Die Aktionen stehen hier und nicht in der Seite, damit sie immer an
     * derselben Stelle sitzen — unabhängig davon, wie weit man gescrollt hat
     * und wie die Fläche darunter aufgebaut ist.
     */
    let { title, actions = [] }: { title?: string; actions?: PageAction[] } = $props();

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

    {#if actions.length > 0}
        <div class="ms-auto flex items-center gap-2">
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
        </div>
    {/if}
</header>
