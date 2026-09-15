<script lang="ts">
    import { Form, Link, router, setLayoutProps } from '@inertiajs/svelte';
    import ArrowLeft from '@lucide/svelte/icons/arrow-left';
    import Save from '@lucide/svelte/icons/save';
    import Trash from '@lucide/svelte/icons/trash';
    import AppHead from '@/components/AppHead.svelte';
    import * as AlertDialog from '@/components/ui/alert-dialog';
    import Heading from '@/components/Heading.svelte';
    import InputError from '@/components/InputError.svelte';
    import { Field, FieldDescription, FieldGroup, FieldLabel } from '@/components/ui/field';
    import { Input } from '@/components/ui/input';
    import { NativeSelect } from '@/components/ui/native-select';
    import { Switch } from '@/components/ui/switch';
    import { destroy, index, show, store, update } from '@/routes/erp/employees';
    import type { EmployeeProfile, RoleOption } from './profile';

    /**
     * Anlegen und Bearbeiten in einer Maske — die Felder sind dieselben, nur
     * das Ziel unterscheidet sich. Zwei Masken auseinanderzuhalten hiesse, jede
     * Feldänderung doppelt zu pflegen.
     */
    let {
        employee = null,
        roles,
    }: { employee?: EmployeeProfile | null; roles: RoleOption[] } = $props();

    const neu = $derived(employee === null);

    /*
     * Schalter sind keine Formularfelder: bits-ui rendert einen Button. Der
     * Wert geht deshalb über ein verstecktes Feld mit — ein nicht gesetzter
     * Schalter sendete sonst gar nichts, und `boolean` in der Prüfung schlüge
     * fehl statt `false` zu lesen.
     */
    let loeschenOffen = $state(false);

    /*
     * Die Instanz des Formulars. „Speichern" sitzt in der App-Kopfzeile und
     * damit ausserhalb des `<form>` — abgeschickt wird deshalb von hier aus,
     * statt sich auf einen Submit-Button im Baum zu verlassen.
     */
    let formular: ReturnType<typeof Form> | undefined = $state();

    function loeschen(): void {
        router.delete(destroy(employee!.id).url);
    }

    $effect(() => {
        setLayoutProps({
            actions: [
                ...(neu
                    ? []
                    : [
                          {
                              label: 'Löschen',
                              icon: Trash,
                              variant: 'outline' as const,
                              iconOnly: true,
                              destructive: true,
                              onSelect: () => (loeschenOffen = true),
                          },
                      ]),
                {
                    label: neu ? 'Anlegen' : 'Speichern',
                    icon: Save,
                    variant: 'default' as const,
                    onSelect: () => formular?.submit(),
                },
            ],
        });
    });

    let aktivGewaehlt = $state<boolean | null>(null);
    let bypassGewaehlt = $state<boolean | null>(null);

    const aktiv = $derived(aktivGewaehlt ?? employee?.flags.active ?? true);
    const bypass = $derived(bypassGewaehlt ?? employee?.flags.admin ?? false);
</script>

<AppHead title={neu ? 'Mitarbeiter anlegen' : `${employee?.name} bearbeiten`} />

