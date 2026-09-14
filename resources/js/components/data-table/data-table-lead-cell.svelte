<script lang="ts">
    import { Link } from '@inertiajs/svelte';

    /**
     * Die Leitspalte einer Liste: Bezeichnung, darunter ein gedämpfter Zusatz.
     *
     * Ein wiederkehrendes Muster — Firma und Firmenzusatz, später Person und
     * Rolle, Gerät und Seriennummer. Es liegt deshalb hier und nicht in jeder
     * `columns.ts` neu, sonst sieht dieselbe Sache in der dritten Liste anders
     * aus als in der ersten.
     *
     * Mit `href` wird die Bezeichnung ein echtes `<a>`. Das ist kein Beiwerk:
     * die ganze Zeile ist zwar anklickbar (siehe `data-table.svelte`), aber nur
     * ein Verweis kann mittlere Maustaste, „in neuem Tab öffnen", Adresse
     * kopieren und Tastaturbedienung.
     */
    let {
        label,
        sublabel = null,
        href,
    }: {
        label: string;
        sublabel?: string | null;
        href?: string;
    } = $props();
</script>

<div class="flex flex-col">
    {#if href}
        <Link {href} class="font-medium underline-offset-4 hover:underline">
            {label}
        </Link>
    {:else}
        <span class="font-medium">{label}</span>
    {/if}

    {#if sublabel}
        <span class="text-xs text-muted-foreground">{sublabel}</span>
    {/if}
</div>
