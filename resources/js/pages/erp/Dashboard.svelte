<script lang="ts">
    import { setLayoutProps } from '@inertiajs/svelte';
    import AppHead from '@/components/AppHead.svelte';
    import AccessPointCard from '@/components/AccessPointCard.svelte';
    import * as Card from '@/components/ui/card';

    /**
     * Die Wurzel des ERP. Inhaltlich noch leer.
     *
     * Das Cockpit ist je Abteilung im Code definiert und besteht aus Listen und
     * Kennzahlen (D-126) — es entsteht mit den Fachmodulen, nicht vorher. Bis
     * dahin steht hier nur der Nachweis, welche Postgres-Rolle den Request
     * bedient hat; er belegt ADR-036 im Browser statt nur im Dokument.
     */
    type Employee = { id: string; name: string; photoUrl: string | null };

    let { accessPoint, employees = [] }: { accessPoint: unknown; employees?: Employee[] } =
        $props();

    /*
     * Einfachauswahl, anders als der Personenfilter im Kalender: dort sieht man
     * mehrere Kalender nebeneinander, hier geht es um EINE Sicht.
     *
     * TODO: noch ohne Wirkung — sie wechselt nichts. Sobald das Cockpit steht
     * (D-126), entscheidet sie, wessen Zahlen es zeigt. Bis dahin ist sie
     * bedienbar, damit sich Aufbau und Platzbedarf beurteilen lassen.
     */
    let gewaehlt = $state<string>('');

    /*
     * Die Auswahl sitzt in der App-Kopfzeile, wie die Aktionen jeder anderen
     * Fläche. Übergeben wird sie als DATEN, nicht als fertiges Markup — die
     * Kopfzeile bestimmt so das Aussehen, die Seite nur, was zur Wahl steht.
     */
    $effect(() => {
        setLayoutProps({
            picker: {
                value: gewaehlt,
                placeholder: 'Alle Mitarbeiter',
                options: employees.map((e) => ({
                    value: e.id,
                    label: e.name,
                    imageUrl: e.photoUrl,
                })),
                onSelect: (wert: string) => (gewaehlt = wert),
            },
        });
    });
</script>

<AppHead title="Dashboard" />

<!--
    Keine eigene Überschrift: der Name der Fläche steht in der App-Kopfzeile,
    wie auf den Listen auch.
-->
<p class="text-sm text-muted-foreground">
    Das Cockpit entsteht mit den Fachmodulen (D-126).
</p>

<Card.Root class="mt-4 max-w-md">
    <Card.Header>
        <Card.Title>Zugriffspunkt</Card.Title>
        <Card.Description>
            Was diesen Request bedient hat — Host, Verbindung, Datenbankrolle.
        </Card.Description>
    </Card.Header>
    <Card.Content>
        <AccessPointCard accessPoint={accessPoint as never} framed={false} />
    </Card.Content>
</Card.Root>
