/**
 * Die zentrale Listenmechanik des ERP.
 *
 * Eine Fläche bringt nur ihre Spalten und ihren Datensatz mit — Suche,
 * Sortierung, Seitenaufteilung und das Erscheinungsbild kommen von hier.
 */
export { default as DataTable } from './data-table.svelte';
export { default as DataTablePagination } from './data-table-pagination.svelte';
export { default as DataTableSortButton } from './data-table-sort-button.svelte';
export { features, type DataTableFeatures, type DataTableMeta } from './data-table-features';
