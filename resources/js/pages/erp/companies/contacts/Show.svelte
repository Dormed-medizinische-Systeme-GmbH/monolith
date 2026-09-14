<script lang="ts">
    import { Link } from '@inertiajs/svelte';
    import ArrowLeft from '@lucide/svelte/icons/arrow-left';
    import AppHead from '@/components/AppHead.svelte';
    import { Badge } from '@/components/ui/badge';
    import { Separator } from '@/components/ui/separator';
    import * as Table from '@/components/ui/table';
    import { show as companyShow } from '@/routes/erp/companies';
    import AccessBadge from '../AccessBadge.svelte';
    import ChannelList from '../ChannelList.svelte';
    import type { ContactProfile } from './profile';

    /**
     * Ein Ansprechpartner — also eine Person IN einer Firma.
     *
     * Gleiche Sprache wie die Firmenansicht: keine Karten, Abschnitte durch
     * Linien getrennt, das Gesuchte oben. Die Rolle steht als Untertitel unter
     * dem Namen, weil sie nur gegenüber dieser Firma gilt (D-005) — sie gehört
     * zur Überschrift, nicht in eine Aufstellung.
     */
    let { contact }: { contact: ContactProfile } = $props();

    const stammdaten = $derived(Object.entries(contact.person.stammdaten));
</script>

<AppHead title={contact.person.name} />

<div class="space-y-6">
    <Link
        href={companyShow(contact.company.id).url}
        class="inline-flex items-center gap-1 text-sm text-muted-foreground underline-offset-4 hover:underline"
    >
        <ArrowLeft class="size-4" />
        {contact.company.name}
    </Link>

    <header class="flex items-start justify-between gap-4">
        <div>
            <h2 class="text-xl font-semibold tracking-tight">{contact.person.name}</h2>
            <p class="text-sm text-muted-foreground">
                {contact.role ?? 'ohne Rolle'}
                {#if contact.department}
                    · {contact.department}
                {/if}
            </p>
        </div>

        {#if contact.isPrimary}
            <Badge variant="secondary" class="shrink-0">Hauptkontakt</Badge>
        {/if}
    </header>

    <ChannelList channels={contact.channels} />

    <Separator />

    <div class="grid gap-x-10 gap-y-6 sm:grid-cols-2">
        <section class="space-y-2">
            <h3 class="text-xs tracking-wide text-muted-foreground uppercase">
                Portal-Zugang
            </h3>
            <AccessBadge account={contact.account} />
            {#if contact.account}
                <p class="text-xs text-muted-foreground">
                    {#if contact.account.verifiedAt}
                        Mailadresse bestätigt am {contact.account.verifiedAt}.
                    {:else}
                        Mailadresse noch nicht bestätigt.
                    {/if}
                </p>
            {:else}
                <!--
                    Zugang anlegen und Passwort zurücksetzen sind der eigentliche
                    Zweck dieser Ansicht (ADR-037) — beides braucht eine eigene
                    Ability (D-136) und kommt mit dem Schreibteil.
                -->
                <p class="text-xs text-muted-foreground">
                    Zugänge werden von hier aus verwaltet, sobald das Anlegen gebaut
                    ist.
                </p>
            {/if}
        </section>

        <section class="space-y-2">
            <h3 class="text-xs tracking-wide text-muted-foreground uppercase">
                Person
            </h3>
            <dl class="space-y-1 text-sm">
                {#each stammdaten as [label, value] (label)}
                    <div class="flex gap-3">
                        <dt class="w-32 shrink-0 text-muted-foreground">{label}</dt>
                        <dd>{value}</dd>
                    </div>
                {/each}
            </dl>
        </section>
    </div>

    {#if contact.weitereFirmen.length > 0}
        <Separator />

        <section class="space-y-2">
            <h3 class="text-xs tracking-wide text-muted-foreground uppercase">
                Auch Ansprechpartner bei
            </h3>
            <!--
                Ohne diesen Abschnitt sähe eine Person mit zwei Praxen genauso
                aus wie eine mit einer — und genau daran hängt die offene
                Frage 2 aus ADR-037.
            -->
            <ul class="space-y-1 text-sm">
                {#each contact.weitereFirmen as firma (firma.id)}
                    <li>
                        <Link
                            href={companyShow(firma.id).url}
                            class="font-medium underline-offset-4 hover:underline"
                        >
                            {firma.name}
                        </Link>
                        <span class="text-muted-foreground">
                            {firma.role ? `· ${firma.role}` : ''}
                        </span>
                    </li>
                {/each}
            </ul>
        </section>
    {/if}

    <Separator />

    <section class="space-y-3">
        <div>
            <h3 class="text-base font-medium">Einwilligungen</h3>
            <p class="text-sm text-muted-foreground">
                Der jeweils aktuelle Stand je Kanal. Ein Widerruf ändert keine
                Einwilligung, er schreibt eine neue fort (D-013) — die älteren
                Einträge bleiben als Nachweis erhalten.
            </p>
        </div>

        <div class="overflow-x-auto rounded-md border">
            <Table.Root>
                <Table.Header>
                    <Table.Row>
                        <Table.Head>Kanal</Table.Head>
                        <Table.Head>Stand</Table.Head>
                        <Table.Head>Seit</Table.Head>
                        <Table.Head>Quelle</Table.Head>
                    </Table.Row>
                </Table.Header>
                <Table.Body>
                    {#each contact.consents as consent (consent.id)}
                        <Table.Row>
                            <Table.Cell class="font-medium">{consent.channel}</Table.Cell>
                            <Table.Cell>
                                {#if consent.granted}
                                    <Badge variant="secondary">{consent.status}</Badge>
                                {:else}
                                    <Badge variant="outline" class="text-muted-foreground">
                                        {consent.status}
                                    </Badge>
                                {/if}
                            </Table.Cell>
                            <Table.Cell class="text-muted-foreground">
                                {consent.date ?? '—'}
                            </Table.Cell>
                            <Table.Cell class="text-muted-foreground">
                                {consent.source}
                            </Table.Cell>
                        </Table.Row>
                    {:else}
                        <Table.Row>
                            <Table.Cell
                                colspan={4}
                                class="h-24 text-center text-muted-foreground"
                            >
                                Keine Einwilligung hinterlegt.
                            </Table.Cell>
                        </Table.Row>
                    {/each}
                </Table.Body>
            </Table.Root>
        </div>
    </section>
</div>
