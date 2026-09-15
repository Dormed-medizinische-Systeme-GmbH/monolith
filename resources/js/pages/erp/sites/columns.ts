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
import { show } from '@/routes/erp/sites';
import ActiveBadge from './ActiveBadge.svelte';

export type SiteRow = {
    id: string;
    name: string;
    shortName: string | null;
    addressLine: string | null;
    city: string | null;
    userCount: number;
    isActive: boolean;
    photoUrl: string | null;
};

const helper = createColumnHelper<DataTableFeatures, SiteRow>();

export function siteColumns(
    meta: DataTableMeta,
    onSort: (column: string) => void,
): ColumnDef<DataTableFeatures, SiteRow, unknown>[] {
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
            header: () => sortable('name', 'Standort'),
            cell: ({ row }) =>
                renderComponent(DataTableLeadCell, {
                    href: show(row.original.id).url,
                    label: row.original.name,
                    sublabel: row.original.addressLine,
                }),
        }),
        helper.accessor('userCount', {
            header: () => sortable('users', 'Mitarbeiter'),
            cell: ({ row }) => String(row.original.userCount),
        }),
        helper.display({
            id: 'status',
            header: () => 'Status',
            cell: ({ row }) =>
                renderComponent(ActiveBadge, { active: row.original.isActive }),
        }),
    ]);
}
