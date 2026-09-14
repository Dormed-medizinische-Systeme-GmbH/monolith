<script lang="ts">
    import { Link } from '@inertiajs/svelte';
    import ArrowLeft from '@lucide/svelte/icons/arrow-left';
    import AppHead from '@/components/AppHead.svelte';
    import { Badge } from '@/components/ui/badge';
    import { Separator } from '@/components/ui/separator';
    import { index } from '@/routes/erp/articles';
    import PublicationBadges from './PublicationBadges.svelte';
    import type { ArticleProfile } from './profile';

    /**
     * Der Artikel auf einen Blick — gleiche Sprache wie die Firmenansicht:
     * keine Karten, Abschnitte durch Linien getrennt, das Gesuchte oben.
     *
     * Der Abschnitt „Merkmale" ist der eigentliche Unterschied zu einem
     * gewöhnlichen Stammdatensatz: die Felder kommen aus der Artikelgruppe
     * (D-100), nicht aus dem Schema. Ein Pflichtfeld ohne Wert steht deshalb
     * sichtbar leer da, statt einfach zu fehlen.
     */
    let { article }: { article: ArticleProfile } = $props();

    const preise = $derived(Object.entries(article.preise));
    const stammdaten = $derived(Object.entries(article.stammdaten));
</script>

<AppHead title={article.name} />

<div class="space-y-6">
    <Link
        href={index().url}
        class="inline-flex items-center gap-1 text-sm text-muted-foreground underline-offset-4 hover:underline"
    >
        <ArrowLeft class="size-4" />
        Alle Artikel
    </Link>

    <header class="flex items-start justify-between gap-4">
        <div>
            <h2 class="text-xl font-semibold tracking-tight">{article.name}</h2>
            <p class="text-sm text-muted-foreground">
                {article.group.path.join(' › ')}
            </p>
        </div>

        <div class="flex shrink-0 flex-col items-end gap-1.5">
            <Badge variant="secondary" class="font-mono">{article.articleNumber}</Badge>
            <PublicationBadges
                active={article.flags.active}
                isPublic={article.flags.public}
                orderable={article.flags.orderable}
            />
        </div>
    </header>

    {#if article.description}
        <p class="max-w-prose text-sm whitespace-pre-line">{article.description}</p>
    {/if}

    {#if article.flags.serialTracked || article.flags.serviceItem}
        <div class="flex flex-wrap gap-2">
            {#if article.flags.serialTracked}
                <!-- Steuert, ob es Exemplare gibt (D-099). -->
                <Badge variant="outline" class="text-muted-foreground">
                    seriennummernpflichtig
                </Badge>
            {/if}
            {#if article.flags.serviceItem}
                <!-- Erscheint im Artikel-Tab des Technikers (D-109). -->
                <Badge variant="outline" class="text-muted-foreground">
                    servicerelevant
                </Badge>
            {/if}
        </div>
    {/if}

    <Separator />

    <div class="grid gap-x-10 gap-y-6 sm:grid-cols-2">
        <section class="space-y-2">
            <h3 class="text-xs tracking-wide text-muted-foreground uppercase">Preise</h3>
            <!--
                Listenpreise. Beim Einfügen in eine Position wird der Preis
                gesnapshottet (D-104) — eine Änderung hier wirkt nie rückwirkend.
            -->
            <dl class="space-y-1 text-sm">
                {#each preise as [label, value] (label)}
                    <div class="flex gap-3">
                        <dt class="w-32 shrink-0 text-muted-foreground">{label}</dt>
                        <dd>{value}</dd>
                    </div>
                {/each}
            </dl>
        </section>

        {#if stammdaten.length > 0}
            <section class="space-y-2">
                <h3 class="text-xs tracking-wide text-muted-foreground uppercase">
                    Stammdaten
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
        {/if}
    </div>

    <Separator />

    <section class="space-y-2">
        <div>
            <h3 class="text-xs tracking-wide text-muted-foreground uppercase">
                Merkmale
            </h3>
            <p class="text-sm text-muted-foreground">
                Aus der Artikelgruppe „{article.group.name}". Obergruppen steuern
                nichts bei (D-110).
            </p>
        </div>

        {#if article.merkmale.length > 0}
            <dl class="grid gap-x-10 gap-y-1 text-sm sm:grid-cols-2">
                {#each article.merkmale as merkmal (merkmal.id)}
                    <div class="flex gap-3">
                        <dt class="w-32 shrink-0 text-muted-foreground">
                            {merkmal.label}
                        </dt>
                        <dd>
                            {#if merkmal.value}
                                {merkmal.value}
                            {:else if merkmal.mandatory}
                                <span class="text-destructive">fehlt</span>
                            {:else}
                                <span class="text-muted-foreground">—</span>
                            {/if}
                        </dd>
                    </div>
                {/each}
            </dl>
        {:else}
            <p class="text-sm text-muted-foreground">
                Für diese Gruppe sind noch keine Merkmale definiert.
            </p>
        {/if}
    </section>

    {#if article.notes}
        <Separator />

        <section class="space-y-2">
            <h3 class="text-xs tracking-wide text-muted-foreground uppercase">Notiz</h3>
            <p class="max-w-prose text-sm whitespace-pre-line">{article.notes}</p>
        </section>
    {/if}
</div>
