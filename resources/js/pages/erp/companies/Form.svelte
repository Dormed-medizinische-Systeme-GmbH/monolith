<script lang="ts">
    import { Form, Link, router, setLayoutProps } from '@inertiajs/svelte';
    import ArrowLeft from '@lucide/svelte/icons/arrow-left';
    import Save from '@lucide/svelte/icons/save';
    import Trash from '@lucide/svelte/icons/trash';
    import AppHead from '@/components/AppHead.svelte';
    import InputError from '@/components/InputError.svelte';
    import * as AlertDialog from '@/components/ui/alert-dialog';
    import { Field, FieldDescription, FieldGroup, FieldLabel } from '@/components/ui/field';
    import { Input } from '@/components/ui/input';
    import { NativeSelect } from '@/components/ui/native-select';
    import { Separator } from '@/components/ui/separator';
    import { Textarea } from '@/components/ui/textarea';
    import { destroy, index, show, store, update } from '@/routes/erp/companies';
    import type { CompanyProfile, EmployeeOption, Option } from './profile';

    /**
     * Anlegen und Bearbeiten einer Firma.
     *
     * Die beiden Zuständigen sind INFORMATIV (D-016): sie sagen, wer die Praxis
     * betreut, nicht wer auf sie zugreifen darf. Berechtigungen kommen
     * ausschließlich aus der Rolle (D-030).
     */
    let {
        company = null,
        salesEmployees,
        serviceEmployees,
        specialties,
        companies,
    }: {
        company?: CompanyProfile | null;
        salesEmployees: EmployeeOption[];
        serviceEmployees: EmployeeOption[];
        specialties: Option[];
        companies: Option[];
    } = $props();

    const neu = $derived(company === null);
    const f = $derived(company?.fields ?? {});

    let loeschenOffen = $state(false);
    let formular: ReturnType<typeof Form> | undefined = $state();

    function loeschen(): void {
        router.delete(destroy(company!.id).url);
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

<AppHead title={neu ? 'Firma anlegen' : `${company?.name} bearbeiten`} />

<div class="space-y-6">
    <Link
        href={neu ? index().url : show(company!.id).url}
        class="inline-flex items-center gap-1 text-sm text-muted-foreground underline-offset-4 hover:underline"
    >
        <ArrowLeft class="size-4" />
        {neu ? 'Alle Firmen' : company?.name}
    </Link>

    <Form
        bind:this={formular}
        {...(neu ? store.form() : update.form(company!.id))}
        class="max-w-2xl"
    >
        {#snippet children({ errors })}
            <FieldGroup>
                <div class="grid gap-4 sm:grid-cols-2">
                    <Field>
                        <FieldLabel for="name">Name</FieldLabel>
                        <Input id="name" name="name" required autofocus value={f.name ?? ''} />
                        <InputError message={errors.name} />
                    </Field>

                    <Field>
                        <FieldLabel for="name_addition">Namenszusatz</FieldLabel>
                        <Input
                            id="name_addition"
                            name="name_addition"
                            value={f.name_addition ?? ''}
                        />
                        <InputError message={errors.name_addition} />
                    </Field>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <Field>
                        <FieldLabel for="debitor_number">Kundennummer</FieldLabel>
                        <Input
                            id="debitor_number"
                            name="debitor_number"
                            value={f.debitor_number ?? ''}
                        />
                        <InputError message={errors.debitor_number} />
                    </Field>

                    <Field>
                        <FieldLabel for="medical_specialty_id">Fachrichtung</FieldLabel>
                        <NativeSelect
                            id="medical_specialty_id"
                            name="medical_specialty_id"
                            class="w-full"
                            value={f.medical_specialty_id ?? ''}
                        >
                            <option value="">— keine —</option>
                            {#each specialties as fach (fach.id)}
                                <option value={fach.id}>{fach.name}</option>
                            {/each}
                        </NativeSelect>
                        <InputError message={errors.medical_specialty_id} />
                    </Field>
                </div>

                <Separator />

                <div class="grid gap-4 sm:grid-cols-2">
                    <Field>
                        <FieldLabel for="responsible_sales_id">
                            Verantwortlicher (Vertrieb)
                        </FieldLabel>
                        <NativeSelect
                            id="responsible_sales_id"
                            name="responsible_sales_id"
                            class="w-full"
                            value={f.responsible_sales_id ?? ''}
                        >
                            <option value="">— niemand —</option>
                            {#each salesEmployees as person (person.id)}
                                <option value={person.id}>
                                    {person.name}{person.foreign
                                        ? ' (nicht mehr im Vertrieb)'
                                        : ''}
                                </option>
                            {/each}
                        </NativeSelect>
                        <InputError message={errors.responsible_sales_id} />
                    </Field>

                    <Field>
                        <FieldLabel for="responsible_service_id">
                            Verantwortlicher (Service)
                        </FieldLabel>
                        <NativeSelect
                            id="responsible_service_id"
                            name="responsible_service_id"
                            class="w-full"
                            value={f.responsible_service_id ?? ''}
                        >
                            <option value="">— niemand —</option>
                            {#each serviceEmployees as person (person.id)}
                                <option value={person.id}>
                                    {person.name}{person.foreign
                                        ? ' (nicht mehr im Service)'
                                        : ''}
                                </option>
                            {/each}
                        </NativeSelect>
                        <InputError message={errors.responsible_service_id} />
                    </Field>
                </div>

                <FieldDescription>
                    Je aus der eigenen Abteilung, abgeleitet aus der Rolle (D-124).
                    Die Zuordnung ist informativ — wer was darf, entscheidet
                    ebenfalls die Rolle, nicht dieses Feld (D-016/D-030).
                </FieldDescription>

                <Separator />

                <div class="grid gap-4 sm:grid-cols-[1fr_auto]">
                    <Field>
                        <FieldLabel for="street">Straße</FieldLabel>
                        <Input id="street" name="street" value={f.street ?? ''} />
                        <InputError message={errors.street} />
                    </Field>

                    <Field>
                        <FieldLabel for="house_number">Nr.</FieldLabel>
                        <Input
                            id="house_number"
                            name="house_number"
                            class="w-24"
                            value={f.house_number ?? ''}
                        />
                    </Field>
                </div>

                <div class="grid gap-4 sm:grid-cols-[auto_1fr]">
                    <Field>
                        <FieldLabel for="postal_code">PLZ</FieldLabel>
                        <Input
                            id="postal_code"
                            name="postal_code"
                            class="w-28"
                            value={f.postal_code ?? ''}
                        />
                        <InputError message={errors.postal_code} />
                    </Field>

                    <Field>
                        <FieldLabel for="city">Ort</FieldLabel>
                        <Input id="city" name="city" value={f.city ?? ''} />
                        <InputError message={errors.city} />
                    </Field>
                </div>

                {#if neu}
                    <FieldDescription>
                        Aus dieser Anschrift entsteht automatisch der Hauptstandort
                        (D-007/D-078). Er ist danach unabhängig bearbeitbar.
                    </FieldDescription>
                {/if}

                <Separator />

                <div class="grid gap-4 sm:grid-cols-2">
                    <Field>
                        <FieldLabel for="avv_status">Auftragsverarbeitung</FieldLabel>
                        <NativeSelect
                            id="avv_status"
                            name="avv_status"
                            required
                            class="w-full"
                            value={f.avv_status ?? 'none'}
                        >
                            <option value="none">nicht unterzeichnet</option>
                            <option value="signed">unterzeichnet</option>
                        </NativeSelect>
                        <InputError message={errors.avv_status} />
                    </Field>

                    <Field>
                        <FieldLabel for="avv_signed_at">unterzeichnet am</FieldLabel>
                        <Input
                            id="avv_signed_at"
                            name="avv_signed_at"
                            type="date"
                            value={f.avv_signed_at ?? ''}
                        />
                        <InputError message={errors.avv_signed_at} />
                    </Field>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <Field>
                        <FieldLabel for="legal_form">Rechtsform</FieldLabel>
                        <Input id="legal_form" name="legal_form" value={f.legal_form ?? ''} />
                    </Field>

                    <Field>
                        <FieldLabel for="billing_company_id">Rechnungsempfänger</FieldLabel>
                        <NativeSelect
                            id="billing_company_id"
                            name="billing_company_id"
                            class="w-full"
                            value={f.billing_company_id ?? ''}
                        >
                            <option value="">— diese Firma —</option>
                            {#each companies as andere (andere.id)}
                                <option value={andere.id}>{andere.name}</option>
                            {/each}
                        </NativeSelect>
                        <FieldDescription>
                            Alle Rechnungen gehen dorthin (D-004/D-066).
                        </FieldDescription>
                        <InputError message={errors.billing_company_id} />
                    </Field>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <Field>
                        <FieldLabel for="tax_number">Steuernummer</FieldLabel>
                        <Input id="tax_number" name="tax_number" value={f.tax_number ?? ''} />
                    </Field>

                    <Field>
                        <FieldLabel for="vat_id">USt-IdNr.</FieldLabel>
                        <Input id="vat_id" name="vat_id" value={f.vat_id ?? ''} />
                    </Field>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <Field>
                        <FieldLabel for="iban">IBAN</FieldLabel>
                        <Input id="iban" name="iban" class="font-mono" value={f.iban ?? ''} />
                        <InputError message={errors.iban} />
                    </Field>

                    <Field>
                        <FieldLabel for="bic">BIC</FieldLabel>
                        <Input id="bic" name="bic" class="font-mono" value={f.bic ?? ''} />
                        <InputError message={errors.bic} />
                    </Field>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <Field>
                        <FieldLabel for="bank_account_holder">Kontoinhaber</FieldLabel>
                        <Input
                            id="bank_account_holder"
                            name="bank_account_holder"
                            value={f.bank_account_holder ?? ''}
                        />
                    </Field>

                    <Field>
                        <FieldLabel for="bank_name">Bank</FieldLabel>
                        <Input id="bank_name" name="bank_name" value={f.bank_name ?? ''} />
                    </Field>
                </div>

                <Field>
                    <FieldLabel for="notes">Notiz</FieldLabel>
                    <Textarea id="notes" name="notes" rows={3} value={f.notes ?? ''} />
                </Field>
            </FieldGroup>
        {/snippet}
    </Form>
</div>

{#if !neu}
    <AlertDialog.Root bind:open={loeschenOffen}>
        <AlertDialog.Content>
            <AlertDialog.Header>
                <AlertDialog.Title>{company?.name} löschen?</AlertDialog.Title>
                <AlertDialog.Description>
                    Der Datensatz bleibt erhalten und wird nur ausgeblendet (D-018) —
                    Standorte, Kontakte und spätere Rechnungen laufen dadurch nicht
                    ins Leere.
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
