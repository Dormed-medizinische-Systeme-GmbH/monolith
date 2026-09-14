<script lang="ts">
    import AppHead from '@/components/AppHead.svelte';
    import Heading from '@/components/Heading.svelte';
    import { DataTable, type DataTableMeta } from '@/components/data-table';
    import { contactColumns, type ContactRow } from './columns';

    let { rows, meta }: { rows: ContactRow[]; meta: DataTableMeta } = $props();

    let table: DataTable<ContactRow>;

    const columns = $derived(contactColumns(meta, (column) => table?.sortBy(column)));
</script>

<AppHead title="Kontakte" />

<Heading
    title="Kontakte"
    description="Personen aus dem Kundenstamm und der Zustand ihres Portalzugangs."
/>

<DataTable
    bind:this={table}
    {columns}
    {rows}
    {meta}
    searchPlaceholder="Name, Firma oder Zugangs-Mail …"
    emptyMessage="Keine Kontakte gefunden."
/>
