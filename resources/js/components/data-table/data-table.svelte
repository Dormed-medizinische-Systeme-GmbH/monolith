<script lang="ts" generics="TData extends RowData">
    import { router } from '@inertiajs/svelte';
    import {
        createTable,
        FlexRender,
        type ColumnDef,
        type RowData,
    } from '@tanstack/svelte-table';
    import { Input } from '@/components/ui/input';
    import * as Table from '@/components/ui/table';
    import DataTablePagination from './data-table-pagination.svelte';
    import { features, type DataTableMeta } from './data-table-features';

    /**
     * Die gemeinsame Listenansicht des ERP.
     *
     * Jede Liste verhält sich gleich — Spalten und Datensatz unterscheiden
     * sich, das Verhalten nicht. Deshalb liegt es hier und nicht in jeder
     * Seite neu.
     *
     * Suche, Sortierung und Seitenaufteilung laufen auf dem SERVER: die
     * Bedienelemente lösen einen Inertia-Besuch mit geänderten
     * Abfrageparametern aus, und der Controller liefert die passende Seite.
     * Das ist die Abweichung von der shadcn-Anleitung, die alles im Browser
     * macht — bei rund 50.000 Personen im Adressstamm wäre das die erste
     * Liste, die nicht mehr lädt.
     */
    let {
        columns,
        rows,
        meta,
        searchPlaceholder = 'Suchen …',
        emptyMessage = 'Keine Einträge gefunden.',
        rowHref,
    }: {
        columns: ColumnDef<typeof features, TData, unknown>[];
        rows: TData[];
        meta: DataTableMeta;
        searchPlaceholder?: string;
        emptyMessage?: string;
        /**
         * Wohin eine Zeile führt. Gesetzt, macht sie die gesamte Zeile
         * anklickbar — die Leitspalte trägt zusätzlich einen `DataTableLink`,
         * damit es auch ein echter Verweis bleibt.
         */
        rowHref?: (row: TData) => string;
    } = $props();

    const table = createTable({
        features,
        get data() {
            return rows;
        },
        get columns() {
            return columns;
        },
        /*
         * `manualSorting`: TanStack verwaltet den Zustand und zeichnet die
         * Pfeile, ordnet die Zeilen aber nicht selbst um — sie kommen bereits
         * sortiert aus Postgres.
         */
        manualSorting: true,
        state: {
            get sorting() {
                return [{ id: meta.sort, desc: meta.direction === 'desc' }];
            },
        },
        onSortingChange: () => {},
    });

    /** Besucht dieselbe Seite mit geänderten Parametern. */
    function navigate(params: Record<string, string | number>): void {
        router.get(
            window.location.pathname,
            {
                suche: meta.search,
                sortierung: meta.sort,
                richtung: meta.direction,
                ...params,
            },
            { preserveState: true, preserveScroll: true, replace: true },
        );
    }

    export function sortBy(column: string): void {
        // Dieselbe Spalte erneut: Richtung umkehren. Andere Spalte: aufsteigend.
        navigate({
            sortierung: column,
            richtung:
                meta.sort === column && meta.direction === 'asc' ? 'desc' : 'asc',
            page: 1,
        });
    }

    /**
     * Klick auf die Zeile öffnet den Datensatz.
     *
     * Trifft der Klick einen Verweis oder ein Bedienelement innerhalb der
     * Zeile, bleibt er dort — sonst navigierte die Zeile zusätzlich zum
     * eigentlichen Ziel.
     */
    function onRowClick(event: MouseEvent, row: TData): void {
        if ((event.target as HTMLElement).closest('a, button, input, [role="button"]')) {
            return;
        }

        router.visit(rowHref!(row));
    }

    function onRowKeydown(event: KeyboardEvent, row: TData): void {
        if (event.key !== 'Enter' && event.key !== ' ') {
            return;
        }

        if ((event.target as HTMLElement).closest('a, button, input')) {
            return;
        }

        event.preventDefault();
        router.visit(rowHref!(row));
    }

    let searchTimer: ReturnType<typeof setTimeout>;

    function onSearch(value: string): void {
        // Ohne Verzögerung löst jeder Tastendruck einen Serverbesuch aus.
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => navigate({ suche: value, page: 1 }), 300);
    }
</script>

<div class="flex flex-col gap-4">
    <Input
        type="search"
        placeholder={searchPlaceholder}
        value={meta.search}
        class="max-w-sm"
        oninput={(event) => onSearch(event.currentTarget.value)}
    />

    <div class="overflow-x-auto rounded-md border">
        <Table.Root>
            <Table.Header>
                {#each table.getHeaderGroups() as headerGroup (headerGroup.id)}
                    <Table.Row>
                        {#each headerGroup.headers as header (header.id)}
                            <Table.Head>
                                {#if !header.isPlaceholder}
                                    <!--
                                        Der Typ von `content` ist bei FlexRender
                                        eine Vereinigung aus Kopf- und
                                        Zellkontext; die Kopfvorlage allein
                                        passt nicht hinein. Die Zuweisung ist
                                        zur Laufzeit korrekt — FlexRender
                                        entscheidet anhand des uebergebenen
                                        Kontexts.
                                    -->
                                    <FlexRender
                                        content={header.column.columnDef
                                            .header as never}
                                        context={header.getContext()}
                                    />
                                {/if}
                            </Table.Head>
                        {/each}
                    </Table.Row>
                {/each}
            </Table.Header>

            <Table.Body>
                {#each table.getRowModel().rows as row (row.id)}
                    <Table.Row
                        class={rowHref ? 'cursor-pointer' : undefined}
                        tabindex={rowHref ? 0 : undefined}
                        onclick={rowHref
                            ? (event: MouseEvent) => onRowClick(event, row.original)
                            : undefined}
                        onkeydown={rowHref
                            ? (event: KeyboardEvent) =>
                                  onRowKeydown(event, row.original)
                            : undefined}
                    >
                        {#each row.getAllCells() as cell (cell.id)}
                            <Table.Cell>
                                <!-- Gleicher Grund wie oben bei der Kopfzeile. -->
                                <FlexRender
                                    content={cell.column.columnDef.cell as never}
                                    context={cell.getContext()}
                                />
                            </Table.Cell>
                        {/each}
                    </Table.Row>
                {:else}
                    <Table.Row>
                        <Table.Cell
                            colspan={columns.length}
                            class="h-24 text-center text-muted-foreground"
                        >
                            {emptyMessage}
                        </Table.Cell>
                    </Table.Row>
                {/each}
            </Table.Body>
        </Table.Root>
    </div>

    <DataTablePagination {meta} onNavigate={navigate} />
</div>