<div class="space-y-6">
    <Link
        href={neu ? index().url : show(employee!.id).url}
        class="inline-flex items-center gap-1 text-sm text-muted-foreground underline-offset-4 hover:underline"
    >
        <ArrowLeft class="size-4" />
        {neu ? 'Alle Mitarbeiter' : employee?.name}
    </Link>

    <Heading
        title={neu ? 'Mitarbeiter anlegen' : 'Mitarbeiter bearbeiten'}
        description="Zugänge entstehen ausschließlich hier — es gibt keine Registrierung (D-032)."
    />

    <Form
        bind:this={formular}
        {...(neu ? store.form() : update.form(employee!.id))}
        class="max-w-xl"
        resetOnSuccess={['password']}
    >
        {#snippet children({ errors })}
            <FieldGroup>
                <div class="grid gap-4 sm:grid-cols-2">
                    <Field>
                        <FieldLabel for="first_name">Vorname</FieldLabel>
                        <Input
                            id="first_name"
                            name="first_name"
                            required
                            autofocus
                            value={employee?.firstName ?? ''}
                        />
                        <InputError message={errors.first_name} />
                    </Field>

                    <Field>
                        <FieldLabel for="last_name">Nachname</FieldLabel>
                        <Input
                            id="last_name"
                            name="last_name"
                            required
                            value={employee?.lastName ?? ''}
                        />
                        <InputError message={errors.last_name} />
                    </Field>
                </div>

                <Field>
                    <FieldLabel for="email">E-Mail</FieldLabel>
                    <Input
                        id="email"
                        name="email"
                        type="email"
                        required
                        autocomplete="off"
                        placeholder="vorname.nachname@dormed.de"
                        value={employee?.email ?? ''}
                    />
                    <FieldDescription>Zugleich der Anmeldename.</FieldDescription>
                    <InputError message={errors.email} />
                </Field>

                <Field>
                    <FieldLabel for="password">
                        {neu ? 'Passwort' : 'Neues Passwort'}
                    </FieldLabel>
                    <Input
                        id="password"
                        name="password"
                        type="password"
                        autocomplete="new-password"
                    />
                    <FieldDescription>
                        {neu
                            ? 'Optional. Ohne Passwort ist der Zugang angelegt, aber noch nicht nutzbar — der Regelfall, sobald Microsoft-Anmeldung übernimmt (D-029).'
                            : 'Leer lassen, um das bestehende Passwort zu behalten.'}
                    </FieldDescription>
                    <InputError message={errors.password} />
                </Field>

                <Field>
                    <FieldLabel for="role_id">Rolle</FieldLabel>
                    <NativeSelect
                        id="role_id"
                        name="role_id"
                        required
                        class="w-full"
                        value={employee?.role.id ?? ''}
                    >
                        <option value="" disabled>Bitte wählen</option>
                        {#each roles as role (role.id)}
                            <option value={role.id}>{role.name}</option>
                        {/each}
                    </NativeSelect>
                    <FieldDescription>
                        Genau eine Rolle je Mitarbeiter (D-124).
                    </FieldDescription>
                    <InputError message={errors.role_id} />
                </Field>

                <Field orientation="horizontal">
                    <Switch
                        id="is_active"
                        checked={aktiv}
                        onCheckedChange={(v) => (aktivGewaehlt = Boolean(v))}
                    />
                    <input type="hidden" name="is_active" value={aktiv ? '1' : '0'} />
                    <div>
                        <FieldLabel for="is_active">Zugang aktiv</FieldLabel>
                        <FieldDescription>
                            Stillgelegt heißt: keine Anmeldung, und aus den
                            Zuständigkeiten ausgeblendet.
                        </FieldDescription>
                    </div>
                    <InputError message={errors.is_active} />
                </Field>

                <Field orientation="horizontal">
                    <Switch
                        id="is_admin"
                        checked={bypass}
                        onCheckedChange={(v) => (bypassGewaehlt = Boolean(v))}
                    />
                    <input type="hidden" name="is_admin" value={bypass ? '1' : '0'} />
                    <div>
                        <FieldLabel for="is_admin">Administrativer Bypass</FieldLabel>
                        <FieldDescription>
                            Umgeht den Berechtigungskatalog vollständig (D-028). Kein
                            Ersatz für eine Rolle — nur für den Notfall gedacht.
                        </FieldDescription>
                    </div>
                    <InputError message={errors.is_admin} />
                </Field>

            </FieldGroup>
        {/snippet}
    </Form>
</div>

{#if !neu}
    <AlertDialog.Root bind:open={loeschenOffen}>
        <AlertDialog.Content>
            <AlertDialog.Header>
                <AlertDialog.Title>{employee?.name} löschen?</AlertDialog.Title>
                <AlertDialog.Description>
                    Der Datensatz bleibt erhalten und wird nur ausgeblendet (D-018) —
                    Spuren in anderen Datensätzen laufen dadurch nicht ins Leere. Der
                    Zugang ist danach gesperrt.
                </AlertDialog.Description>
            </AlertDialog.Header>
            <AlertDialog.Footer>
                <AlertDialog.Cancel>Abbrechen</AlertDialog.Cancel>
                <AlertDialog.Action variant="destructive" onclick={loeschen}>
                    Löschen
                </AlertDialog.Action>
            </AlertDialog.Footer>
        </AlertDialog.Content>
    </AlertDialog.Root>
{/if}
