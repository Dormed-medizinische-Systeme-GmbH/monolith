<script lang="ts">
    import AppHead from '@/components/AppHead.svelte';
    import Heading from '@/components/Heading.svelte';
    import { DataTable, type DataTableMeta } from '@/components/data-table';
    import { show } from '@/routes/erp/articles';
    import { articleColumns, type ArticleRow } from './columns';

    let { rows, meta }: { rows: ArticleRow[]; meta: DataTableMeta } = $props();

    let table: DataTable<ArticleRow>;

    const columns = $derived(articleColumns(meta, (column) => table?.sortBy(column)));
</script>

<AppHead title="Artikel" />

<Heading
    title="Artikel"
    description="Der Katalog. Bestand und Exemplare kommen mit dem Lager."
/>

<DataTable
    bind:this={table}
    {columns}
    {rows}
    {meta}
    rowHref={(row) => show(row.id).url}
    searchPlaceholder="Artikel, Nummer, Hersteller oder EAN …"
    emptyMessage="Keine Artikel gefunden."
/>
