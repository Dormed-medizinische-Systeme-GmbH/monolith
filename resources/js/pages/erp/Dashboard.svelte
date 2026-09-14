<script lang="ts">
    import AppHead from '@/components/AppHead.svelte';
    import AccessPointCard from '@/components/AccessPointCard.svelte';
    import Heading from '@/components/Heading.svelte';
    import * as Card from '@/components/ui/card';
    import * as Select from '@/components/ui/select';

    /**
     * Die Wurzel des ERP. Inhaltlich noch leer.
     *
     * Das Cockpit ist je Abteilung im Code definiert und besteht aus Listen und
     * Kennzahlen (D-126) — es entsteht mit den Fachmodulen, nicht vorher. Bis
     * dahin steht hier nur der Nachweis, welche Postgres-Rolle den Request
     * bedient hat; er belegt ADR-036 im Browser statt nur im Dokument.
     */
    let { accessPoint } = $props();
</script>

<AppHead title="Dashboard" />

<div class="flex items-start justify-between gap-4">
    <Heading
        title="Dashboard"
        description="Das Cockpit entsteht mit den Fachmodulen (D-126)."
    />

    <!--
        TODO: Bewusst tot — Zweck und Werteliste sind noch offen.
        Entweder mit Inhalt füllen und verdrahten oder entfernen; ein
        Bedienelement, das dauerhaft nichts tut, erzieht dazu, Bedienelemente
        nicht mehr ernst zu nehmen.
    -->
    <Select.Root type="single" disabled>
        <Select.Trigger class="w-44 shrink-0">
            <Select.Value placeholder="Auswahl" />
        </Select.Trigger>
    </Select.Root>
</div>

<Card.Root class="max-w-md">
    <Card.Header>
        <Card.Title>Zugriffspunkt</Card.Title>
        <Card.Description>
            Was diesen Request bedient hat — Host, Verbindung, Datenbankrolle.
        </Card.Description>
    </Card.Header>
    <Card.Content>
        <AccessPointCard {accessPoint} framed={false} />
    </Card.Content>
</Card.Root>
