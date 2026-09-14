<script lang="ts">
    import { Link } from '@inertiajs/svelte';
    import ArrowLeft from '@lucide/svelte/icons/arrow-left';
    import Boxes from '@lucide/svelte/icons/boxes';
    import Building from '@lucide/svelte/icons/building';
    import ClipboardCheck from '@lucide/svelte/icons/clipboard-check';
    import FileText from '@lucide/svelte/icons/file-text';
    import HardDrive from '@lucide/svelte/icons/hard-drive';
    import MapPin from '@lucide/svelte/icons/map-pin';
    import ReceiptText from '@lucide/svelte/icons/receipt-text';
    import TrendingUp from '@lucide/svelte/icons/trending-up';
    import Wrench from '@lucide/svelte/icons/wrench';
    import AppHead from '@/components/AppHead.svelte';
    import { Badge } from '@/components/ui/badge';
    import * as Empty from '@/components/ui/empty';
    import { Separator } from '@/components/ui/separator';
    import * as Table from '@/components/ui/table';
    import * as Tabs from '@/components/ui/tabs';
    import { index } from '@/routes/erp/companies';
    import AccessBadge from './AccessBadge.svelte';
    import ChannelList from './ChannelList.svelte';
    import type { CompanyProfile } from './profile';

    /**
     * Die Firma auf einen Blick.
     *
     * Bewusst ohne Karten: gerahmte Kästen setzen sieben Angaben optisch
     * gleichrangig nebeneinander und zwingen das Auge, jeden Rahmen einzeln
     * abzusuchen. Was man tatsächlich sucht — Anschrift und Telefonnummer —
     * steht deshalb oben und unverpackt, alles Belegmäßige darunter.
     *
     * Die Ansprechpartner sind keine `DataTable`: es gibt nichts zu
     * durchsuchen, zu sortieren oder zu blättern. Die zentrale Listenmechanik
     * ist für den Adressstamm gebaut, nicht für sechs Zeilen.
     */
    let { company }: { company: CompanyProfile } = $props();

    const weitereAngaben = $derived(Object.entries(company.stammdaten));
    const bank = $derived(Object.entries(company.bank));
    const hatZustaendige = $derived(
        Boolean(company.responsible.sales || company.responsible.service),
    );

    /**
     * Die Reiter der Akte.
     *
     * Bis auf die Ansprechpartner sind sie leer — die Datensätze dahinter
     * entstehen mit den Fachmodulen (ADR-031). Sie stehen trotzdem schon da,
     * weil die Akte sonst nicht erkennen lässt, was zu einer Firma überhaupt
     * gehört. Wer ein Modul baut, ersetzt hier den leeren Zustand durch seine
     * Liste und sonst nichts.
     */
    const akten = [
        { value: 'verkaufschancen', label: 'Verkaufschancen', icon: TrendingUp },
        { value: 'servicefaelle', label: 'Servicefälle', icon: Wrench },
        { value: 'wartungen', label: 'Wartungen', icon: ClipboardCheck },
        { value: 'geraete', label: 'Geräte', icon: HardDrive },
        { value: 'vertraege', label: 'Verträge', icon: FileText },
        { value: 'rechnungen', label: 'Rechnungen', icon: ReceiptText },
        { value: 'artikel', label: 'Artikel', icon: Boxes },
    ];
</script>

<AppHead title={company.name} />

