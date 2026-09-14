<script lang="ts">
    import { Link } from '@inertiajs/svelte';
    import ArrowLeft from '@lucide/svelte/icons/arrow-left';
    import AppHead from '@/components/AppHead.svelte';
    import { Badge } from '@/components/ui/badge';
    import * as Card from '@/components/ui/card';
    import * as Table from '@/components/ui/table';
    import { index } from '@/routes/erp/companies';
    import AccessBadge from './AccessBadge.svelte';
    import ChannelList from './ChannelList.svelte';
    import type { CompanyProfile } from './profile';

    /**
     * Die Firma auf einen Blick: Stammdaten oben, Ansprechpartner darunter.
     *
     * Die Ansprechpartner sind hier bewusst KEINE `DataTable`: es gibt nichts
     * zu durchsuchen, zu sortieren oder zu blättern — eine Praxis hat eine
     * Handvoll Kontakte, und die stehen vollständig da. Die zentrale
     * Listenmechanik ist für den Adressstamm gebaut, nicht für sechs Zeilen.
     */
    let { company }: { company: CompanyProfile } = $props();
</script>

<AppHead title={company.name} />

<div class="space-y-6">
    <div>
        <Link
            href={index().url}
            class="inline-flex items-center gap-1 text-sm text-muted-foreground underline-offset-4 hover:underline"
        >
            <ArrowLeft class="size-4" />
            Alle Firmen
        </Link>
    </div>

    <header class="space-y-1">
        <h2 class="text-xl font-semibold tracking-tight">{company.name}</h2>
        {#if company.nameAddition}
            <p class="text-sm text-muted-foreground">{company.nameAddition}</p>
        {/if}
        <div class="flex flex-wrap items-center gap-2 pt-1">
            {#if company.specialty}
                <Badge variant="secondary">{company.specialty}</Badge>
            {/if}
            {#if company.avv.signed}
                <Badge variant="secondary">
                    AVV {company.avv.label}{company.avv.signedAt
                        ? ` · ${company.avv.signedAt}`
                        : ''}
                </Badge>
            {:else}
                <Badge variant="outline" class="text-muted-foreground">
                    AVV {company.avv.label}
                </Badge>
            {/if}
        </div>
    </header>

    <div class="grid gap-4 md:grid-cols-2">
        <Card.Root>
            <Card.Header>
                <Card.Title>Anschrift</Card.Title>
            </Card.Header>
            <Card.Content class="space-y-4 text-sm">
                {#if company.address}
                    <address class="not-italic">
                        {company.address.street}<br />
                        {company.address.city}
                        {#if company.address.district}
                            <br />{company.address.district}
                        {/if}
                    </address>
                {:else}
                    <p class="text-muted-foreground">Keine Sitzadresse hinterlegt.</p>
                {/if}

                {#if company.billingCompany}
                    <p class="text-muted-foreground">
                        Rechnungen gehen an
                        <span class="text-foreground">{company.billingCompany.name}</span>
                    </p>
                {/if}
            </Card.Content>
        </Card.Root>

        <Card.Root>
            <Card.Header>
                <Card.Title>Kontakt</Card.Title>
            </Card.Header>
            <Card.Content>
                <ChannelList channels={company.channels} />
            </Card.Content>
        </Card.Root>

        {#if Object.keys(company.stammdaten).length > 0}
            <Card.Root>
                <Card.Header>
                    <Card.Title>Stammdaten</Card.Title>
                </Card.Header>
                <Card.Content>
                    <dl class="space-y-1 text-sm">
                        {#each Object.entries(company.stammdaten) as [label, value] (label)}
                            <div class="flex gap-2">
                                <dt class="w-36 shrink-0 text-muted-foreground">
                                    {label}
                                </dt>
                                <dd>{value}</dd>
                            </div>
                        {/each}
                    </dl>
                </Card.Content>
            </Card.Root>
        {/if}

        <Card.Root>
            <Card.Header>
                <Card.Title>Standorte</Card.Title>
                <Card.Description>
                    Geräte und Serviceverträge hängen später am Standort, nicht an
                    der Firma (D-007).
                </Card.Description>
            </Card.Header>
            <Card.Content class="space-y-3 text-sm">
                {#each company.locations as location (location.id)}
                    <div>
                        <div class="flex items-center gap-2">
                            <span class="font-medium">{location.name}</span>
                            {#if location.isPrimary}
                                <Badge variant="outline" class="text-muted-foreground">
                                    Hauptstandort
                                </Badge>
                            {/if}
                        </div>
                        {#if location.address}
                            <p class="text-muted-foreground">
                                {location.address.street}, {location.address.city}
                            </p>
                        {/if}
                    </div>
                {:else}
                    <p class="text-muted-foreground">Kein Standort hinterlegt.</p>
                {/each}
            </Card.Content>
        </Card.Root>

        {#if Object.keys(company.bank).length > 0}
            <Card.Root>
                <Card.Header>
                    <Card.Title>Bankverbindung</Card.Title>
                </Card.Header>
                <Card.Content>
                    <dl class="space-y-1 text-sm">
                        {#each Object.entries(company.bank) as [label, value] (label)}
                            <div class="flex gap-2">
                                <dt class="w-36 shrink-0 text-muted-foreground">
                                    {label}
                                </dt>
                                <dd class="font-mono text-xs">{value}</dd>
                            </div>
                        {/each}
                    </dl>
                </Card.Content>
            </Card.Root>
        {/if}

        {#if company.responsible.sales || company.responsible.service}
            <Card.Root>
                <Card.Header>
                    <Card.Title>Zuständig</Card.Title>
                    <Card.Description>
                        Informativ — Berechtigungen kommen aus der Rolle, nicht von
                        hier (D-016).
                    </Card.Description>
                </Card.Header>
                <Card.Content>
                    <dl class="space-y-1 text-sm">
                        <div class="flex gap-2">
                            <dt class="w-36 shrink-0 text-muted-foreground">Vertrieb</dt>
                            <dd>{company.responsible.sales ?? '—'}</dd>
                        </div>
                        <div class="flex gap-2">
                            <dt class="w-36 shrink-0 text-muted-foreground">Service</dt>
                            <dd>{company.responsible.service ?? '—'}</dd>
                        </div>
                    </dl>
                </Card.Content>
            </Card.Root>
        {/if}
    </div>

    {#if company.notes}
        <Card.Root>
            <Card.Header>
                <Card.Title>Notiz</Card.Title>
            </Card.Header>
            <Card.Content class="text-sm whitespace-pre-line">
                {company.notes}
            </Card.Content>
        </Card.Root>
    {/if}

    <section class="space-y-3">
        <div>
            <h3 class="text-base font-medium">Ansprechpartner</h3>
            <p class="text-sm text-muted-foreground">
                Die Rolle gilt gegenüber dieser Firma. Dieselbe Person kann bei einer
                anderen Praxis eine andere Rolle haben (D-005).
            </p>
        </div>

        <div class="overflow-x-auto rounded-md border">
            <Table.Root>
                <Table.Header>
                    <Table.Row>
                        <Table.Head>Name</Table.Head>
                        <Table.Head>Rolle</Table.Head>
                        <Table.Head>Kontakt</Table.Head>
                        <Table.Head>Portal-Zugang</Table.Head>
                    </Table.Row>
                </Table.Header>
                <Table.Body>
                    {#each company.contacts as contact (contact.id)}
                        <Table.Row>
                            <Table.Cell class="align-top">
                                <div class="flex items-center gap-2">
                                    <span class="font-medium">{contact.name}</span>
                                    {#if contact.isPrimary}
                                        <Badge
                                            variant="outline"
                                            class="text-muted-foreground"
                                        >
                                            Hauptkontakt
                                        </Badge>
                                    {/if}
                                </div>
                            </Table.Cell>
                            <Table.Cell class="align-top">
                                {contact.role ?? '—'}
                                {#if contact.department}
                                    <span class="block text-xs text-muted-foreground">
                                        {contact.department}
                                    </span>
                                {/if}
                            </Table.Cell>
                            <Table.Cell class="align-top">
                                <ChannelList channels={contact.channels} compact />
                            </Table.Cell>
                            <Table.Cell class="align-top">
                                <AccessBadge account={contact.account} />
                            </Table.Cell>
                        </Table.Row>
                    {:else}
                        <Table.Row>
                            <Table.Cell
                                colspan={4}
                                class="h-24 text-center text-muted-foreground"
                            >
                                Noch kein Ansprechpartner hinterlegt.
                            </Table.Cell>
                        </Table.Row>
                    {/each}
                </Table.Body>
            </Table.Root>
        </div>
    </section>
</div>
