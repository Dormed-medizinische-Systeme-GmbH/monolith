<script lang="ts">
    import { setLayoutProps } from '@inertiajs/svelte';
    import Plus from '@lucide/svelte/icons/plus';
    import AppHead from '@/components/AppHead.svelte';
    import { DataTable, type DataTableMeta } from '@/components/data-table';
    import { create, show } from '@/routes/erp/sites';
    import { siteColumns, type SiteRow } from './columns';

    let { rows, meta }: { rows: SiteRow[]; meta: DataTableMeta } = $props();

    let table: DataTable<SiteRow>;

    const columns = $derived(siteColumns(meta, (column) => table?.sortBy(column)));

    $effect(() => {
        setLayoutProps({
            actions: [
                { label: 'Neu', icon: Plus, variant: 'default', href: create().url },
            ],
        });
    });
</script>

<AppHead title="Betriebsstätten" />

<DataTable
    bind:this={table}
    {columns}
    {rows}
    {meta}
    rowHref={(row) => show(row.id).url}
    searchPlaceholder="Name, Straße oder Ort …"
    emptyMessage="Keine Betriebsstätten gefunden."
/>
