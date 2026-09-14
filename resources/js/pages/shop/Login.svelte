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
    } from '@/components/ui/field';
    import { Input } from '@/components/ui/input';
    import { Spinner } from '@/components/ui/spinner';
    import { store } from '@/routes/shop/login';

    let { status = '' }: { status?: string } = $props();
</script>

<AppHead title="Anmeldung" />

<div
    class="flex min-h-svh flex-col items-center justify-center gap-6 bg-background p-6 md:p-10"
>
    <div class="w-full max-w-sm">
        {#if status}
            <div class="mb-4 text-center text-sm font-medium text-green-600">
                {status}
            </div>
        {/if}

        <Form {...store.form()} resetOnSuccess={['password']} class="flex flex-col gap-6">
            {#snippet children({ errors, processing })}
                <FieldGroup>
                    <div class="flex flex-col items-center gap-2 text-center">
                        <span
                            class="flex size-8 items-center justify-center rounded-md bg-primary text-sm font-bold text-primary-foreground"
                        >
                            D
                        </span>
                        <h1 class="text-xl font-bold">Shop</h1>
                        <FieldDescription>Anmeldung für Bestellungen und Ihre Aufträge</FieldDescription>
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

                    <FieldDescription class="text-center">
                        Noch kein Zugang? Ihre Ansprechpartnerin oder Ihr
                        Ansprechpartner bei Dormed richtet ihn ein.
                    </FieldDescription>
                </FieldGroup>
            {/snippet}
        </Form>
    </div>
</div>
