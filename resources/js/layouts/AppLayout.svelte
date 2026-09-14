<script lang="ts">
    import type { Snippet } from 'svelte';

    /**
     * Vorläufige Hülle für die angemeldeten Flächen.
     *
     * Die Sidebar-Shell des Starter-Kits ist entfallen: sie war Gerüst, hing an
     * der alten shadcn-API und wird vom ERP-Layout ohnehin ersetzt. Bis dieses
     * entworfen ist, genügt ein zentrierter Rahmen — Navigation gibt es noch
     * keine, weil es noch nichts zu navigieren gibt.
     */
    let { children }: { children?: Snippet } = $props();
</script>

<div class="min-h-svh bg-background">
    <div class="mx-auto w-full max-w-5xl p-6 md:p-10">
        {@render children?.()}
    </div>
</div>
