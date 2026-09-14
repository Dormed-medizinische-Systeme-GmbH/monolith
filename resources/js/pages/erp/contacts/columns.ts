import {
    createColumnHelper,
    renderComponent,
    type ColumnDef,
} from '@tanstack/svelte-table';
import { DataTableSortButton, type DataTableFeatures, type DataTableMeta } from '@/components/data-table';
import AccessBadge from './AccessBadge.svelte';

export type ContactRow = {
    id: number;
    name: string;
    nameSuffix: string | null;
    company: string | null;
    role: string | null;
    accountEmail: string | null;
    accountActive: boolean;
};

const helper = createColumnHelper<DataTableFeatures, ContactRow>();

/**
 * Nur die Spalten — Suche, Sortierung und Seitenaufteilung kommen aus
 * `components/data-table`. Genau das ist die Aufteilung: hier der Datensatz,
 * dort das Verhalten.
 *
 * Die Schlüssel (`name`, `company`, `role`, `access`) müssen zur Freigabeliste
 * im ContactController passen — was dort nicht steht, lässt sich nicht
 * sortieren.
 */
export function contactColumns(
    meta: DataTableMeta,
    onSort: (column: string) => void,
): ColumnDef<DataTableFeatures, ContactRow, unknown>[] {
    const sortable = (id: string, label: string) =>
        renderComponent(DataTableSortButton, {
            label,
            column: id,
            activeColumn: meta.sort,
            direction: meta.direction,
            onSort,
        });

    return helper.columns([
        helper.accessor('name', {
            header: () => sortable('name', 'Name'),
            cell: ({ row }) =>
                [row.original.nameSuffix, row.original.name]
                    .filter(Boolean)
                    .join(' '),
        }),
        helper.accessor('company', {
            header: () => sortable('company', 'Firma'),
            cell: ({ row }) => row.original.company ?? '—',
        }),
        helper.accessor('role', {
            header: () => sortable('role', 'Rolle'),
            cell: ({ row }) => row.original.role ?? '—',
        }),
        helper.display({
            id: 'access',
            header: () => sortable('access', 'Portal-Zugang'),
            cell: ({ row }) =>
                renderComponent(AccessBadge, {
                    email: row.original.accountEmail,
                    active: row.original.accountActive,
                }),
        }),
    ]);
}
