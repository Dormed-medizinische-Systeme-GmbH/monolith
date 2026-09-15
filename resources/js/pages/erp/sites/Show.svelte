<script lang="ts">
    import { Link, setLayoutProps } from '@inertiajs/svelte';
    import ArrowLeft from '@lucide/svelte/icons/arrow-left';
    import MapPin from '@lucide/svelte/icons/map-pin';
    import Pencil from '@lucide/svelte/icons/pencil';
    import Users from '@lucide/svelte/icons/users';
    import AppHead from '@/components/AppHead.svelte';
    import * as Avatar from '@/components/ui/avatar';
    import { Separator } from '@/components/ui/separator';
    import { show as employeeShow } from '@/routes/erp/employees';
    import { edit, index } from '@/routes/erp/sites';
    import ActiveBadge from './ActiveBadge.svelte';
    import type { SiteProfile } from './profile';

    let { site }: { site: SiteProfile } = $props();

    $effect(() => {
        setLayoutProps({
            actions: [{ label: 'Bearbeiten', icon: Pencil, href: edit(site.id).url }],
        });
    });

    function initialen(name: string): string {
        return name
            .split(' ')
            .map((teil) => teil[0] ?? '')
            .slice(0, 2)
            .join('')
            .toUpperCase();
    }
</script>

<AppHead title={site.name} />

<div class="space-y-6">
    <Link
        href={index().url}
        class="inline-flex items-center gap-1 text-sm text-muted-foreground underline-offset-4 hover:underline"
    >
        <ArrowLeft class="size-4" />
        Alle Betriebsstätten
    </Link>

    <header class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h2 class="text-xl font-semibold tracking-tight">{site.name}</h2>
            {#if site.shortName}
                <p class="text-sm text-muted-foreground">{site.shortName}</p>
            {/if}
        </div>

        <ActiveBadge active={site.isActive} />
    </header>

    {#if site.photoUrl}
        <!--
            Aussenansicht aus dem Object Storage. Der Browser holt sie direkt
            beim Speicher, die Anwendung steht nicht im Abrufweg (ADR-045).
        -->
        <img
            src={site.photoUrl}
            alt={`Außenansicht ${site.name}`}
            class="max-h-64 w-full rounded-md border object-cover"
        />
    {/if}

    <div class="flex gap-3 text-sm">
        <MapPin class="size-4 shrink-0 translate-y-0.5 text-muted-foreground" />
        {#if site.address.line}
            <address class="leading-relaxed not-italic">
                {[site.address.street, site.address.houseNumber].filter(Boolean).join(' ')}<br />
                {[site.address.postalCode, site.address.city].filter(Boolean).join(' ')}
            </address>
        {:else}
            <span class="text-muted-foreground">Keine Anschrift hinterlegt.</span>
        {/if}
    </div>

    {#if site.notes}
        <p class="max-w-prose text-sm whitespace-pre-line">{site.notes}</p>
    {/if}

    <Separator />

    <section class="space-y-3">
        <div class="flex items-center gap-2">
            <Users class="size-4 text-muted-foreground" />
            <h3 class="text-base font-medium">
                Mitarbeiter ({site.users.length})
            </h3>
        </div>
        <!--
            Steht hier, weil es die Frage beantwortet, die vor dem Stilllegen
            kommt — und weil ein Standort mit Mitarbeitern nicht gelöscht werden
            kann.
        -->

        {#if site.users.length > 0}
            <ul class="grid gap-3 sm:grid-cols-2">
                {#each site.users as user (user.id)}
                    <li>
                        <Link
                            href={employeeShow(user.id).url}
                            class="flex items-center gap-3 rounded-md border p-2 transition-colors hover:bg-muted/50"
                        >
                            <Avatar.Root class="size-9">
                                {#if user.photoUrl}
                                    <Avatar.Image src={user.photoUrl} alt={user.name} />
                                {/if}
                                <Avatar.Fallback>{initialen(user.name)}</Avatar.Fallback>
                            </Avatar.Root>
                            <div class="min-w-0">
                                <div class="truncate text-sm font-medium">{user.name}</div>
                                <div class="truncate text-xs text-muted-foreground">
                                    {user.role ?? '—'}{user.isActive ? '' : ' · gesperrt'}
                                </div>
                            </div>
                        </Link>
                    </li>
                {/each}
            </ul>
        {:else}
            <p class="text-sm text-muted-foreground">
                Diesem Standort ist niemand zugeordnet.
            </p>
        {/if}
    </section>
</div>
