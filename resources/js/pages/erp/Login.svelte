<script lang="ts">
    import { Form } from '@inertiajs/svelte';
    import AppHead from '@/components/AppHead.svelte';
    import InputError from '@/components/InputError.svelte';
    import { Button } from '@/components/ui/button';
    import {
        Field,
        FieldDescription,
        FieldGroup,
        FieldLabel,
        FieldSeparator,
    } from '@/components/ui/field';
    import { Input } from '@/components/ui/input';
    import { Spinner } from '@/components/ui/spinner';
    import { store } from '@/routes/login';

    let { status = '' }: { status?: string } = $props();
</script>

<AppHead title="Anmeldung" />

<div class="grid min-h-svh lg:grid-cols-2">
    <div class="flex flex-col gap-4 p-6 md:p-10">
        <div class="flex justify-center gap-2 md:justify-start">
            <span class="flex items-center gap-2 font-medium">
                <span
                    class="flex size-6 items-center justify-center rounded-md bg-primary text-xs font-bold text-primary-foreground"
                >
                    D
                </span>
                Dormed ERP
            </span>
        </div>

        <div class="flex flex-1 items-center justify-center">
            <div class="w-full max-w-xs">
                {#if status}
                    <div class="mb-4 text-center text-sm font-medium text-green-600">
                        {status}
                    </div>
                {/if}

                <Form {...store.form()} resetOnSuccess={['password']} class="flex flex-col gap-6">
                    {#snippet children({ errors, processing })}
                        <FieldGroup>
                            <div class="flex flex-col gap-1 text-center">
                                <h1 class="text-xl font-bold">Anmeldung</h1>
                                <FieldDescription>
                                    Interner Zugang für Mitarbeiterinnen und Mitarbeiter
                                </FieldDescription>
                            </div>

                            <Field>
                                <FieldLabel for="email">E-Mail</FieldLabel>
                                <Input
                                    id="email"
                                    name="email"
                                    type="email"
                                    autocomplete="username"
                                    required
                                    autofocus
                                    placeholder="vorname.nachname@dormed.de"
                                />
                                <InputError message={errors.email} />
                            </Field>

                            <Field>
                                <FieldLabel for="password">Passwort</FieldLabel>
                                <Input
                                    id="password"
                                    name="password"
                                    type="password"
                                    autocomplete="current-password"
                                    required
                                />
                                <InputError message={errors.password} />
                            </Field>

                            <Field>
                                <Button type="submit" disabled={processing}>
                                    {#if processing}
                                        <Spinner />
                                    {/if}
                                    Anmelden
                                </Button>
                            </Field>

                            <FieldSeparator>oder</FieldSeparator>

                            <Field>
                                <!--
                                    Bewusst tot. Ziel ist Microsoft-Entra-SSO (D-029);
                                    der Passwort-Login existiert nur, weil SSO etwas
                                    braucht, worauf es aufsetzen kann. Neutral
                                    beschriftet, damit die Beschriftung einen
                                    Anbieterwechsel überlebt.
                                -->
                                <Button variant="outline" type="button" disabled>
                                    Single Sign-On
                                </Button>
                                <FieldDescription class="text-center">
                                    In Vorbereitung
                                </FieldDescription>
                            </Field>
                        </FieldGroup>
                    {/snippet}
                </Form>
            </div>
        </div>
    </div>

    <div class="relative hidden bg-muted lg:block">
        <img
            src="/assets/img/9166be6b-46c0-4068-8b57-2b414dd62000.png"
            alt=""
            class="absolute inset-0 h-full w-full object-contain p-24"
        />
    </div>
</div>
