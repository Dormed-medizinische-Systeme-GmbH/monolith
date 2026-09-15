<script lang="ts">
    import { Link } from '@inertiajs/svelte';
    import { Button, buttonVariants } from '@/components/ui/button';
    import { Separator } from '@/components/ui/separator';
    import { cn } from '@/lib/utils';
    import * as Sidebar from '@/components/ui/sidebar';
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
            {#each actions as action (action.label)}
                {#if action.href}
                    <Link
                        href={action.href}
                        class={buttonVariants({
                            variant: action.variant ?? 'outline',
                            size: 'sm',
                        })}
                    >
                        {#if action.icon}
                            <action.icon class="size-4" />
                        {/if}
                        {action.label}
                    </Link>
                {:else}
                    <Button
                        variant={action.variant ?? 'outline'}
                        size={action.iconOnly ? 'icon-sm' : 'sm'}
                        class={cn(
                            action.destructive && 'text-destructive hover:text-destructive',
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
    {/if}
</header>
