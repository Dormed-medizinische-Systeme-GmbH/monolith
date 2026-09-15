<script lang="ts">
    import AppHead from '@/components/AppHead.svelte';
    import AccessPointCard from '@/components/AccessPointCard.svelte';
    import Heading from '@/components/Heading.svelte';
    import * as Avatar from '@/components/ui/avatar';
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
    type Employee = { id: string; name: string; photoUrl: string | null };

    let { accessPoint, employees = [] }: { accessPoint: unknown; employees?: Employee[] } =
        $props();

    /*
     * Einfachauswahl, anders als der Personenfilter im Kalender: dort sieht man
     * mehrere Kalender nebeneinander, hier geht es um EINE Sicht.
     */
    let gewaehlt = $state<string>('');

    const person = $derived(employees.find((e) => e.id === gewaehlt) ?? null);

    function initialen(name: string): string {
        return name
            .split(' ')
            .map((teil) => teil[0] ?? '')
            .slice(0, 2)
            .join('')
            .toUpperCase();
    }
</script>

<AppHead title="Dashboard" />

<div class="flex items-start justify-between gap-4">
    <Heading
        title="Dashboard"
        description="Das Cockpit entsteht mit den Fachmodulen (D-126)."
    />

    <!--
        TODO: Die Auswahl hat noch keine Wirkung — sie wechselt nichts. Sobald
        das Cockpit steht (D-126), entscheidet sie, wessen Zahlen es zeigt. Bis
        dahin ist sie bedienbar, damit sich Aufbau und Platzbedarf beurteilen
        lassen.

        Einfachauswahl, anders als der Personenfilter im Kalender: dort sieht
        man mehrere Kalender nebeneinander, hier geht es um EINE Sicht.
    -->
    <Select.Root type="single" bind:value={gewaehlt}>
        <Select.Trigger class="w-56 shrink-0">
            {#if person}
                <span class="flex items-center gap-2">
                    <Avatar.Root class="size-6">
                        {#if person.photoUrl}
                            <Avatar.Image src={person.photoUrl} alt={person.name} />
                        {/if}
                        <Avatar.Fallback class="text-[10px]">
                            {initialen(person.name)}
                        </Avatar.Fallback>
                    </Avatar.Root>
                    {person.name}
                </span>
            {:else}
                <span class="text-muted-foreground">Alle Mitarbeiter</span>
            {/if}
        </Select.Trigger>

        <Select.Content>
            <Select.Item value="" label="Alle Mitarbeiter">Alle Mitarbeiter</Select.Item>
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
</div>

<Card.Root class="max-w-md">
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
