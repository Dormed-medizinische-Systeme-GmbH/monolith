import {
    createColumnHelper,
    renderComponent,
    type ColumnDef,
} from '@tanstack/svelte-table';
import {
    DataTableLeadCell,
    DataTableSortButton,
    type DataTableFeatures,
    type DataTableMeta,
} from '@/components/data-table';
import { show } from '@/routes/erp/companies';

export type CompanyRow = {
    id: string;
    name: string;
    nameAddition: string | null;
    specialty: string | null;
    street: string | null;
    city: string | null;
    avvSigned: boolean;
};

const helper = createColumnHelper<DataTableFeatures, CompanyRow>();

/**
 * Nur die Spalten — Suche, Sortierung und Seitenaufteilung kommen aus
 * `components/data-table`. Genau das ist die Aufteilung: hier der Datensatz,
 * dort das Verhalten.
 *
 * Die Schlüssel (`name`, `specialty`, …) müssen zur Freigabeliste in
 * `CompanyList::SORTABLE` passen — was dort nicht steht, lässt sich nicht
 * sortieren.
 */
export function companyColumns(
    meta: DataTableMeta,
    onSort: (column: string) => void,
): ColumnDef<DataTableFeatures, CompanyRow, unknown>[] {
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
            header: () => sortable('name', 'Firma'),
            cell: ({ row }) =>
                renderComponent(DataTableLeadCell, {
                    href: show(row.original.id).url,
                    label: row.original.name,
                    sublabel: row.original.nameAddition,
                }),
        }),
        helper.accessor('specialty', {
            header: () => sortable('specialty', 'Fachrichtung'),
            cell: ({ row }) => row.original.specialty ?? '—',
        }),
        helper.accessor('street', {
            header: () => sortable('street', 'Straße'),
            cell: ({ row }) => row.original.street ?? '—',
        }),
        helper.accessor('city', {
            header: () => sortable('city', 'Ort'),
            cell: ({ row }) => row.original.city ?? '—',
        }),
    ]);
}
