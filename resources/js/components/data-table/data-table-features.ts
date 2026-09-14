import {
    createSortedRowModel,
    rowSortingFeature,
    sortFn_alphanumeric,
    sortFn_text,
    tableFeatures,
} from '@tanstack/svelte-table';

/**
 * Die Fähigkeiten, die jede Liste im ERP hat.
 *
 * Was hier nicht steht, landet auch nicht im Bundle — TanStack schüttelt
 * Nichtregistriertes heraus. Deshalb ist die Liste kurz und wächst nur, wenn
 * eine Fläche etwas wirklich braucht.
 *
 * Bewusst NICHT dabei: Filterung und Seitenaufteilung im Browser. Beides
 * passiert auf dem Server (siehe data-table.svelte) — der Adressstamm hat rund
 * 50.000 Personen, die lädt man nicht am Stück, um dann clientseitig zu
 * filtern.
 *
 * Sortierung bleibt registriert, weil TanStack den Zustand verwaltet und die
 * Pfeile in den Kopfzeilen daraus kommen; sortiert wird trotzdem in Postgres.
 */
export const features = tableFeatures({
    rowSortingFeature,
    sortedRowModel: createSortedRowModel(),
    sortFns: {
        alphanumeric: sortFn_alphanumeric,
        text: sortFn_text,
    },
});

export type DataTableFeatures = typeof features;

/**
 * Was der Server zu jeder Liste mitschickt.
 */
export type DataTableMeta = {
    sort: string;
    direction: 'asc' | 'desc';
    search: string;
    page: number;
    lastPage: number;
    perPage: number;
    total: number;
    from: number | null;
    to: number | null;
};
