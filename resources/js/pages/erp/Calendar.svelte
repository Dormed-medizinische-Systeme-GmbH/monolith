<script lang="ts">
    import { setLayoutProps } from '@inertiajs/svelte';
    import CalendarDays from '@lucide/svelte/icons/calendar-days';
    import CalendarRange from '@lucide/svelte/icons/calendar-range';
    import ChevronDown from '@lucide/svelte/icons/chevron-down';
    import ChevronLeft from '@lucide/svelte/icons/chevron-left';
    import ChevronRight from '@lucide/svelte/icons/chevron-right';
    import Columns3 from '@lucide/svelte/icons/columns-3';
    import List from '@lucide/svelte/icons/list';
    import MapPin from '@lucide/svelte/icons/map-pin';
    import AppHead from '@/components/AppHead.svelte';
    import * as Avatar from '@/components/ui/avatar';
    import { Badge } from '@/components/ui/badge';
    import { Button } from '@/components/ui/button';
    import * as DropdownMenu from '@/components/ui/dropdown-menu';
    import { ScrollArea } from '@/components/ui/scroll-area';
    import { Separator } from '@/components/ui/separator';

    /**
     * Kalendervorschau — Entwurf.
     *
     * **Bewusst eine einzige Datei.** Der Kalender wird an keiner zweiten Stelle
     * gebraucht; ihn über zwanzig Komponenten zu verteilen hieße, eine Struktur
     * zu bauen, die niemand wiederverwendet. Geteilt wird nur, was ohnehin
     * geteilt ist: Button, Badge, Avatar, DropdownMenu, ScrollArea und
     * Separator aus `components/ui`.
     *
     * Aufbau übernommen von `github.com/lramos33/big-calendar` (React) — die
     * Terminlogik ist dieselbe, das Datumsrechnen nicht: das Original benutzt
     * `date-fns`, das wir nicht haben. Die Handvoll nötiger Funktionen steht
     * unten; deutsche Monats- und Wochentagsnamen kommen aus `Intl`, was für
     * eine deutschsprachige Oberfläche ohnehin besser passt als eine
     * Bibliothek mit eigenem Sprachpaket.
     *
     * Die Woche beginnt am MONTAG. Das Original beginnt am Sonntag; hier wäre
     * das schlicht falsch.
     */
    type Employee = { id: string; name: string; photoUrl: string | null };

    type CalendarEvent = {
        id: string;
        title: string;
        location: string;
        employeeId: string | null;
        assignee: string;
        color: 'blue' | 'green' | 'red' | 'yellow' | 'purple' | 'orange' | 'gray';
        allDay: boolean;
        start: string;
        end: string;
    };

    type View = 'month' | 'week' | 'day' | 'agenda';

    let {
        events = [],
        employees = [],
    }: { events?: CalendarEvent[]; employees?: Employee[] } = $props();

    const ansichten: { key: View; label: string; icon: typeof List }[] = [
        { key: 'day', label: 'Tag', icon: List },
        { key: 'week', label: 'Woche', icon: Columns3 },
        { key: 'month', label: 'Monat', icon: CalendarDays },
        { key: 'agenda', label: 'Agenda', icon: CalendarRange },
    ];

    let view = $state<View>('week');
    let anchor = $state(new Date());

    /*
     * Der Personenfilter. `null` heisst „noch nichts angefasst" und damit
     * ALLE — das ist etwas anderes als eine leere Auswahl, bei der bewusst
     * niemand angehakt ist und folglich nichts zu sehen sein soll.
     */
    let gewaehlt = $state<Set<string> | null>(null);

    const alleGewaehlt = $derived(gewaehlt === null || gewaehlt.size === employees.length);
    const sichtbar = $derived(gewaehlt === null ? new Set(employees.map((e) => e.id)) : gewaehlt);

    function umschalten(id: string): void {
        const naechste = new Set(gewaehlt ?? employees.map((e) => e.id));

        if (naechste.has(id)) {
            naechste.delete(id);
        } else {
            naechste.add(id);
        }

        gewaehlt = naechste;
    }

    function initialen(name: string): string {
        return name
            .split(' ')
            .map((teil) => teil[0] ?? '')
            .slice(0, 2)
            .join('')
            .toUpperCase();
    }

    /** Die Ansichtsumschaltung sitzt in der App-Kopfzeile, wie auf jeder Fläche. */
    $effect(() => {
        setLayoutProps({
            actions: ansichten.map((a) => ({
                label: a.label,
                icon: a.icon,
                variant: view === a.key ? ('default' as const) : ('outline' as const),
                onSelect: () => (view = a.key),
            })),
        });
    });

    /* ------------------------------------------------------------------ */
    /* Datumsrechnen                                                       */
    /* ------------------------------------------------------------------ */

    const MS_PRO_TAG = 86_400_000;

    function startOfDay(d: Date): Date {
        const x = new Date(d);
        x.setHours(0, 0, 0, 0);
        return x;
    }

    function addDays(d: Date, n: number): Date {
        const x = new Date(d);
        x.setDate(x.getDate() + n);
        return x;
    }

    function addMonths(d: Date, n: number): Date {
        const x = new Date(d);
        x.setDate(1);
        x.setMonth(x.getMonth() + n);
        return x;
    }

    /** Montag der Woche, in der `d` liegt. */
    function startOfWeek(d: Date): Date {
        const x = startOfDay(d);
        // getDay(): 0 = Sonntag. Für einen Montagsstart wird der Sonntag zur 7.
        const versatz = (x.getDay() + 6) % 7;
        return addDays(x, -versatz);
    }

    function isSameDay(a: Date, b: Date): boolean {
        return startOfDay(a).getTime() === startOfDay(b).getTime();
    }

    function isToday(d: Date): boolean {
        return isSameDay(d, new Date());
    }

    const zeit = new Intl.DateTimeFormat('de-DE', { hour: '2-digit', minute: '2-digit' });
    const wochentagKurz = new Intl.DateTimeFormat('de-DE', { weekday: 'short' });
    const wochentagLang = new Intl.DateTimeFormat('de-DE', { weekday: 'long' });
    const monatJahr = new Intl.DateTimeFormat('de-DE', { month: 'long', year: 'numeric' });
    const tagLang = new Intl.DateTimeFormat('de-DE', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    });
    const tagKurz = new Intl.DateTimeFormat('de-DE', { day: '2-digit', month: '2-digit' });

    /* ------------------------------------------------------------------ */
    /* Termine aufbereiten                                                 */
    /* ------------------------------------------------------------------ */

    type Termin = CalendarEvent & { von: Date; bis: Date; mehrtaegig: boolean };

    const termine: Termin[] = $derived(
        events
            .filter((e) => e.employeeId === null || sichtbar.has(e.employeeId))
            .map((e) => {
                const von = new Date(e.start);
                const bis = new Date(e.end);
                return { ...e, von, bis, mehrtaegig: e.allDay || !isSameDay(von, bis) };
            })
            .sort((a, b) => a.von.getTime() - b.von.getTime()),
    );

    const eintaegig = $derived(termine.filter((t) => !t.mehrtaegig));
    const mehrtaegig = $derived(termine.filter((t) => t.mehrtaegig));

    function anTag(liste: Termin[], tag: Date): Termin[] {
        const start = startOfDay(tag).getTime();
        const ende = start + MS_PRO_TAG - 1;

        return liste.filter((t) => t.von.getTime() <= ende && t.bis.getTime() >= start);
    }

    /**
     * Überlappende Termine nebeneinander legen.
     *
     * Gierig in Spalten sortiert: ein Termin kommt in die erste Spalte, in der
     * er nichts überdeckt. Ohne das liegen zwei Termine um 9 Uhr exakt
     * übereinander und der hintere ist unsichtbar.
     */
    function spalten(liste: Termin[]): { termin: Termin; spalte: number; von: number }[] {
        const gruppen: Termin[][] = [];

        for (const termin of liste) {
            const passt = gruppen.find(
                (g) => g[g.length - 1].bis.getTime() <= termin.von.getTime(),
            );

            if (passt) {
                passt.push(termin);
            } else {
                gruppen.push([termin]);
            }
        }

        return gruppen.flatMap((gruppe, spalte) =>
            gruppe.map((termin) => ({ termin, spalte, von: gruppen.length })),
        );
    }

    /* ------------------------------------------------------------------ */
    /* Zeitraster                                                          */
    /* ------------------------------------------------------------------ */

    const STUNDE_VON = 7;
    const STUNDE_BIS = 20;
    const STUNDEN_HOEHE = 56;

    const stunden = Array.from(
        { length: STUNDE_BIS - STUNDE_VON },
        (_, i) => i + STUNDE_VON,
    );

    /** Anteil des Tages, gemessen am sichtbaren Ausschnitt. */
    function anteil(d: Date): number {
        const minuten = d.getHours() * 60 + d.getMinutes();
        const sichtbarVon = STUNDE_VON * 60;
        const sichtbarBis = STUNDE_BIS * 60;

        return ((minuten - sichtbarVon) / (sichtbarBis - sichtbarVon)) * 100;
    }

    function blockStil(t: Termin, tag: Date, spalte: number, von: number): string {
        const tagStart = startOfDay(tag);
        const tagEnde = addDays(tagStart, 1);

        const start = t.von < tagStart ? tagStart : t.von;
        const ende = t.bis > tagEnde ? tagEnde : t.bis;

        const oben = Math.max(anteil(start), 0);
        const unten = Math.min(anteil(ende), 100);
        const breite = 100 / von;

        return `top:${oben}%;height:${Math.max(unten - oben, 2)}%;left:${spalte * breite}%;width:calc(${breite}% - 2px)`;
    }

    /** Wo die Jetzt-Linie liegt — oder `null`, wenn sie außerhalb liegt. */
    const jetztAnteil = $derived.by(() => {
        const a = anteil(new Date());
        return a >= 0 && a <= 100 ? a : null;
    });

    /* ------------------------------------------------------------------ */
    /* Zeitraum der aktuellen Ansicht                                      */
    /* ------------------------------------------------------------------ */

    const wochentage = $derived(
        Array.from({ length: 7 }, (_, i) => addDays(startOfWeek(anchor), i)),
    );

    /**
     * Die Zellen des Monatsrasters, aufgefüllt bis zur vollen Woche.
     *
     * Die Tage aus Vor- und Folgemonat bleiben sichtbar statt leer zu stehen —
     * ein Termin am 1. gehört in dieselbe Zeile wie der 31., sonst reißt eine
     * mehrtägige Zuordnung optisch ab.
     */
    const monatszellen = $derived.by(() => {
        const erster = new Date(anchor.getFullYear(), anchor.getMonth(), 1);
        const start = startOfWeek(erster);
        const letzter = new Date(anchor.getFullYear(), anchor.getMonth() + 1, 0);
        const ende = addDays(startOfWeek(letzter), 6);
        const tage = Math.round((ende.getTime() - start.getTime()) / MS_PRO_TAG) + 1;

        return Array.from({ length: tage }, (_, i) => {
            const datum = addDays(start, i);
            return { datum, imMonat: datum.getMonth() === anchor.getMonth() };
        });
    });

    /** Die nächsten 30 Tage, an denen überhaupt etwas ansteht. */
    const agendaTage = $derived.by(() => {
        const von = startOfDay(anchor);

        return Array.from({ length: 30 }, (_, i) => addDays(von, i))
            .map((datum) => ({ datum, eintraege: anTag(termine, datum) }))
            .filter((t) => t.eintraege.length > 0);
    });

    const titel = $derived.by(() => {
        if (view === 'day') {
            return tagLang.format(anchor);
        }

        if (view === 'week') {
            const [erster] = wochentage;
            const letzter = wochentage[6];
            return `${tagKurz.format(erster)} – ${tagKurz.format(letzter)} ${letzter.getFullYear()}`;
        }

        if (view === 'agenda') {
            return `ab ${tagLang.format(anchor)}`;
        }

        return monatJahr.format(anchor);
    });

    function blaettern(richtung: -1 | 1): void {
        if (view === 'month') {
            anchor = addMonths(anchor, richtung);
        } else if (view === 'week') {
            anchor = addDays(anchor, richtung * 7);
        } else {
            anchor = addDays(anchor, richtung);
        }
    }

    /* ------------------------------------------------------------------ */
    /* Farben                                                              */
    /* ------------------------------------------------------------------ */

    /*
     * Ohne Dark-Varianten (ADR-044). Die Palette des Projekts kennt bisher nur
     * `destructive`; sobald sie steht, ist das diese eine Tabelle.
     */
    const farben: Record<CalendarEvent['color'], string> = {
        blue: 'border-blue-200 bg-blue-50 text-blue-800',
        green: 'border-emerald-200 bg-emerald-50 text-emerald-800',
        red: 'border-red-200 bg-red-50 text-red-800',
        yellow: 'border-amber-200 bg-amber-50 text-amber-800',
        purple: 'border-violet-200 bg-violet-50 text-violet-800',
        orange: 'border-orange-200 bg-orange-50 text-orange-800',
        gray: 'border-neutral-200 bg-neutral-100 text-neutral-800',
    };

    const punkte: Record<CalendarEvent['color'], string> = {
        blue: 'bg-blue-500',
        green: 'bg-emerald-500',
        red: 'bg-red-500',
        yellow: 'bg-amber-500',
        purple: 'bg-violet-500',
        orange: 'bg-orange-500',
        gray: 'bg-neutral-400',
    };

