<script lang="ts">
    import { setLayoutProps } from '@inertiajs/svelte';
    import Plus from '@lucide/svelte/icons/plus';
    import AppHead from '@/components/AppHead.svelte';
    import { DataTable, type DataTableMeta } from '@/components/data-table';
    import { create, show } from '@/routes/erp/employees';
    import { employeeColumns, type EmployeeRow } from './columns';

    /**
     * Keine eigene Überschrift: der Name der Fläche steht bereits in der
     * App-Kopfzeile, und „Neu" steht dort, wo jede Aktion steht. Die Suchleiste
     * beginnt damit direkt unter der Kopfzeile.
     */
    let { rows, meta }: { rows: EmployeeRow[]; meta: DataTableMeta } = $props();

    let table: DataTable<EmployeeRow>;

    const columns = $derived(employeeColumns(meta, (column) => table?.sortBy(column)));

    $effect(() => {
        setLayoutProps({
            actions: [
                { label: 'Neu', icon: Plus, variant: 'default', href: create().url },
            ],
        });
    });
</script>

<AppHead title="Mitarbeiter" />

<DataTable
    bind:this={table}
    {columns}
    {rows}
    {meta}
    rowHref={(row) => show(row.id).url}
    searchPlaceholder="Name, E-Mail, Rolle oder Standort …"
    emptyMessage="Keine Mitarbeiter gefunden."
/>
