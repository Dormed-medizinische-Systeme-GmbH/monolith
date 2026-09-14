<script lang="ts">
    import * as Sidebar from '@/components/ui/sidebar';
    import { Separator } from '@/components/ui/separator';

    /**
     * Die Kopfzeile über dem Inhalt: Umschalter für die Seitenleiste und der
     * Name der aktuellen Fläche. Der Titel kommt von der Seite, nicht aus einer
     * zweiten Zuordnung von Pfad zu Bezeichnung.
     */
    let { title }: { title?: string } = $props();
</script>

<header
    class="flex h-14 shrink-0 items-center gap-2 border-b bg-background px-4 transition-[width,height] ease-linear"
>
    <Sidebar.Trigger class="-ms-1" />
    {#if title}
        <Separator orientation="vertical" class="mx-2 data-[orientation=vertical]:h-4" />
        <span class="text-sm font-medium">{title}</span>
    {/if}
</header>
