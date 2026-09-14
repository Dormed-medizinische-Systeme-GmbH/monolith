<script lang="ts">
    import AppHead from '@/components/AppHead.svelte';
    import Heading from '@/components/Heading.svelte';
    import { DataTable, type DataTableMeta } from '@/components/data-table';
    import { show } from '@/routes/erp/companies';
    import { companyColumns, type CompanyRow } from './columns';

    let { rows, meta }: { rows: CompanyRow[]; meta: DataTableMeta } = $props();

    let table: DataTable<CompanyRow>;

    const columns = $derived(companyColumns(meta, (column) => table?.sortBy(column)));
</script>

<AppHead title="Firmen" />

<Heading
    title="Firmen"
    description="Der Kundenstamm. Eine Zeile ist eine Firma — die Ansprechpartner stehen in der Firma."
/>

<DataTable
    bind:this={table}
    {columns}
    {rows}
    {meta}
    rowHref={(row) => show(row.id).url}
    searchPlaceholder="Firma, Kundennummer oder Adresse …"
    emptyMessage="Keine Firmen gefunden."
/>