</script>

<AppHead title="Kalender" />

<div class="flex flex-col gap-4">
    <!-- Bedienleiste -->
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-2">
            <Button variant="outline" size="sm" onclick={() => (anchor = new Date())}>
                Heute
            </Button>

            <div class="flex">
                <Button
                    variant="outline"
                    size="icon-sm"
                    class="rounded-r-none"
                    aria-label="Zurück"
                    onclick={() => blaettern(-1)}
                >
                    <ChevronLeft class="size-4" />
                </Button>
                <Button
                    variant="outline"
                    size="icon-sm"
                    class="-ms-px rounded-l-none"
                    aria-label="Weiter"
                    onclick={() => blaettern(1)}
                >
                    <ChevronRight class="size-4" />
                </Button>
            </div>

            <span class="ps-1 text-sm font-medium">{titel}</span>
        </div>

        <!--
            Personenfilter. Mehrfachauswahl, deshalb Kästchen und kein
            Auswahlfeld — man will „Falk und Meitsch" sehen können, nicht
            „einen oder alle". Das Menü bleibt beim Anhaken offen, sonst müsste
            man es je Person neu aufziehen.
        -->
        <DropdownMenu.Root>
            <DropdownMenu.Trigger>
                {#snippet child({ props })}
                    <Button {...props} variant="outline" size="sm" class="gap-2">
                        <span class="flex -space-x-2">
                            {#each employees.filter((e) => sichtbar.has(e.id)).slice(0, 3) as e (e.id)}
                                <Avatar.Root class="size-5 ring-2 ring-background">
                                    {#if e.photoUrl}
                                        <Avatar.Image src={e.photoUrl} alt={e.name} />
                                    {/if}
                                    <Avatar.Fallback class="text-[9px]">
                                        {initialen(e.name)}
                                    </Avatar.Fallback>
                                </Avatar.Root>
                            {/each}
                        </span>
                        {alleGewaehlt
                            ? 'Alle Mitarbeiter'
                            : `${sichtbar.size} von ${employees.length}`}
                        <ChevronDown class="size-4 text-muted-foreground" />
                    </Button>
                {/snippet}
            </DropdownMenu.Trigger>

            <DropdownMenu.Content align="end" class="w-64">
                <DropdownMenu.Label>Mitarbeiter</DropdownMenu.Label>
                <DropdownMenu.Separator />

                <DropdownMenu.CheckboxItem
                    checked={alleGewaehlt}
                    closeOnSelect={false}
                    onCheckedChange={() =>
                        (gewaehlt = alleGewaehlt ? new Set() : new Set(employees.map((e) => e.id)))}
                >
                    Alle
                </DropdownMenu.CheckboxItem>

                <DropdownMenu.Separator />

                <div class="max-h-72 overflow-y-auto">
                    {#each employees as e (e.id)}
                        <DropdownMenu.CheckboxItem
                            checked={sichtbar.has(e.id)}
                            closeOnSelect={false}
                            onCheckedChange={() => umschalten(e.id)}
                        >
                            <span class="flex min-w-0 items-center gap-2">
                                <Avatar.Root class="size-6">
                                    {#if e.photoUrl}
                                        <Avatar.Image src={e.photoUrl} alt={e.name} />
                                    {/if}
                                    <Avatar.Fallback class="text-[10px]">
                                        {initialen(e.name)}
                                    </Avatar.Fallback>
                                </Avatar.Root>
                                <span class="truncate">{e.name}</span>
                            </span>
                        </DropdownMenu.CheckboxItem>
                    {/each}
                </div>
            </DropdownMenu.Content>
        </DropdownMenu.Root>
    </div>

    <!-- =============================== MONAT =============================== -->
    {#if view === 'month'}
        <div class="overflow-hidden rounded-md border">
            <div class="grid grid-cols-7 border-b bg-muted/30">
                {#each wochentage as tag (tag.getTime())}
                    <div class="py-2 text-center text-xs font-medium text-muted-foreground">
                        {wochentagKurz.format(tag)}
                    </div>
                {/each}
            </div>

            <div class="grid grid-cols-7">
                {#each monatszellen as zelle (zelle.datum.getTime())}
                    {@const eintraege = anTag(termine, zelle.datum)}
                    <div
                        class="flex min-h-28 flex-col gap-1 border-t border-l p-1.5 first:border-l-0 [&:nth-child(7n+1)]:border-l-0"
                        class:bg-muted-30={!zelle.imMonat}
                    >
                        <span
                            class="flex size-6 items-center justify-center self-start rounded-full text-xs font-semibold"
                            class:opacity-40={!zelle.imMonat}
                            class:bg-primary={isToday(zelle.datum)}
                            class:text-primary-foreground={isToday(zelle.datum)}
                        >
                            {zelle.datum.getDate()}
                        </span>

                        {#each eintraege.slice(0, 3) as t (t.id)}
                            <div
                                class="flex items-center gap-1.5 truncate rounded border px-1.5 py-0.5 text-xs {farben[
                                    t.color
                                ]}"
                                class:opacity-50={!zelle.imMonat}
                                title="{t.title} · {t.location}"
                            >
                                {#if !t.mehrtaegig}
                                    <span class="shrink-0 tabular-nums opacity-70">
                                        {zeit.format(t.von)}
                                    </span>
                                {/if}
                                <span class="truncate">{t.title}</span>
                            </div>
                        {/each}

                        {#if eintraege.length > 3}
                            <span class="px-1 text-xs text-muted-foreground">
                                +{eintraege.length - 3} weitere
                            </span>
                        {/if}
                    </div>
                {/each}
            </div>
        </div>

        <!-- =========================== WOCHE / TAG =========================== -->
    {:else if view === 'week' || view === 'day'}
        {@const tage = view === 'day' ? [startOfDay(anchor)] : wochentage}

        <div class="overflow-hidden rounded-md border">
            <!-- Kopfzeile -->
            <div class="flex border-b bg-muted/30">
                <div class="w-14 shrink-0"></div>
                <div
                    class="grid flex-1 border-l"
                    style="grid-template-columns: repeat({tage.length}, minmax(0, 1fr))"
                >
                    {#each tage as tag (tag.getTime())}
                        <div class="border-l py-2 text-center first:border-l-0">
                            <div class="text-xs text-muted-foreground">
                                {view === 'day'
                                    ? wochentagLang.format(tag)
                                    : wochentagKurz.format(tag)}
                            </div>
                            <div
                                class="mx-auto mt-0.5 flex size-6 items-center justify-center rounded-full text-sm font-semibold"
                                class:bg-primary={isToday(tag)}
                                class:text-primary-foreground={isToday(tag)}
                            >
                                {tag.getDate()}
                            </div>
                        </div>
                    {/each}
                </div>
            </div>

            <!--
                Ganztägige und mehrtägige Einträge stehen ÜBER dem Zeitraster,
                nicht darin: sie haben keine Uhrzeit, an der man sie aufhängen
                könnte, und würden das Raster sonst über die volle Höhe blocken.
            -->
            {#if mehrtaegig.length > 0}
                <div class="flex border-b">
                    <div
                        class="flex w-14 shrink-0 items-center justify-end pe-2 text-xs text-muted-foreground"
                    >
                        ganztägig
                    </div>
                    <div
                        class="grid flex-1 border-l"
                        style="grid-template-columns: repeat({tage.length}, minmax(0, 1fr))"
                    >
                        {#each tage as tag (tag.getTime())}
                            <div class="min-h-8 space-y-1 border-l p-1 first:border-l-0">
                                {#each anTag(mehrtaegig, tag) as t (t.id)}
                                    <div
                                        class="truncate rounded border px-1.5 py-0.5 text-xs {farben[
                                            t.color
                                        ]}"
                                        title={t.title}
                                    >
                                        {t.title}
                                    </div>
                                {/each}
                            </div>
                        {/each}
                    </div>
                </div>
            {/if}

            <!-- Zeitraster -->
            <ScrollArea class="h-[640px]">
                <div class="flex">
                    <div class="w-14 shrink-0">
                        {#each stunden as stunde (stunde)}
                            <div
                                class="relative text-right"
                                style="height:{STUNDEN_HOEHE}px"
                            >
                                <span
                                    class="absolute -top-2 right-2 text-xs text-muted-foreground tabular-nums"
                                >
                                    {String(stunde).padStart(2, '0')}:00
                                </span>
                            </div>
                        {/each}
                    </div>

                    <div
                        class="relative grid flex-1 border-l"
                        style="grid-template-columns: repeat({tage.length}, minmax(0, 1fr))"
                    >
                        {#each tage as tag (tag.getTime())}
                            {@const belegt = spalten(anTag(eintaegig, tag))}
                            <div class="relative border-l first:border-l-0">
                                {#each stunden as stunde (stunde)}
                                    <div
                                        class="border-t border-dashed first:border-t-0"
                                        style="height:{STUNDEN_HOEHE}px"
                                        class:bg-muted-20={stunde < 8 || stunde >= 17}
                                    ></div>
                                {/each}

                                {#each belegt as { termin, spalte, von } (termin.id)}
                                    <button
                                        type="button"
                                        class="absolute overflow-hidden rounded border px-1.5 py-0.5 text-left text-xs {farben[
                                            termin.color
                                        ]}"
                                        style={blockStil(termin, tag, spalte, von)}
                                        title="{termin.title} · {termin.location} · {termin.assignee}"
                                    >
                                        <span class="block truncate font-medium">
                                            {termin.title}
                                        </span>
                                        <span class="block truncate opacity-75 tabular-nums">
                                            {zeit.format(termin.von)}–{zeit.format(termin.bis)}
                                        </span>
                                    </button>
                                {/each}

                                {#if jetztAnteil !== null && isToday(tag)}
                                    <!-- Wo wir gerade stehen. -->
                                    <div
                                        class="pointer-events-none absolute inset-x-0 z-10 border-t-2 border-red-500"
                                        style="top:{jetztAnteil}%"
                                    >
                                        <span
                                            class="absolute -top-1 -left-1 size-2 rounded-full bg-red-500"
                                        ></span>
                                    </div>
                                {/if}
                            </div>
                        {/each}
                    </div>
                </div>
            </ScrollArea>
        </div>

        <!-- =============================== AGENDA ============================== -->
    {:else}
        <div class="overflow-hidden rounded-md border">
            {#each agendaTage as { datum, eintraege }, i (datum.getTime())}
                {#if i > 0}
                    <Separator />
                {/if}
                <div class="flex gap-4 p-4">
                    <div class="w-28 shrink-0">
                        <div
                            class="text-sm font-semibold"
                            class:text-primary={isToday(datum)}
                        >
                            {wochentagLang.format(datum)}
                        </div>
                        <div class="text-xs text-muted-foreground">
                            {tagLang.format(datum)}
                        </div>
                    </div>

                    <ul class="flex-1 space-y-2">
                        {#each eintraege as t (t.id)}
                            <li class="flex items-start gap-3 text-sm">
                                <span
                                    class="mt-1.5 size-2 shrink-0 rounded-full {punkte[t.color]}"
                                ></span>
                                <span class="w-28 shrink-0 tabular-nums text-muted-foreground">
                                    {t.mehrtaegig
                                        ? 'ganztägig'
                                        : `${zeit.format(t.von)}–${zeit.format(t.bis)}`}
                                </span>
                                <span class="min-w-0 flex-1">
                                    <span class="font-medium">{t.title}</span>
                                    {#if t.location && t.location !== '—'}
                                        <span
                                            class="flex items-center gap-1 text-xs text-muted-foreground"
                                        >
                                            <MapPin class="size-3" />
                                            {t.location}
                                        </span>
                                    {/if}
                                </span>
                                <Badge variant="outline" class="shrink-0 text-muted-foreground">
                                    {t.assignee}
                                </Badge>
                            </li>
                        {/each}
                    </ul>
                </div>
            {:else}
                <p class="p-8 text-center text-sm text-muted-foreground">
                    In den nächsten 30 Tagen steht nichts an.
                </p>
            {/each}
        </div>
    {/if}

    <p class="text-xs text-muted-foreground">
        Entwurf. Die Termine sind erfunden — es gibt weder eine Terminverwaltung
        noch ein Scheduling-Modul (Phase 6.1). Diese Fläche dient dazu, die
        Bedienung zu beurteilen, bevor das Schema steht.
    </p>
</div>

<style>
    /* Zwei Abstufungen, die als Utility-Klasse mit Schrägstrich nicht durch
       Sveltes `class:`-Direktive gehen. */
    .bg-muted-30 {
        background-color: color-mix(in oklab, var(--color-muted) 30%, transparent);
    }

    .bg-muted-20 {
        background-color: color-mix(in oklab, var(--color-muted) 40%, transparent);
    }
</style>
