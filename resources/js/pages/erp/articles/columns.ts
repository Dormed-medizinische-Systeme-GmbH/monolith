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
import { show } from '@/routes/erp/articles';
import PublicationBadges from './PublicationBadges.svelte';

export type ArticleRow = {
    id: string;
    name: string;
    articleNumber: string;
    group: string | null;
    manufacturer: string | null;
    price: string | null;
    unit: string;
    isActive: boolean;
    isPublic: boolean;
    isOrderable: boolean;
    isSerialTracked: boolean;
};

const helper = createColumnHelper<DataTableFeatures, ArticleRow>();

/**
 * Nur die Spalten — Suche, Sortierung, Auswahl und Seitenaufteilung kommen aus
 * `components/data-table`.
 *
 * Die Schlüssel (`name`, `number`, …) müssen zur Freigabeliste in
 * `ArticleList::SORTABLE` passen.
 */
export function articleColumns(
    meta: DataTableMeta,
    onSort: (column: string) => void,
): ColumnDef<DataTableFeatures, ArticleRow, unknown>[] {
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
            header: () => sortable('name', 'Artikel'),
            cell: ({ row }) =>
                renderComponent(DataTableLeadCell, {
                    href: show(row.original.id).url,
                    label: row.original.name,
                    sublabel: row.original.articleNumber,
                }),
        }),
        helper.accessor('group', {
            header: () => sortable('group', 'Gruppe'),
            cell: ({ row }) => row.original.group ?? '—',
        }),
        helper.accessor('manufacturer', {
            header: () => sortable('manufacturer', 'Hersteller'),
            cell: ({ row }) => row.original.manufacturer ?? '—',
        }),
        helper.accessor('price', {
            header: () => sortable('price', 'Verkauf'),
            cell: ({ row }) =>
                `${row.original.price ?? '—'} / ${row.original.unit}`,
        }),
        helper.display({
            id: 'publication',
            header: () => 'Sichtbar',
            cell: ({ row }) =>
                renderComponent(PublicationBadges, {
                    active: row.original.isActive,
                    isPublic: row.original.isPublic,
                    orderable: row.original.isOrderable,
                }),
        }),
    ]);
}