<div class="space-y-6">
    <Link
        href={index().url}
        class="inline-flex items-center gap-1 text-sm text-muted-foreground underline-offset-4 hover:underline"
    >
        <ArrowLeft class="size-4" />
        Alle Firmen
    </Link>

    <header class="flex items-start justify-between gap-4">
        <div>
            <h2 class="text-xl font-semibold tracking-tight">{company.name}</h2>
            {#if company.nameAddition}
                <p class="text-sm text-muted-foreground">{company.nameAddition}</p>
            {/if}
        </div>

        <div class="flex shrink-0 flex-col items-end gap-1.5">
            {#if company.debitorNumber}
                <Badge variant="secondary" class="font-mono">
                    {company.debitorNumber}
                </Badge>
            {/if}
            {#if company.avv.signed}
                <Badge variant="outline" class="text-muted-foreground">
                    AVV {company.avv.signedAt ?? company.avv.label}
                </Badge>
            {:else}
                <Badge variant="outline" class="text-muted-foreground">
                    AVV {company.avv.label}
                </Badge>
            {/if}
        </div>
    </header>

    <div class="grid gap-x-10 gap-y-5 sm:grid-cols-2">
        <div class="flex gap-2.5">
            <MapPin class="size-4 shrink-0 translate-y-0.5 text-muted-foreground" />
            {#if company.address}
                <address class="text-sm leading-relaxed not-italic">
                    {company.address.street}<br />
                    {company.address.city}
                    {#if company.address.district}
                        <br /><span class="text-muted-foreground">
                            {company.address.district}
                        </span>
                    {/if}
                </address>
            {:else}
                <span class="text-sm text-muted-foreground">
                    Keine Sitzadresse hinterlegt.
                </span>
            {/if}
        </div>

        <ChannelList channels={company.channels} />
    </div>

    <Separator />

    <section class="space-y-2">
        <h3 class="text-xs tracking-wide text-muted-foreground uppercase">
            Standorte
        </h3>
        <!--
            Geräte und Serviceverträge hängen später am Standort, nicht an der
            Firma (D-007) — deshalb stehen sie hier, auch wenn der Hauptstandort
            meist die Sitzadresse ist.
        -->
        <ul class="space-y-2">
            {#each company.locations as location (location.id)}
                <li class="flex gap-2.5 text-sm">
                    <Building class="size-4 shrink-0 translate-y-0.5 text-muted-foreground" />
                    <div>
                        <span class="font-medium">{location.name}</span>
                        {#if location.isPrimary}
                            <span class="ms-2 text-xs text-muted-foreground">
                                Hauptstandort
                            </span>
                        {/if}
                        <div class="text-muted-foreground">
                            {#if location.sameAsCompanyAddress}
                                wie Sitzadresse
                            {:else if location.address}
                                {location.address.street}, {location.address.city}
                            {:else}
                                keine Adresse hinterlegt
                            {/if}
                        </div>
                    </div>
                </li>
            {:else}
                <li class="text-sm text-muted-foreground">Kein Standort hinterlegt.</li>
            {/each}
        </ul>
    </section>

    {#if weitereAngaben.length > 0 || bank.length > 0 || hatZustaendige || company.billingCompany || company.notes}
        <Separator />

        <div class="grid gap-x-10 gap-y-6 sm:grid-cols-2">
            {#if weitereAngaben.length > 0}
                <section class="space-y-2">
                    <h3 class="text-xs tracking-wide text-muted-foreground uppercase">
                        Stammdaten
                    </h3>
                    <dl class="space-y-1 text-sm">
                        {#each weitereAngaben as [label, value] (label)}
                            <div class="flex gap-3">
                                <dt class="w-36 shrink-0 text-muted-foreground">{label}</dt>
                                <dd>{value}</dd>
                            </div>
                        {/each}
                    </dl>
                </section>
            {/if}

            {#if bank.length > 0}
                <section class="space-y-2">
                    <h3 class="text-xs tracking-wide text-muted-foreground uppercase">
                        Bankverbindung
                    </h3>
                    <dl class="space-y-1 text-sm">
                        {#each bank as [label, value] (label)}
                            <div class="flex gap-3">
                                <dt class="w-36 shrink-0 text-muted-foreground">{label}</dt>
                                <dd class="font-mono text-xs">{value}</dd>
                            </div>
                        {/each}
                    </dl>
                </section>
            {/if}

            {#if hatZustaendige || company.billingCompany}
                <section class="space-y-2">
                    <h3 class="text-xs tracking-wide text-muted-foreground uppercase">
                        Zuständig
                    </h3>
                    <dl class="space-y-1 text-sm">
                        <div class="flex gap-3">
                            <dt class="w-36 shrink-0 text-muted-foreground">Vertrieb</dt>
                            <dd>{company.responsible.sales ?? '—'}</dd>
                        </div>
                        <div class="flex gap-3">
                            <dt class="w-36 shrink-0 text-muted-foreground">Service</dt>
                            <dd>{company.responsible.service ?? '—'}</dd>
                        </div>
                        {#if company.billingCompany}
                            <div class="flex gap-3">
                                <dt class="w-36 shrink-0 text-muted-foreground">
                                    Rechnung an
                                </dt>
                                <dd>{company.billingCompany.name}</dd>
                            </div>
                        {/if}
                    </dl>
                    <!--
                        Informativ, KEINE Berechtigung: wer was darf, kommt aus
                        der Rolle (D-016/D-030).
                    -->
                </section>
            {/if}

            {#if company.notes}
                <section class="space-y-2">
                    <h3 class="text-xs tracking-wide text-muted-foreground uppercase">
                        Notiz
                    </h3>
                    <p class="text-sm whitespace-pre-line">{company.notes}</p>
                </section>
            {/if}
        </div>
    {/if}

    <Separator />

    <!--
        Die Akte. Hier hängt später alles, was zu dieser Firma gehört —
        deshalb Reiter und keine weitere Überschriftenebene: die Abschnitte
        stehen nicht untereinander, sie lösen einander ab.
    -->
    <Tabs.Root value="gesamt">
        <!-- Bei acht Reitern reicht die Zeile auf schmalen Geräten nicht. -->
        <div class="overflow-x-auto">
            <Tabs.List>
                <Tabs.Trigger value="gesamt">Gesamt</Tabs.Trigger>
                <Tabs.Trigger value="ansprechpartner">Ansprechpartner</Tabs.Trigger>
                {#each akten as akte (akte.value)}
                    <Tabs.Trigger value={akte.value}>{akte.label}</Tabs.Trigger>
                {/each}
            </Tabs.List>
        </div>

        <Tabs.Content value="gesamt" class="space-y-3 pt-4">
            <p class="text-sm text-muted-foreground">
                Alles, was zu dieser Firma vorliegt. Heute sind das die
                Ansprechpartner — die übrigen Datensätze kommen mit den
                Fachmodulen dazu.
            </p>
            {@render ansprechpartner()}
        </Tabs.Content>

        <Tabs.Content value="ansprechpartner" class="space-y-3 pt-4">
            <p class="text-sm text-muted-foreground">
                Die Rolle gilt gegenüber dieser Firma. Dieselbe Person kann bei einer
                anderen Praxis eine andere Rolle haben (D-005).
            </p>
            {@render ansprechpartner()}
        </Tabs.Content>

        {#each akten as akte (akte.value)}
            <Tabs.Content value={akte.value} class="pt-4">
                <Empty.Root class="border">
                    <Empty.Header>
                        <Empty.Media variant="icon">
                            <akte.icon />
                        </Empty.Media>
                        <Empty.Title>{akte.label}</Empty.Title>
                        <Empty.Description>
                            Entsteht mit dem zugehörigen Fachmodul (ADR-031).
                        </Empty.Description>
                    </Empty.Header>
                </Empty.Root>
            </Tabs.Content>
        {/each}
    </Tabs.Root>
</div>

{#snippet ansprechpartner()}
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
                                    <Badge variant="outline" class="text-muted-foreground">
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
{/snippet}
