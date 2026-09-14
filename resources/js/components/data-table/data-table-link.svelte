<script lang="ts">
    import { Link } from '@inertiajs/svelte';

    /**
     * Der Einsprung aus einer Listenzeile in den Datensatz.
     *
     * Die ganze Zeile ist zusätzlich anklickbar (siehe `data-table.svelte`) —
     * aber ein echtes `<a>` in der Leitspalte muss es trotzdem geben: nur damit
     * funktionieren mittlere Maustaste, „in neuem Tab öffnen", Adresse kopieren
     * und die Tastaturbedienung so, wie man es erwartet.
     */
    let { href, label }: { href: string; label: string } = $props();
</script>

<Link {href} class="font-medium underline-offset-4 hover:underline">
    {label}
</Link>
