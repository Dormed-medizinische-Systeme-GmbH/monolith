<script lang="ts">
    import { Link, router } from '@inertiajs/svelte';
    import ArrowLeft from '@lucide/svelte/icons/arrow-left';
    import Pencil from '@lucide/svelte/icons/pencil';
    import Trash2 from '@lucide/svelte/icons/trash-2';
    import AppHead from '@/components/AppHead.svelte';
    import * as AlertDialog from '@/components/ui/alert-dialog';
    import { Badge } from '@/components/ui/badge';
    import { Button, buttonVariants } from '@/components/ui/button';
    import { Separator } from '@/components/ui/separator';
    import { destroy, edit, index } from '@/routes/erp/employees';
    import StatusBadge from './StatusBadge.svelte';
    import type { EmployeeProfile } from './profile';

    /**
     * Ein Mitarbeiter. Gleiche Bildsprache wie Firmen und Artikel.
     */
    let { employee }: { employee: EmployeeProfile } = $props();

    let loeschenOffen = $state(false);

    function loeschen(): void {
        router.delete(destroy(employee.id).url);
    }
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

    <header class="flex flex-wrap items-start justify-between gap-4">
        <div>
            <h2 class="text-xl font-semibold tracking-tight">{employee.name}</h2>
            <p class="text-sm text-muted-foreground">{employee.email}</p>
            <div class="flex flex-wrap items-center gap-2 pt-2">
                <Badge variant="secondary">{employee.role.name}</Badge>
                <StatusBadge
                    active={employee.flags.active}
                    hasPassword={employee.anmeldung.hasPassword}
                />
                {#if employee.flags.admin}
                    <!-- Bootstrap-/IT-Bypass (D-028), kein Ersatz für eine Rolle. -->
                    <Badge variant="outline" class="text-muted-foreground">Bypass</Badge>
                {/if}
            </div>
        </div>

        <div class="flex shrink-0 gap-2">
            <Link
                href={edit(employee.id).url}
                class={buttonVariants({ variant: 'outline', size: 'sm' })}
            >
                <Pencil class="size-4" />
                Bearbeiten
            </Link>
            <Button variant="outline" size="sm" onclick={() => (loeschenOffen = true)}>
                <Trash2 class="size-4" />
                Löschen
            </Button>
        </div>
    </header>

    <Separator />

    <div class="grid gap-x-10 gap-y-6 sm:grid-cols-2">
        <section class="space-y-2">
            <h3 class="text-xs tracking-wide text-muted-foreground uppercase">
                Anmeldung
            </h3>
            <dl class="space-y-1 text-sm">
                <div class="flex gap-3">
                    <dt class="w-36 shrink-0 text-muted-foreground">Passwort</dt>
                    <dd>
                        {employee.anmeldung.hasPassword ? 'gesetzt' : 'nicht gesetzt'}
                    </dd>
                </div>
                <div class="flex gap-3">
                    <dt class="w-36 shrink-0 text-muted-foreground">Zwei Faktoren</dt>
                    <dd>
                        {employee.anmeldung.twoFactorConfirmedAt
                            ? `bestätigt am ${employee.anmeldung.twoFactorConfirmedAt}`
                            : 'nicht eingerichtet'}
                    </dd>
                </div>
                <div class="flex gap-3">
                    <dt class="w-36 shrink-0 text-muted-foreground">Microsoft-Konto</dt>
                    <dd>{employee.anmeldung.entraOid ?? 'noch nicht verknüpft'}</dd>
                </div>
                <div class="flex gap-3">
                    <dt class="w-36 shrink-0 text-muted-foreground">Zuletzt</dt>
                    <dd>{employee.anmeldung.lastLoginAt ?? 'noch nie angemeldet'}</dd>
                </div>
            </dl>
            <!--
                Der Passwort-Login ist die Übergangslösung. Ziel ist
                Microsoft-Entra-SSO (D-029) — danach ist „Passwort nicht gesetzt"
                der Normalfall und kein Mangel.
            -->
        </section>

        <section class="space-y-2">
            <h3 class="text-xs tracking-wide text-muted-foreground uppercase">
                Zuordnung
            </h3>
            <dl class="space-y-1 text-sm">
                <div class="flex gap-3">
                    <dt class="w-36 shrink-0 text-muted-foreground">Rolle</dt>
                    <dd>{employee.role.name}</dd>
                </div>
                <div class="flex gap-3">
                    <dt class="w-36 shrink-0 text-muted-foreground">Angelegt</dt>
                    <dd>{employee.angelegtAm ?? '—'}</dd>
                </div>
            </dl>
            <p class="pt-1 text-xs text-muted-foreground">
                Genau eine Rolle je Mitarbeiter (D-124). Was sie erlaubt, entscheidet
                der Berechtigungskatalog — der ist noch nicht gebaut.
            </p>
        </section>
    </div>
</div>

<AlertDialog.Root bind:open={loeschenOffen}>
    <AlertDialog.Content>
        <AlertDialog.Header>
            <AlertDialog.Title>{employee.name} löschen?</AlertDialog.Title>
            <AlertDialog.Description>
                Der Datensatz bleibt erhalten und wird nur ausgeblendet (D-018) —
                Spuren in anderen Datensätzen laufen dadurch nicht ins Leere. Der
                Zugang ist danach gesperrt.
            </AlertDialog.Description>
        </AlertDialog.Header>
        <AlertDialog.Footer>
            <AlertDialog.Cancel>Abbrechen</AlertDialog.Cancel>
            <AlertDialog.Action onclick={loeschen}>Löschen</AlertDialog.Action>
        </AlertDialog.Footer>
    </AlertDialog.Content>
</AlertDialog.Root>
