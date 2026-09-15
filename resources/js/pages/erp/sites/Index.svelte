<script lang="ts">
    import { Link } from '@inertiajs/svelte';
    import Plus from '@lucide/svelte/icons/plus';
    import AppHead from '@/components/AppHead.svelte';
    import Heading from '@/components/Heading.svelte';
    import { DataTable, type DataTableMeta } from '@/components/data-table';
    import { buttonVariants } from '@/components/ui/button';
    import { create, show } from '@/routes/erp/sites';
    import { siteColumns, type SiteRow } from './columns';

    let { rows, meta }: { rows: SiteRow[]; meta: DataTableMeta } = $props();

    let table: DataTable<SiteRow>;

    const columns = $derived(siteColumns(meta, (column) => table?.sortBy(column)));
</script>

<AppHead title="Betriebsstätten" />

<div class="flex items-start justify-between gap-4">
    <Heading
        title="Betriebsstätten"
        description="Die eigenen Standorte von Dormed — nicht die der Kunden (D-007)."
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
    searchPlaceholder="Name, Kürzel oder Ort …"
    emptyMessage="Keine Betriebsstätten gefunden."
/>
