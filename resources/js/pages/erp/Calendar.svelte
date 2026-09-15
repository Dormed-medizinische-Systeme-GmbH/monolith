<script lang="ts">
    import { setLayoutProps } from '@inertiajs/svelte';
    import CalendarDays from '@lucide/svelte/icons/calendar-days';
    import CalendarRange from '@lucide/svelte/icons/calendar-range';
    import CalendarClock from '@lucide/svelte/icons/calendar-clock';
    import ChevronDown from '@lucide/svelte/icons/chevron-down';
    import ChevronLeft from '@lucide/svelte/icons/chevron-left';
    import ChevronRight from '@lucide/svelte/icons/chevron-right';
    import Columns3 from '@lucide/svelte/icons/columns-3';
    import List from '@lucide/svelte/icons/list';
    import MapPin from '@lucide/svelte/icons/map-pin';
    import Text from '@lucide/svelte/icons/text';
    import User from '@lucide/svelte/icons/user';
    import AppHead from '@/components/AppHead.svelte';
    import * as Avatar from '@/components/ui/avatar';
    import { Badge } from '@/components/ui/badge';
    import { Button } from '@/components/ui/button';
    import * as Dialog from '@/components/ui/dialog';
    import { Field, FieldGroup, FieldLabel } from '@/components/ui/field';
    import { Input } from '@/components/ui/input';
    import { Checkbox } from '@/components/ui/checkbox';
    import * as Select from '@/components/ui/select';
    import { Textarea } from '@/components/ui/textarea';
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
     *
     * **Ein Termin hat keinen Betreff.** Was im Kalender steht, wird aus Typ,
     * Status, „außer Haus" und der verknüpften Firma zusammengesetzt und
     * nirgends gespeichert. Die Farbe folgt dem Typ und ist ebenfalls keine
     * Eingabe — die Legende unter dem Kalender sagt, welche Farbe was meint.
     *
     * **Anlegen, Bearbeiten und Verschieben wirken nur im Browser.** Es gibt
     * keine Terminverwaltung, gegen die man speichern könnte; alles ist beim
     * nächsten Laden weg. Das ist kein Mangel des Entwurfs, sondern sein Zweck
     * — die Bedienung soll beurteilbar sein, bevor das Schema steht.
     */
    type Employee = { id: string; name: string; photoUrl: string | null };

    type Company = {
        id: string;
        name: string;
        postalCode: string | null;
        city: string | null;
    };

    type Typ = 'wartung' | 'service' | 'besprechung' | 'privat';
    type Status = 'vorlaeufig' | 'bestaetigt' | 'storniert';

    /**
     * Ein Termin hat KEINEN Betreff.
     *
     * Was im Kalender steht, entsteht aus Typ, Status, „außer Haus" und der
     * verknüpften Firma — siehe `bezeichnung()`. Gespeichert wird es nirgends;
     * ein eingetippter Betreff wäre eine zweite Wahrheit neben Feldern, die
     * dasselbe schon sagen.
     */
    type CalendarEvent = {
        id: string;
        type: Typ;
        status: Status | null;
        /** Außer Haus. Ergibt sich später aus der verknüpften Adresse. */
        offsite: boolean;
        companyId: string | null;
        description: string;
        employeeId: string | null;
        assignee: string;
        allDay: boolean;
        start: string;
        end: string;
    };

    type View = 'month' | 'week' | 'day' | 'agenda';

    let {
        events = [],
        employees = [],
        companies = [],
    }: {
        events?: CalendarEvent[];
        employees?: Employee[];
        companies?: Company[];
    } = $props();

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

    /**
     * Änderungen — NUR im Browser.
     *
     * Es gibt keine Terminverwaltung, gegen die man speichern könnte. Was hier
     * geändert oder angelegt wird, lebt in diesen beiden Feldern und ist beim
     * nächsten Laden weg. Absichtlich als Überlagerung über den Prop und nicht
     * als Kopie der ganzen Liste: so ist im Code sichtbar, was vom Server kommt
     * und was nur angefasst wurde.
     *
     * Verschieben per Ziehen schreibt in dieselbe Tabelle wie das Formular —
     * es ist dieselbe Art Änderung, nur mit der Maus.
     */
    let entwurf = $state<Record<string, Partial<CalendarEvent>>>({});
    let angelegt = $state<CalendarEvent[]>([]);

    /** Der Termin, dessen Kartei offen ist. */
    let offen = $state<Termin | null>(null);

    /** Was gerade am Mauszeiger hängt. */
    let zieht = $state<string | null>(null);

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

    /**
     * Die Ansichtsumschaltung sitzt in der App-Kopfzeile, wie auf jeder Fläche
     * — als EINE Schaltergruppe, denn genau eine Ansicht gilt.
     */
    $effect(() => {
        setLayoutProps({
            actions: ansichten.map((a) => ({
                label: a.label,
                icon: a.icon,
                group: 'ansicht',
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
        [...events, ...angelegt]
            .map((e) => ({ ...e, ...entwurf[e.id] }))
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
    /* Ziehen und Ablegen                                                  */
    /* ------------------------------------------------------------------ */

    /*
     * Natives HTML5-Drag-and-Drop statt einer Bibliothek: das Projekt hat
     * keine, und für „Block aufnehmen, woanders fallen lassen" braucht es
     * keine. Der Preis ist, dass der Ziehschatten der Browser-Standard ist.
     */
    function aufnehmen(event: DragEvent, t: Termin): void {
        zieht = t.id;
        event.dataTransfer?.setData('text/plain', t.id);

        if (event.dataTransfer) {
            event.dataTransfer.effectAllowed = 'move';
        }
    }

    function ablegenErlauben(event: DragEvent): void {
        if (zieht === null) {
            return;
        }

        event.preventDefault();

        if (event.dataTransfer) {
            event.dataTransfer.dropEffect = 'move';
        }
    }

    /** Verschiebt den gezogenen Termin, Dauer bleibt. */
    function verschiebeAuf(ziel: Date): void {
        const t = termine.find((x) => x.id === zieht);
        zieht = null;

        if (!t) {
            return;
        }

        const dauer = t.bis.getTime() - t.von.getTime();

        aendere(t.id, {
            start: ziel.toISOString(),
            end: new Date(ziel.getTime() + dauer).toISOString(),
        });
    }

    /** Im Zeitraster: auf Tag und Stunde, Minuten bleiben erhalten. */
    function ablegenImRaster(event: DragEvent, tag: Date, stunde: number): void {
        event.preventDefault();

        const t = termine.find((x) => x.id === zieht);

        if (!t) {
            zieht = null;
            return;
        }

        const ziel = startOfDay(tag);
        ziel.setHours(stunde, t.von.getMinutes(), 0, 0);

        verschiebeAuf(ziel);
    }

    /** Im Monat: nur der TAG wechselt, die Uhrzeit bleibt. */
    function ablegenAmTag(event: DragEvent, tag: Date): void {
        event.preventDefault();

        const t = termine.find((x) => x.id === zieht);

        if (!t) {
            zieht = null;
            return;
        }

        const ziel = startOfDay(tag);
        ziel.setHours(t.von.getHours(), t.von.getMinutes(), 0, 0);

        verschiebeAuf(ziel);
    }

    /** Eine Änderung an einem Termin ablegen — egal ob per Maus oder Formular. */
    function aendere(id: string, werte: Partial<CalendarEvent>): void {
        entwurf = { ...entwurf, [id]: { ...entwurf[id], ...werte } };
    }

    const aenderungen = $derived(Object.keys(entwurf).length + angelegt.length);

    /* ------------------------------------------------------------------ */
    /* Anlegen und Bearbeiten                                              */
    /* ------------------------------------------------------------------ */

    type FormWerte = {
        id: string | null;
        type: Typ;
        status: Status | null;
        companyId: string;
        employeeId: string;
        description: string;
        allDay: boolean;
        offsite: boolean;
        startDate: string;
        startTime: string;
        endDate: string;
        endTime: string;
    };

    let form = $state<FormWerte | null>(null);

    const gewaehlterMitarbeiter = $derived(
        employees.find((e) => e.id === form?.employeeId) ?? null,
    );

    /** `yyyy-mm-dd` — was ein `<input type="date">` erwartet, in ORTSZEIT. */
    function alsDatum(d: Date): string {
        return `${d.getFullYear()}-${String(d.getMonth() + 1).padStart(2, '0')}-${String(d.getDate()).padStart(2, '0')}`;
    }

    function alsUhrzeit(d: Date): string {
        return `${String(d.getHours()).padStart(2, '0')}:${String(d.getMinutes()).padStart(2, '0')}`;
    }

    /*
     * `new Date('2026-09-15T08:00')` OHNE Zeitzonenangabe wird als Ortszeit
     * gelesen — genau das ist hier gewollt. Mit angehängtem `Z` wäre 8 Uhr
     * plötzlich 10 Uhr.
     */
    function ausFeldern(datum: string, uhrzeit: string): Date {
        return new Date(`${datum}T${uhrzeit || '00:00'}`);
    }

    function bearbeiten(t: Termin): void {
        offen = null;

        form = {
            id: t.id,
            type: t.type,
            status: t.status,
            companyId: t.companyId ?? '',
            employeeId: t.employeeId ?? '',
            description: t.description,
            allDay: t.allDay,
            offsite: t.offsite,
            startDate: alsDatum(t.von),
            startTime: alsUhrzeit(t.von),
            endDate: alsDatum(t.bis),
            endTime: alsUhrzeit(t.bis),
        };
    }

    /** Klick in einen leeren Platz: neuer Termin, dort beginnend. */
    function anlegen(tag: Date, stunde: number | null): void {
        const start = startOfDay(tag);
        start.setHours(stunde ?? 9, 0, 0, 0);

        const ende = new Date(start.getTime() + 60 * 60 * 1000);

        form = {
            id: null,
            type: 'wartung',
            status: 'vorlaeufig',
            companyId: '',
            employeeId: employees[0]?.id ?? '',
            description: '',
            allDay: false,
            offsite: false,
            startDate: alsDatum(start),
            startTime: alsUhrzeit(start),
            endDate: alsDatum(ende),
            endTime: alsUhrzeit(ende),
        };
    }

    /** Die Status, die zum gewählten Typ gehören. */
    const moeglicheStatus = $derived(form ? typInfo(form.type).statusse : []);

    /*
     * Ein Typwechsel kann den Status ungültig machen — „Wartung, bestätigt" zu
     * „Privat" gemacht, und der Status gehört dort nicht hin. Statt ihn stehen
     * zu lassen und beim Speichern stillschweigend zu verwerfen, wird er hier
     * mitgeführt.
     */
    $effect(() => {
        if (!form) {
            return;
        }

        const erlaubt = typInfo(form.type).statusse;

        if (erlaubt.length === 0 && form.status !== null) {
            form.status = null;
        } else if (erlaubt.length > 0 && (form.status === null || !erlaubt.includes(form.status))) {
            form.status = erlaubt[0];
        }
    });

    const formularGueltig = $derived(
        form !== null &&
            ausFeldern(form.endDate, form.endTime) > ausFeldern(form.startDate, form.startTime),
    );

    function speichern(): void {
        if (!form || !formularGueltig) {
            return;
        }

        const start = form.allDay
            ? ausFeldern(form.startDate, '00:00')
            : ausFeldern(form.startDate, form.startTime);

        const ende = form.allDay
            ? ausFeldern(form.endDate, '23:59')
            : ausFeldern(form.endDate, form.endTime);

        const werte = {
            type: form.type,
            status: form.status,
            companyId: form.offsite ? form.companyId || null : null,
            employeeId: form.employeeId || null,
            assignee: employees.find((e) => e.id === form!.employeeId)?.name ?? '—',
            description: form.description,
            allDay: form.allDay,
            offsite: form.offsite,
            start: start.toISOString(),
            end: ende.toISOString(),
        };

        if (form.id) {
            aendere(form.id, werte);
        } else {
            // Der Schluessel ist lokal und bewusst erkennbar: nichts davon
            // erreicht je einen Server.
            angelegt = [...angelegt, { id: `lokal-${Date.now()}`, ...werte }];
        }

        form = null;
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
    /* Typ, Status und die daraus gebaute Bezeichnung                      */
    /* ------------------------------------------------------------------ */

    /*
     * Die Farbe hängt am TYP und ist keine Eingabe — ein frei wählbares
     * Farbfeld hieße, dass zwei Wartungen verschieden aussehen können. Ohne
     * Dark-Varianten (ADR-044); sobald die Palette steht, ist das diese eine
     * Tabelle.
     *
     * Welche Status es gibt, hängt ebenfalls am Typ: Privat und Besprechung
     * haben keinen, Wartung und Service haben drei (Nutzer).
     */
    const typen: {
        wert: Typ;
        label: string;
        statusse: Status[];
        farbe: string;
        punkt: string;
    }[] = [
        {
            wert: 'wartung',
            label: 'Wartung',
            statusse: ['vorlaeufig', 'bestaetigt', 'storniert'],
            farbe: 'border-blue-200 bg-blue-50 text-blue-800',
            punkt: 'bg-blue-500',
        },
        {
            wert: 'service',
            label: 'Service',
            statusse: ['vorlaeufig', 'bestaetigt', 'storniert'],
            farbe: 'border-red-200 bg-red-50 text-red-800',
            punkt: 'bg-red-500',
        },
        {
            wert: 'besprechung',
            label: 'Besprechung',
            statusse: [],
            farbe: 'border-neutral-200 bg-neutral-100 text-neutral-800',
            punkt: 'bg-neutral-400',
        },
        {
            wert: 'privat',
            label: 'Privat',
            statusse: [],
            farbe: 'border-violet-200 bg-violet-50 text-violet-800',
            punkt: 'bg-violet-500',
        },
    ];

    const statusnamen: Record<Status, string> = {
        vorlaeufig: 'vorläufig',
        bestaetigt: 'bestätigt',
        storniert: 'storniert',
    };

    function typInfo(typ: Typ) {
        return typen.find((t) => t.wert === typ) ?? typen[0];
    }

    function farbe(t: { type: Typ; status: Status | null }): string {
        // Storniert tritt zurück, unabhängig vom Typ — es soll nicht mehr nach
        // einem Termin aussehen, den jemand wahrnimmt.
        return t.status === 'storniert'
            ? 'border-neutral-200 bg-neutral-50 text-neutral-500 line-through'
            : typInfo(t.type).farbe;
    }

    /**
     * Die Bezeichnung eines Termins — zusammengesetzt, nirgends gespeichert.
     *
     * ```
     * Wartung · bestätigt · außer Haus · Musterpraxis
     *   → „Wartung bei Musterpraxis Dr. Muster, 21244 Buchholz"
     * dieselbe im Haus     → „Wartung im Haus"
     * dieselbe vorläufig   → „[BLOCKED] Wartung bei …"
     * dieselbe storniert   → „[STORNO] Wartung bei …"
     * ```
     *
     * > **Die Matrix ist noch nicht entschieden** (Nutzer). Was hier steht, ist
     * > die Regel aus dem ersten Durchgang; Sonderfälle je Typ kommen dazu.
     * > Wenn es so weit ist, gehört sie auf den Server — sie wird auch für
     * > Listen, Mails und PDFs gebraucht, und zweimal gepflegt läuft sie
     * > auseinander.
     */
    function bezeichnung(t: {
        type: Typ;
        status: Status | null;
        offsite: boolean;
        companyId: string | null;
    }): string {
        const kopf =
            t.status === 'vorlaeufig'
                ? '[BLOCKED] '
                : t.status === 'storniert'
                  ? '[STORNO] '
                  : '';

        const typ = typInfo(t.type).label;

        if (!t.offsite) {
            return `${kopf}${typ} im Haus`;
        }

        const firma = companies.find((c) => c.id === t.companyId);

        if (!firma) {
            return `${kopf}${typ} auswärts`;
        }

        const ort = [firma.postalCode, firma.city].filter(Boolean).join(' ');

        return `${kopf}${typ} bei ${firma.name}${ort ? `, ${ort}` : ''}`;
    }

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
                    <!--
                        Der Klick auf die freie Fläche legt an. Die Termine
                        darin fangen ihn selbst ab — sonst öffnete ein Klick auf
                        einen Termin zugleich die Neuanlage darunter.
                    -->
                    <div
                        class="flex min-h-28 flex-col gap-1 border-t border-l p-1.5 first:border-l-0 hover:bg-accent/40 [&:nth-child(7n+1)]:border-l-0"
                        class:bg-muted-30={!zelle.imMonat}
                        class:ring-1={zieht !== null}
                        class:ring-primary-40={zieht !== null}
                        role="presentation"
                        ondragover={ablegenErlauben}
                        ondrop={(e) => ablegenAmTag(e, zelle.datum)}
                        onclick={() => anlegen(zelle.datum, null)}
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
                            <button
                                type="button"
                                draggable="true"
                                ondragstart={(e) => aufnehmen(e, t)}
                                ondragend={() => (zieht = null)}
                                onclick={(e) => {
                                    e.stopPropagation();
                                    offen = t;
                                }}
                                class="flex cursor-grab items-center gap-1.5 truncate rounded border px-1.5 py-0.5 text-left text-xs active:cursor-grabbing {farbe(
                                    t,
                                )}"
                                class:opacity-50={!zelle.imMonat}
                                class:opacity-40={zieht === t.id}
                                title={bezeichnung(t)}
                            >
                                {#if !t.mehrtaegig}
                                    <span class="shrink-0 tabular-nums opacity-70">
                                        {zeit.format(t.von)}
                                    </span>
                                {/if}
                                <span class="truncate">{bezeichnung(t)}</span>
                            </button>
                        {/each}

                        {#if eintraege.length > 3}
                            <button
                                type="button"
                                class="px-1 text-left text-xs text-muted-foreground hover:underline"
                                onclick={(e) => {
                                    e.stopPropagation();
                                    anchor = zelle.datum;
                                    view = 'day';
                                }}
                            >
                                +{eintraege.length - 3} weitere
                            </button>
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
                            <!--
                                Auch hier wird abgelegt — auf den TAG, wie im
                                Monat. Eine Stunde gibt es nicht, an der man
                                einen ganztägigen Eintrag festmachen könnte.
                            -->
                            <div
                                class="min-h-8 space-y-1 border-l p-1 first:border-l-0"
                                class:bg-accent={zieht !== null}
                                role="presentation"
                                ondragover={ablegenErlauben}
                                ondrop={(e) => ablegenAmTag(e, tag)}
                            >
                                {#each anTag(mehrtaegig, tag) as t (t.id)}
                                    <button
                                        type="button"
                                        draggable="true"
                                        ondragstart={(e) => aufnehmen(e, t)}
                                        ondragend={() => (zieht = null)}
                                        onclick={() => (offen = t)}
                                        class="block w-full cursor-grab truncate rounded border px-1.5 py-0.5 text-left text-xs active:cursor-grabbing {farbe(
                                            t,
                                        )}"
                                        class:opacity-40={zieht === t.id}
                                        title={bezeichnung(t)}
                                    >
                                        {bezeichnung(t)}
                                    </button>
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
                                    <!--
                                        Jede Stunde ist ein Ablageziel. Die
                                        Minuten des Termins bleiben erhalten —
                                        ein Termin um 9:15 landet beim Ablegen
                                        auf 14 Uhr auf 14:15 und nicht auf 14:00.
                                    -->
                                    <button
                                        type="button"
                                        aria-label="Termin anlegen"
                                        class="block w-full border-t border-dashed first:border-t-0 hover:bg-accent/60"
                                        style="height:{STUNDEN_HOEHE}px"
                                        class:bg-muted-20={stunde < 8 || stunde >= 17}
                                        class:bg-accent={zieht !== null}
                                        ondragover={ablegenErlauben}
                                        ondrop={(e) => ablegenImRaster(e, tag, stunde)}
                                        onclick={() => anlegen(tag, stunde)}
                                    ></button>
                                {/each}

                                {#each belegt as { termin, spalte, von } (termin.id)}
                                    <button
                                        type="button"
                                        draggable="true"
                                        ondragstart={(e) => aufnehmen(e, termin)}
                                        ondragend={() => (zieht = null)}
                                        onclick={() => (offen = termin)}
                                        class="absolute cursor-grab overflow-hidden rounded border px-1.5 py-0.5 text-left text-xs active:cursor-grabbing {farbe(
                                            termin,
                                        )}"
                                        class:opacity-40={zieht === termin.id}
                                        style={blockStil(termin, tag, spalte, von)}
                                        title="{bezeichnung(termin)} · {termin.assignee}"
                                    >
                                        <span class="block truncate font-medium">
                                            {bezeichnung(termin)}
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
                            <li>
                                <button
                                    type="button"
                                    class="flex w-full items-start gap-3 rounded text-left text-sm hover:bg-muted/50"
                                    onclick={() => (offen = t)}
                                >
                                <span
                                    class="mt-1.5 size-2 shrink-0 rounded-full {typInfo(t.type).punkt}"
                                ></span>
                                <span class="w-28 shrink-0 tabular-nums text-muted-foreground">
                                    {t.mehrtaegig
                                        ? 'ganztägig'
                                        : `${zeit.format(t.von)}–${zeit.format(t.bis)}`}
                                </span>
                                <span class="min-w-0 flex-1">
                                    <span class="font-medium">{bezeichnung(t)}</span>
                                    {#if t.description}
                                        <span class="block truncate text-xs text-muted-foreground">
                                            {t.description}
                                        </span>
                                    {/if}
                                </span>
                                    <Badge
                                        variant="outline"
                                        class="shrink-0 text-muted-foreground"
                                    >
                                        {t.assignee}
                                    </Badge>
                                </button>
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

    <!--
        Legende. Die Farbe wird nicht gewählt, sie folgt dem Typ — dann muss
        irgendwo stehen, welche Farbe welchen Typ meint.
    -->
    <div class="flex flex-wrap items-center gap-x-5 gap-y-2 border-t pt-3 text-xs">
        {#each typen as t (t.wert)}
            <span class="flex items-center gap-1.5">
                <span class="size-2.5 rounded-full {t.punkt}"></span>
                {t.label}
            </span>
        {/each}

        <span class="flex items-center gap-1.5 text-muted-foreground">
            <span class="size-2.5 rounded-full bg-neutral-300"></span>
            <span class="line-through">storniert</span>
        </span>

        <span class="text-muted-foreground">
            [BLOCKED] vorläufig · [STORNO] storniert
        </span>
    </div>

    <!--
        Nur der Zähler bleibt: solange es keine Terminverwaltung gibt, muss
        sichtbar sein, dass Änderungen den nächsten Seitenaufruf nicht
        überleben. Er zeigt sich erst, wenn es etwas zu zeigen gibt.
    -->
    {#if aenderungen > 0}
        <p class="text-xs text-muted-foreground">
            <span class="font-medium text-foreground">
                {aenderungen}
                {aenderungen === 1 ? 'Änderung' : 'Änderungen'}
            </span>
            — nur hier im Browser, beim nächsten Laden ist alles wieder wie vorher.
        </p>
    {/if}
</div>

<!--
    Die Kartei eines Termins. Im Entwurf nur lesend: Bearbeiten und Löschen
    hängen an einer Terminverwaltung, die es noch nicht gibt.
-->
<Dialog.Root
    open={offen !== null}
    onOpenChange={(o) => {
        if (!o) {
            offen = null;
        }
    }}
>
    <Dialog.Content class="sm:max-w-md">
        {#if offen}
            <Dialog.Header>
                <Dialog.Title class="flex items-center gap-2">
                    <span
                        class="size-2.5 shrink-0 rounded-full {typInfo(offen.type).punkt}"
                    ></span>
                    {bezeichnung(offen)}
                </Dialog.Title>
            </Dialog.Header>

            <dl class="space-y-3 text-sm">
                <div class="flex items-start gap-3">
                    <CalendarClock class="mt-0.5 size-4 shrink-0 text-muted-foreground" />
                    <div>
                        <dt class="font-medium">Zeitraum</dt>
                        <dd class="text-muted-foreground">
                            {#if offen.mehrtaegig}
                                {tagLang.format(offen.von)} – {tagLang.format(offen.bis)}
                                <span class="block">ganztägig</span>
                            {:else}
                                {tagLang.format(offen.von)}
                                <span class="block tabular-nums">
                                    {zeit.format(offen.von)} – {zeit.format(offen.bis)} Uhr
                                </span>
                            {/if}
                        </dd>
                    </div>
                </div>

                <div class="flex items-start gap-3">
                    <User class="mt-0.5 size-4 shrink-0 text-muted-foreground" />
                    <div>
                        <dt class="font-medium">Verantwortlich</dt>
                        <dd class="text-muted-foreground">{offen.assignee}</dd>
                    </div>
                </div>

                <div class="flex items-start gap-3">
                    <MapPin class="mt-0.5 size-4 shrink-0 text-muted-foreground" />
                    <div>
                        <dt class="font-medium">
                            {typInfo(offen.type).label}{offen.status
                                ? `, ${statusnamen[offen.status]}`
                                : ''}
                        </dt>
                        <dd class="text-muted-foreground">
                            {offen.offsite ? 'außer Haus' : 'im Haus'}
                        </dd>
                    </div>
                </div>

                {#if offen.description}
                    <div class="flex items-start gap-3">
                        <Text class="mt-0.5 size-4 shrink-0 text-muted-foreground" />
                        <div>
                            <dt class="font-medium">Beschreibung</dt>
                            <dd class="whitespace-pre-line text-muted-foreground">
                                {offen.description}
                            </dd>
                        </div>
                    </div>
                {/if}
            </dl>

            <Dialog.Footer>
                {#if entwurf[offen.id]}
                    <Button
                        variant="ghost"
                        class="me-auto"
                        onclick={() => {
                            const { [offen!.id]: _, ...rest } = entwurf;
                            entwurf = rest;
                            offen = null;
                        }}
                    >
                        Änderung zurücknehmen
                    </Button>
                {/if}
                <Button variant="outline" onclick={() => (offen = null)}>Schließen</Button>
                <Button onclick={() => bearbeiten(offen!)}>Bearbeiten</Button>
            </Dialog.Footer>
        {/if}
    </Dialog.Content>
</Dialog.Root>

<style>
    /* Zwei Abstufungen, die als Utility-Klasse mit Schrägstrich nicht durch
       Sveltes `class:`-Direktive gehen. */
    .bg-muted-30 {
        background-color: color-mix(in oklab, var(--color-muted) 30%, transparent);
    }

    .bg-muted-20 {
        background-color: color-mix(in oklab, var(--color-muted) 40%, transparent);
    }

    /* Zeigt beim Ziehen, wohin man ablegen darf. */
    .ring-primary-40 {
        --tw-ring-color: color-mix(in oklab, var(--color-primary) 40%, transparent);
    }
</style>

<!--
    Anlegen und Bearbeiten in einer Maske — die Felder sind dieselben, nur das
    Ziel unterscheidet sich. Wie alles hier: reiner Entwurf, gespeichert wird
    nichts.
-->
<Dialog.Root
    open={form !== null}
    onOpenChange={(o) => {
        if (!o) {
            form = null;
        }
    }}
>
    <Dialog.Content class="sm:max-w-lg">
        {#if form}
            <Dialog.Header>
                <Dialog.Title>
                    {form.id ? 'Termin bearbeiten' : 'Termin anlegen'}
                </Dialog.Title>
                <Dialog.Description>
                    Die Änderung bleibt im Browser. Es gibt noch keine
                    Terminverwaltung, gegen die gespeichert werden könnte.
                </Dialog.Description>
            </Dialog.Header>

            <FieldGroup>
                <!--
                    Kein Betreff. Was im Kalender steht, entsteht aus diesen
                    Feldern — siehe `bezeichnung()`. Die Vorschau darunter zeigt
                    beim Tippen, was dabei herauskommt.
                -->
                <div class="grid gap-4 sm:grid-cols-2">
                    <Field>
                        <FieldLabel for="termin_type">Typ</FieldLabel>
                        <Select.Root type="single" bind:value={form.type}>
                            <Select.Trigger id="termin_type" class="w-full">
                                <span class="flex items-center gap-2">
                                    <span
                                        class="size-2.5 rounded-full {typInfo(form.type).punkt}"
                                    ></span>
                                    {typInfo(form.type).label}
                                </span>
                            </Select.Trigger>
                            <Select.Content>
                                {#each typen as t (t.wert)}
                                    <Select.Item value={t.wert} label={t.label}>
                                        <span class="flex items-center gap-2">
                                            <span class="size-2.5 rounded-full {t.punkt}"></span>
                                            {t.label}
                                        </span>
                                    </Select.Item>
                                {/each}
                            </Select.Content>
                        </Select.Root>
                    </Field>

                    <!--
                        Welche Status es gibt, hängt am Typ. Privat und
                        Besprechung haben keinen — dort steht das Feld gar nicht
                        erst, statt leer und gesperrt herumzustehen.
                    -->
                    {#if moeglicheStatus.length > 0 && form.status}
                        <Field>
                            <FieldLabel for="termin_status">Status</FieldLabel>
                            <Select.Root type="single" bind:value={form.status}>
                                <Select.Trigger id="termin_status" class="w-full">
                                    {statusnamen[form.status]}
                                </Select.Trigger>
                                <Select.Content>
                                    {#each moeglicheStatus as st (st)}
                                        <Select.Item value={st} label={statusnamen[st]}>
                                            {statusnamen[st]}
                                        </Select.Item>
                                    {/each}
                                </Select.Content>
                            </Select.Root>
                        </Field>
                    {/if}
                </div>

                <p class="rounded-md bg-muted/50 px-3 py-2 text-sm">
                    <span class="text-muted-foreground">Erscheint als</span>
                    <span class="font-medium">{bezeichnung(form)}</span>
                </p>

                <Field>
                    <FieldLabel for="termin_employee">Verantwortlich</FieldLabel>
                    <!--
                        Kein `<select>`: ein natives Auswahlfeld kann in seinen
                        Einträgen kein Bild tragen, und das Gesicht ist hier die
                        schnellste Unterscheidung — zwölf Namen liest man, zwölf
                        Gesichter erkennt man.
                    -->
                    <Select.Root type="single" bind:value={form.employeeId}>
                        <Select.Trigger id="termin_employee" class="w-full">
                            {#if gewaehlterMitarbeiter}
                                <span class="flex items-center gap-2">
                                    <Avatar.Root class="size-6">
                                        {#if gewaehlterMitarbeiter.photoUrl}
                                            <Avatar.Image
                                                src={gewaehlterMitarbeiter.photoUrl}
                                                alt={gewaehlterMitarbeiter.name}
                                            />
                                        {/if}
                                        <Avatar.Fallback class="text-[10px]">
                                            {initialen(gewaehlterMitarbeiter.name)}
                                        </Avatar.Fallback>
                                    </Avatar.Root>
                                    {gewaehlterMitarbeiter.name}
                                </span>
                            {:else}
                                <span class="text-muted-foreground">Bitte wählen</span>
                            {/if}
                        </Select.Trigger>

                        <Select.Content>
                            {#each employees as e (e.id)}
                                <Select.Item value={e.id} label={e.name}>
                                    <span class="flex items-center gap-2">
                                        <Avatar.Root class="size-6">
                                            {#if e.photoUrl}
                                                <Avatar.Image src={e.photoUrl} alt={e.name} />
                                            {/if}
                                            <Avatar.Fallback class="text-[10px]">
                                                {initialen(e.name)}
                                            </Avatar.Fallback>
                                        </Avatar.Root>
                                        {e.name}
                                    </span>
                                </Select.Item>
                            {/each}
                        </Select.Content>
                    </Select.Root>
                </Field>

                <div class="grid gap-4 sm:grid-cols-2">
                    <Field>
                        <FieldLabel for="termin_start_date">Beginn</FieldLabel>
                        <Input
                            id="termin_start_date"
                            type="date"
                            bind:value={form.startDate}
                        />
                    </Field>

                    <Field>
                        <FieldLabel for="termin_start_time">Uhrzeit</FieldLabel>
                        <Input
                            id="termin_start_time"
                            type="time"
                            disabled={form.allDay}
                            bind:value={form.startTime}
                        />
                    </Field>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <Field>
                        <FieldLabel for="termin_end_date">Ende</FieldLabel>
                        <Input id="termin_end_date" type="date" bind:value={form.endDate} />
                    </Field>

                    <Field>
                        <FieldLabel for="termin_end_time">Uhrzeit</FieldLabel>
                        <Input
                            id="termin_end_time"
                            type="time"
                            disabled={form.allDay}
                            bind:value={form.endTime}
                        />
                    </Field>
                </div>

                <div class="flex flex-wrap items-center gap-6">
                    <Field orientation="horizontal" class="w-auto">
                        <Checkbox id="termin_all_day" bind:checked={form.allDay} />
                        <FieldLabel for="termin_all_day">Ganztägig</FieldLabel>
                    </Field>

                    <!--
                        Vorgabe ist IM Haus. Später ergibt sich das aus der
                        verknüpften Adresse — ein Termin bei einer Praxis ist
                        außer Haus, einer ohne ist es nicht; bis dahin ist es
                        ein Haken.
                    -->
                    <Field orientation="horizontal" class="w-auto">
                        <Checkbox id="termin_offsite" bind:checked={form.offsite} />
                        <FieldLabel for="termin_offsite">Außer Haus</FieldLabel>
                    </Field>
                </div>

                {#if form.offsite}
                    <!--
                        Die Firma liefert Name, PLZ und Ort für die Bezeichnung.
                        Später kommt daraus auch der Ort selbst — dann ist „außer
                        Haus" keine Eingabe mehr, sondern eine Folge.
                    -->
                    <Field>
                        <FieldLabel for="termin_company">Firma</FieldLabel>
                        <Select.Root type="single" bind:value={form.companyId}>
                            <Select.Trigger id="termin_company" class="w-full">
                                {companies.find((c) => c.id === form!.companyId)?.name ??
                                    'keine — erscheint als „auswärts"'}
                            </Select.Trigger>
                            <Select.Content>
                                {#each companies as c (c.id)}
                                    <Select.Item value={c.id} label={c.name}>
                                        {c.name}
                                    </Select.Item>
                                {/each}
                            </Select.Content>
                        </Select.Root>
                    </Field>
                {/if}

                <Separator />

                <Field>
                    <FieldLabel for="termin_description">Beschreibung</FieldLabel>
                    <Textarea id="termin_description" rows={3} bind:value={form.description} />
                </Field>
            </FieldGroup>

            <Dialog.Footer>
                {#if form.id && angelegt.some((e) => e.id === form!.id)}
                    <Button
                        variant="ghost"
                        class="me-auto text-destructive hover:text-destructive"
                        onclick={() => {
                            angelegt = angelegt.filter((e) => e.id !== form!.id);
                            form = null;
                        }}
                    >
                        Löschen
                    </Button>
                {/if}
                <Button variant="outline" onclick={() => (form = null)}>Abbrechen</Button>
                <Button disabled={!formularGueltig} onclick={speichern}>
                    {form.id ? 'Speichern' : 'Anlegen'}
                </Button>
            </Dialog.Footer>
        {/if}
    </Dialog.Content>
</Dialog.Root>
