<script lang="ts">
    import AppHead from '@/components/AppHead.svelte';
    import { setLayoutProps } from '@inertiajs/svelte';
    import Plus from '@lucide/svelte/icons/plus';
    import { DataTable, type DataTableMeta } from '@/components/data-table';
    import { create, show } from '@/routes/erp/companies';
    import { companyColumns, type CompanyRow } from './columns';

    let { rows, meta }: { rows: CompanyRow[]; meta: DataTableMeta } = $props();

    let table: DataTable<CompanyRow>;

    const columns = $derived(companyColumns(meta, (column) => table?.sortBy(column)));

    $effect(() => {
        setLayoutProps({
            actions: [
                { label: 'Neu', icon: Plus, variant: 'default', href: create().url },
            ],
        });
    });
</script>

<AppHead title="Firmen" />

<DataTable
    bind:this={table}
    {columns}
    {rows}
    {meta}
    rowHref={(row) => show(row.id).url}
    searchPlaceholder="Firma, Kundennummer oder Adresse …"
    emptyMessage="Keine Firmen gefunden."
/>
