<script lang="ts">
    import { Form, Link, router, setLayoutProps } from '@inertiajs/svelte';
    import ArrowLeft from '@lucide/svelte/icons/arrow-left';
    import Save from '@lucide/svelte/icons/save';
    import Trash from '@lucide/svelte/icons/trash';
    import AppHead from '@/components/AppHead.svelte';
    import Heading from '@/components/Heading.svelte';
    import InputError from '@/components/InputError.svelte';
    import * as AlertDialog from '@/components/ui/alert-dialog';
    import { Field, FieldGroup, FieldLabel } from '@/components/ui/field';
    import { Input } from '@/components/ui/input';
    import { Textarea } from '@/components/ui/textarea';
    import { destroy, index, show, store, update } from '@/routes/erp/sites';
    import type { SiteProfile } from './profile';

    /**
     * Anlegen und Bearbeiten in einer Maske, wie bei den Mitarbeitern.
     *
     * Das Standortbild fehlt bewusst: `photo_path` ist nicht `$fillable` und
     * kommt aus dem Seed.
     */
    let { site = null }: { site?: SiteProfile | null } = $props();

    const neu = $derived(site === null);

    let loeschenOffen = $state(false);
    let formular: ReturnType<typeof Form> | undefined = $state();

    function loeschen(): void {
        router.delete(destroy(site!.id).url);
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
</script>

<AppHead title={neu ? 'Betriebsstätte anlegen' : `${site?.name} bearbeiten`} />

<div class="space-y-6">
    <Link
        href={neu ? index().url : show(site!.id).url}
        class="inline-flex items-center gap-1 text-sm text-muted-foreground underline-offset-4 hover:underline"
    >
        <ArrowLeft class="size-4" />
        {neu ? 'Alle Betriebsstätten' : site?.name}
    </Link>

    <Heading
        title={neu ? 'Betriebsstätte anlegen' : 'Betriebsstätte bearbeiten'}
        description="Ein eigener Standort von Dormed — nicht der eines Kunden."
    />

    <Form
        bind:this={formular}
        {...(neu ? store.form() : update.form(site!.id))}
        class="max-w-xl"
    >
        {#snippet children({ errors })}
            <FieldGroup>
                <Field>
                    <FieldLabel for="name">Name</FieldLabel>
                    <Input id="name" name="name" required autofocus value={site?.name ?? ''} />
                    <InputError message={errors.name} />
                </Field>

                <Field>
                    <FieldLabel for="street">Straße und Hausnummer</FieldLabel>
                    <!--
                        Ein Feld, anders als bei den Kundenadressen: dort hängen
                        Geokodierung und Fahrtzone daran (D-024), hier sind es
                        vier Standorte, die niemand auswertet.
                    -->
                    <Input id="street" name="street" value={site?.address.street ?? ''} />
                    <InputError message={errors.street} />
                </Field>

                <div class="grid gap-4 sm:grid-cols-[auto_1fr]">
                    <Field>
                        <FieldLabel for="postal_code">PLZ</FieldLabel>
                        <Input
                            id="postal_code"
                            name="postal_code"
                            class="w-28"
                            value={site?.address.postalCode ?? ''}
                        />
                        <InputError message={errors.postal_code} />
                    </Field>

                    <Field>
                        <FieldLabel for="city">Ort</FieldLabel>
                        <Input id="city" name="city" value={site?.address.city ?? ''} />
                        <InputError message={errors.city} />
                    </Field>
                </div>

                <Field>
                    <FieldLabel for="notes">Notiz</FieldLabel>
                    <Textarea id="notes" name="notes" rows={3} value={site?.notes ?? ''} />
                    <InputError message={errors.notes} />
                </Field>

            </FieldGroup>
        {/snippet}
    </Form>
</div>

{#if !neu}
    <AlertDialog.Root bind:open={loeschenOffen}>
        <AlertDialog.Content>
            <AlertDialog.Header>
                <AlertDialog.Title>{site?.name} löschen?</AlertDialog.Title>
                <AlertDialog.Description>
                    Der Datensatz bleibt erhalten und wird nur ausgeblendet (D-018).
                    Solange dem Standort Mitarbeiter zugeordnet sind, wird das
                    abgelehnt — sie zeigten sonst auf etwas, das es in der Auswahl
                    nicht mehr gibt.
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
