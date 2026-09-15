<script lang="ts">
    import type { Component } from 'svelte';
    import { Link, setLayoutProps } from '@inertiajs/svelte';
    import ArrowLeft from '@lucide/svelte/icons/arrow-left';
    import CalendarPlus from '@lucide/svelte/icons/calendar-plus';
    import Clock from '@lucide/svelte/icons/clock';
    import Cloud from '@lucide/svelte/icons/cloud';
    import KeyRound from '@lucide/svelte/icons/key-round';
    import Mail from '@lucide/svelte/icons/mail';
    import Pencil from '@lucide/svelte/icons/pencil';
    import ShieldCheck from '@lucide/svelte/icons/shield-check';
    import AppHead from '@/components/AppHead.svelte';
    import * as Avatar from '@/components/ui/avatar';
    import { Badge } from '@/components/ui/badge';
    import { edit, index } from '@/routes/erp/employees';
    import StatusBadge from './StatusBadge.svelte';
    import type { EmployeeProfile } from './profile';

    /**
     * Ein Mitarbeiter.
     *
     * Eine durchgehende Liste statt Abschnitten: die acht Angaben sind alle
     * gleichrangig, und zwei Überschriften darüber zwingen nur dazu, zweimal
     * zu suchen. Die Beschriftungen tragen Symbole, damit man die gesuchte
     * Zeile ohne Lesen findet.
     */
    let { employee }: { employee: EmployeeProfile } = $props();

    // Löschen sitzt im Bearbeiten-Formular, nicht hier — eine zerstörende
    // Aktion gehört nicht neben eine reine Ansicht.
    $effect(() => {
        setLayoutProps({
            actions: [{ label: 'Bearbeiten', icon: Pencil, href: edit(employee.id).url }],
        });
    });

    const initialen = $derived(
        `${employee.firstName[0] ?? ''}${employee.lastName[0] ?? ''}`.toUpperCase(),
    );

    type Zeile = { icon: Component; label: string; value: string };

    const zeilen: Zeile[] = $derived([
        { icon: Mail, label: 'E-Mail', value: employee.email },
        {
            icon: KeyRound,
            label: 'Passwort',
            value: employee.anmeldung.hasPassword ? 'gesetzt' : 'nicht gesetzt',
        },
        {
            icon: ShieldCheck,
            label: 'Zwei Faktoren',
            value: employee.anmeldung.twoFactorConfirmedAt
                ? `bestätigt am ${employee.anmeldung.twoFactorConfirmedAt}`
                : 'nicht eingerichtet',
        },
        {
            icon: Cloud,
            label: 'Microsoft-Konto',
            value: employee.anmeldung.entraOid ?? 'noch nicht verknüpft',
        },
        {
            icon: Clock,
            label: 'Zuletzt angemeldet',
            value: employee.anmeldung.lastLoginAt ?? 'noch nie',
        },
        {
            icon: CalendarPlus,
            label: 'Angelegt',
            value: employee.angelegtAm ?? '—',
        },
    ]);
</script>

<AppHead title={employee.name} />

<div class="space-y-6">
    <Link
        href={index().url}
        class="inline-flex items-center gap-1 text-sm text-muted-foreground underline-offset-4 hover:underline"
    >
        <ArrowLeft class="size-4" />
        Alle Mitarbeiter
    </Link>

    <header class="flex flex-wrap items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <!--
                Das Foto kommt aus dem Object Storage und wird nicht hier
                gepflegt (`photo_path` ist nicht `$fillable`). Die Initialen
                tragen den Fall, dass keines hinterlegt ist.
            -->
            <Avatar.Root class="size-12">
                {#if employee.photoUrl}
                    <Avatar.Image src={employee.photoUrl} alt={employee.name} />
                {/if}
                <Avatar.Fallback>{initialen}</Avatar.Fallback>
            </Avatar.Root>

            <div>
                <h2 class="text-xl font-semibold tracking-tight">{employee.name}</h2>
                <p class="text-sm text-muted-foreground">{employee.role.name}</p>
            </div>
        </div>

        <div class="flex shrink-0 flex-wrap items-center gap-2">
            <StatusBadge
                active={employee.flags.active}
                hasPassword={employee.anmeldung.hasPassword}
            />
            {#if employee.flags.admin}
                <!-- Bootstrap-/IT-Bypass (D-028), kein Ersatz für eine Rolle. -->
                <Badge variant="outline" class="text-muted-foreground">Bypass</Badge>
            {/if}
        </div>
    </header>

    <dl class="space-y-2.5 text-sm">
        {#each zeilen as zeile (zeile.label)}
            <div class="flex items-baseline gap-3">
                <dt
                    class="flex w-48 shrink-0 items-baseline gap-2 text-muted-foreground"
                >
                    <zeile.icon class="size-4 shrink-0 translate-y-0.5" />
                    {zeile.label}
                </dt>
                <dd>{zeile.value}</dd>
            </div>
        {/each}
    </dl>

    <!--
        Der Passwort-Login ist die Übergangslösung. Ziel ist Microsoft-Entra-SSO
        (D-029) — danach ist „Passwort nicht gesetzt" der Normalfall und kein
        Mangel. Was die Rolle erlaubt, entscheidet der Berechtigungskatalog; der
        ist noch nicht gebaut.
    -->
</div>
