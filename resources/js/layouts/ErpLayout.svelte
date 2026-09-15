<script lang="ts">
    import type { Snippet } from 'svelte';
    import { page } from '@inertiajs/svelte';
    import * as Sidebar from '@/components/ui/sidebar';
    import { Toaster } from '@/components/ui/sonner';
    import AppHeader from './erp/AppHeader.svelte';
    import AppSidebar from './erp/AppSidebar.svelte';
    import CommandPalette from './erp/CommandPalette.svelte';
    import { isCurrent, navigation } from './erp/navigation';
    import type { PageAction } from './erp/page-actions';

    /**
     * Die Hülle aller ERP-Flächen: Seitenleiste, Kopfzeile, Inhalt.
     *
     * Portal und Shop bekommen später eine eigene — ihre Navigation sieht
     * anders aus, und ein Layout, das beides kann, könnte am Ende keins von
     * beidem richtig. Die Zuordnung Seite → Layout steht in `app.ts`.
     */
    let {
        children,
        // Kommt aus der Seite über `setLayoutProps` (siehe `erp/page-actions.ts`).
        actions = [],
    }: { children?: Snippet; actions?: PageAction[] } = $props();

    let searchOpen = $state(false);

    /*
     * Der Zustand der Seitenleiste steht in einem Cookie und wird vom Server
     * mitgeliefert (HandleInertiaRequests). Sonst klappte sie bei jedem
     * Seitenwechsel kurz auf, bevor der Browser den Cookie ausgewertet hat.
     */
    const sidebarOpen = $derived((page.props.sidebarOpen as boolean | undefined) ?? true);

    /** Der Abschnitt, in dem wir uns befinden — aus derselben Navigation. */
    const section = $derived(
        navigation
            .flatMap((group) => group.items)
            .find((item) => isCurrent(item.href, page.url))?.title,
    );

    function onKeydown(event: KeyboardEvent): void {
        if (event.key === 'k' && (event.metaKey || event.ctrlKey)) {
            event.preventDefault();
            searchOpen = !searchOpen;
        }
    }
</script>

<svelte:window onkeydown={onKeydown} />

<!--
    Schmaler als die 16rem der Vorlage. Die laengste Beschriftung ist
    „Verkaufschancen"; alles darueber hinaus waere Rand, der dem Inhalt fehlt.
    Die Breite gehoert an diese Huelle und nicht in `components/ui` — dort
    stehen die Werte der Vorlage, und die sollen bleiben, wo sie sind.
-->
<Sidebar.Provider open={sidebarOpen} style="--sidebar-width: 14rem;">
    <AppSidebar onOpenSearch={() => (searchOpen = true)} />

    <Sidebar.Inset>
        <AppHeader title={section} {actions} />

        <div class="flex flex-1 flex-col gap-4 p-4 md:p-6">
            {@render children?.()}
        </div>
    </Sidebar.Inset>
</Sidebar.Provider>

<CommandPalette bind:open={searchOpen} />

<!--
    Ohne diesen Empfänger liefen die Rückmeldungen ins Leere: `app.ts` horcht
    seit jeher auf Inertias `flash`-Ereignis und ruft `toast()` auf, aber
    gerendert hat das niemand. `theme="light"` fest, es gibt keinen Dark Mode
    (ADR-044).
-->
<Toaster theme="light" richColors closeButton />
