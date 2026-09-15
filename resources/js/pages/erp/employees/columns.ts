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
import { show } from '@/routes/erp/employees';
import StatusBadge from './StatusBadge.svelte';

export type EmployeeRow = {
    id: string;
    name: string;
    email: string;
    role: string | null;
    isActive: boolean;
    isAdmin: boolean;
    hasPassword: boolean;
    hasTwoFactor: boolean;
    lastLoginAt: string | null;
};

const helper = createColumnHelper<DataTableFeatures, EmployeeRow>();

/**
 * Nur die Spalten. Die Schlüssel müssen zur Freigabeliste in
 * `EmployeeList::SORTABLE` passen.
 */
export function employeeColumns(
    meta: DataTableMeta,
    onSort: (column: string) => void,
): ColumnDef<DataTableFeatures, EmployeeRow, unknown>[] {
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
                renderComponent(DataTableLeadCell, {
                    href: show(row.original.id).url,
                    label: row.original.name,
                    sublabel: row.original.email,
                }),
        }),
        helper.accessor('role', {
            header: () => sortable('role', 'Rolle'),
            cell: ({ row }) => row.original.role ?? '—',
        }),
        helper.display({
            id: 'status',
            header: () => 'Zugang',
            cell: ({ row }) =>
                renderComponent(StatusBadge, {
                    active: row.original.isActive,
                    hasPassword: row.original.hasPassword,
                }),
        }),
        helper.accessor('lastLoginAt', {
            header: () => sortable('lastLogin', 'Zuletzt angemeldet'),
            cell: ({ row }) => row.original.lastLoginAt ?? 'noch nie',
        }),
    ]);
}
