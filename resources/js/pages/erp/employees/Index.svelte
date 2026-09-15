<script lang="ts">
    import { Link } from '@inertiajs/svelte';
    import Plus from '@lucide/svelte/icons/plus';
    import AppHead from '@/components/AppHead.svelte';
    import Heading from '@/components/Heading.svelte';
    import { DataTable, type DataTableMeta } from '@/components/data-table';
    import { buttonVariants } from '@/components/ui/button';
    import { create, show } from '@/routes/erp/employees';
    import { employeeColumns, type EmployeeRow } from './columns';

    let { rows, meta }: { rows: EmployeeRow[]; meta: DataTableMeta } = $props();

    let table: DataTable<EmployeeRow>;

    const columns = $derived(employeeColumns(meta, (column) => table?.sortBy(column)));
</script>

<AppHead title="Mitarbeiter" />

<div class="flex items-start justify-between gap-4">
    <Heading
        title="Mitarbeiter"
        description="Zugänge werden ausschließlich hier angelegt — es gibt keine Registrierung (D-032)."
    />

    <Link href={create().url} class={buttonVariants({ size: 'sm' })}>
        <Plus class="size-4" />
        Neu
    </Link>
</div>

<DataTable
    bind:this={table}
    {columns}
    {rows}
    {meta}
    rowHref={(row) => show(row.id).url}
    searchPlaceholder="Name, E-Mail oder Rolle …"
    emptyMessage="Keine Mitarbeiter gefunden."
/>
