<script lang="ts">
    import { Form } from '@inertiajs/svelte';
    import InputError from '@/components/InputError.svelte';
    import { Button } from '@/components/ui/button';
    import * as Dialog from '@/components/ui/dialog';
    import { Field, FieldDescription, FieldGroup, FieldLabel } from '@/components/ui/field';
    import { Input } from '@/components/ui/input';
    import { Switch } from '@/components/ui/switch';
    import { Textarea } from '@/components/ui/textarea';
    import { store, update } from '@/routes/erp/companies/locations';
    import type { CompanyProfile } from './profile';

    /**
     * Anlegen und Bearbeiten eines Standorts — als Dialog IN der Firmenakte.
     *
     * Keine eigene Seite: die Firma ist der Rahmen und steht bereits fest. Sie
     * kommt aus der Route und ist deshalb auch kein Formularfeld — eines wäre
     * eine Einladung, einen Standort in eine fremde Praxis zu schieben.
     *
     * Der Praxis-Netzwerk-Block (D-092) fehlt bewusst; er gehört zum
     * Servicemodul und kommt mit ihm.
     */
    type Standort = CompanyProfile['locations'][number];

    let {
        open = $bindable(false),
        companyId,
        // NICHT `location`: das verdeckt das gleichnamige Browser-Global, und
        // ein Tippfehler im Namen fällt dann nicht auf, sondern liest
        // `window.location`.
        standort = null,
    }: { open?: boolean; companyId: string; standort?: Standort | null } = $props();

    const neu = $derived(standort === null);

    let hauptGewaehlt = $state<boolean | null>(null);
    const haupt = $derived(hauptGewaehlt ?? standort?.isPrimary ?? false);

    // Beim Wechsel zwischen „neu" und „bearbeiten" darf die vorige Wahl nicht
    // stehen bleiben. Der Schlüssel ist die Abhängigkeit, nicht das Objekt.
    const bearbeiteteId = $derived(standort?.id ?? null);

    $effect(() => {
        void bearbeiteteId;
        hauptGewaehlt = null;
    });
</script>

<Dialog.Root bind:open>
    <Dialog.Content class="sm:max-w-lg">
        <Dialog.Header>
            <Dialog.Title>
                {neu ? 'Standort anlegen' : `${standort?.name} bearbeiten`}
            </Dialog.Title>
            <Dialog.Description>
                Ein realer Betriebs- oder Servicestandort dieser Praxis. Geräte und
                Serviceverträge hängen später daran, nicht an der Firma (D-007).
            </Dialog.Description>
        </Dialog.Header>

        <Form
            {...(neu
                ? store.form(companyId)
                : update.form({ company: companyId, location: standort!.id }))}
            onSuccess={() => (open = false)}
        >
            {#snippet children({ errors, processing })}
                <FieldGroup>
                    <Field>
                        <FieldLabel for="location_name">Bezeichnung</FieldLabel>
                        <Input
                            id="location_name"
                            name="name"
                            required
                            autofocus
                            placeholder="Hauptstandort"
                            value={standort?.name ?? ''}
                        />
                        <InputError message={errors.name} />
                    </Field>

                    <div class="grid gap-4 sm:grid-cols-[1fr_auto]">
                        <Field>
                            <FieldLabel for="location_street">Straße</FieldLabel>
                            <Input
                                id="location_street"
                                name="street"
                                value={standort?.addressFields.street ?? ''}
                            />
                            <InputError message={errors.street} />
                        </Field>

                        <Field>
                            <FieldLabel for="location_house_number">Nr.</FieldLabel>
                            <Input
                                id="location_house_number"
                                name="house_number"
                                class="w-24"
                                value={standort?.addressFields.houseNumber ?? ''}
                            />
                        </Field>
                    </div>

                    <div class="grid gap-4 sm:grid-cols-[auto_1fr]">
                        <Field>
                            <FieldLabel for="location_postal_code">PLZ</FieldLabel>
                            <Input
                                id="location_postal_code"
                                name="postal_code"
                                class="w-28"
                                value={standort?.addressFields.postalCode ?? ''}
                            />
                            <InputError message={errors.postal_code} />
                        </Field>

                        <Field>
                            <FieldLabel for="location_city">Ort</FieldLabel>
                            <Input
                                id="location_city"
                                name="city"
                                value={standort?.addressFields.city ?? ''}
                            />
                            <InputError message={errors.city} />
                        </Field>
                    </div>

                    <Field>
                        <FieldLabel for="location_notes">Notiz</FieldLabel>
                        <Textarea
                            id="location_notes"
                            name="notes"
                            rows={2}
                            value={standort?.notes ?? ''}
                        />
                    </Field>

                    <Field orientation="horizontal">
                        <Switch
                            id="location_is_primary"
                            checked={haupt}
                            onCheckedChange={(v) => (hauptGewaehlt = Boolean(v))}
                        />
                        <input
                            type="hidden"
                            name="is_primary"
                            value={haupt ? '1' : '0'}
                        />
                        <div>
                            <FieldLabel for="location_is_primary">Hauptstandort</FieldLabel>
                            <FieldDescription>
                                Genau einer je Firma. Setzt man ihn hier, wechselt er
                                vom bisherigen herüber.
                            </FieldDescription>
                        </div>
                        <InputError message={errors.is_primary} />
                    </Field>
                </FieldGroup>

                <Dialog.Footer class="pt-4">
                    <Button
                        type="button"
                        variant="ghost"
                        onclick={() => (open = false)}
                    >
                        Abbrechen
                    </Button>
                    <Button type="submit" disabled={processing}>
                        {neu ? 'Anlegen' : 'Speichern'}
                    </Button>
                </Dialog.Footer>
            {/snippet}
        </Form>
    </Dialog.Content>
</Dialog.Root>
