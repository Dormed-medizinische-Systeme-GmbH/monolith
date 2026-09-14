<script lang="ts">
    import { Button } from '@/components/ui/button';
    import type { DataTableMeta } from './data-table-features';

    /**
     * Seitenaufteilung für alle Listen. Zählt bewusst Datensätze und nicht
     * Seiten: „26–50 von 1.284" beantwortet die Frage, die man beim Blättern
     * tatsächlich hat.
     */
    let {
        meta,
        onNavigate,
        selectedCount = 0,
    }: {
        meta: DataTableMeta;
        onNavigate: (params: Record<string, string | number>) => void;
        selectedCount?: number;
    } = $props();

    const zahl = new Intl.NumberFormat('de-DE');
</script>

<div class="flex items-center justify-between gap-4">
    <p class="text-sm text-muted-foreground">
        {#if selectedCount > 0}
            <!--
                Die Auswahl gilt ueber Seitengrenzen hinweg, deshalb wird sie
                gegen die Gesamtzahl gezaehlt und nicht gegen die 25 Zeilen,
                die gerade sichtbar sind.
            -->
            {zahl.format(selectedCount)} von {zahl.format(meta.total)} ausgewählt
        {:else if meta.total === 0}
            Keine Einträge
        {:else}
            {zahl.format(meta.from ?? 0)}–{zahl.format(meta.to ?? 0)} von
            {zahl.format(meta.total)}
        {/if}
    </p>

    <div class="flex items-center gap-2">
        <Button
            variant="outline"
            size="sm"
            disabled={meta.page <= 1}
            onclick={() => onNavigate({ page: meta.page - 1 })}
        >
            Zurück
        </Button>

        <span class="text-sm text-muted-foreground">
            Seite {zahl.format(meta.page)} von {zahl.format(Math.max(meta.lastPage, 1))}
        </span>

        <Button
            variant="outline"
            size="sm"
            disabled={meta.page >= meta.lastPage}
            onclick={() => onNavigate({ page: meta.page + 1 })}
        >
            Weiter
        </Button>
    </div>
</div>
