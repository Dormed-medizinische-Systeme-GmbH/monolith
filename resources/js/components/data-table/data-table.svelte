<script lang="ts" generics="TData extends RowData">
    import { router } from '@inertiajs/svelte';
    import {
        createTable,
        FlexRender,
        type ColumnDef,
        type RowData,
        type RowSelectionState,
    } from '@tanstack/svelte-table';
    import { Checkbox } from '@/components/ui/checkbox';
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
        selectable = true,
        rowId = (row: TData) => String((row as { id: unknown }).id),
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
        /** Auswahlkästchen je Zeile. Aus, wo es nichts auszuwählen gibt. */
        selectable?: boolean;
        /**
         * Der Schlüssel eines Datensatzes. Steuert die Auswahl über
         * Seitenwechsel hinweg — mit dem voreingestellten Zeilenindex wäre
         * sonst auf Seite 2 „dieselbe" Zeile ausgewählt wie auf Seite 1.
         */
        rowId?: (row: TData) => string;
    } = $props();

    // `RowSelectionState` fuehrt nur die AUSGEWAEHLTEN Schluessel (`true`),
    // abgewaehlte verschwinden — deshalb genuegt das Zaehlen der Schluessel.
    let rowSelection = $state<RowSelectionState>({});

    const selectedCount = $derived(Object.keys(rowSelection).length);

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
        getRowId: (row) => rowId(row),
        get enableRowSelection() {
            return selectable;
        },
        state: {
            get sorting() {
                return [{ id: meta.sort, desc: meta.direction === 'desc' }];
            },
            get rowSelection() {
                return rowSelection;
            },
        },
        onSortingChange: () => {},
        onRowSelectionChange: (updater) => {
            rowSelection =
                typeof updater === 'function' ? updater(rowSelection) : updater;
        },
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
                        {#if selectable}
                            <Table.Head class="w-10">
                                <Checkbox
                                    checked={table.getIsAllPageRowsSelected()}
                                    indeterminate={table.getIsSomePageRowsSelected()}
                                    onCheckedChange={(value) =>
                                        table.toggleAllPageRowsSelected(Boolean(value))}
                                    aria-label="Alle Zeilen dieser Seite auswählen"
                                />
                            </Table.Head>
                        {/if}
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
                        {#if selectable}
                            <!--
                                Die Zelle schluckt den Klick: sie gehört zur
                                Auswahl, nicht zum Öffnen. Ohne das führte ein
                                Treffer neben dem Kästchen in den Datensatz —
                                also genau dorthin, wo man gerade nicht hin
                                wollte.
                            -->
                            <Table.Cell
                                class="w-10"
                                onclick={(event: MouseEvent) => event.stopPropagation()}
                            >
                                <Checkbox
                                    checked={row.getIsSelected()}
                                    onCheckedChange={(value) =>
                                        row.toggleSelected(Boolean(value))}
                                    aria-label="Zeile auswählen"
                                />
                            </Table.Cell>
                        {/if}
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
                            colspan={columns.length + (selectable ? 1 : 0)}
                            class="h-24 text-center text-muted-foreground"
                        >
                            {emptyMessage}
                        </Table.Cell>
                    </Table.Row>
                {/each}
            </Table.Body>
        </Table.Root>
    </div>

    <DataTablePagination {meta} {selectedCount} onNavigate={navigate} />
</div>
