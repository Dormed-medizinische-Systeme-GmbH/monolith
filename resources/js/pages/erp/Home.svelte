<script lang="ts">
    import { Link } from '@inertiajs/svelte';
    import AppHead from '@/components/AppHead.svelte';
    import AccessPointCard from '@/components/AccessPointCard.svelte';
    import { index as companies } from '@/routes/erp/companies';

    let { accessPoint } = $props();
</script>

<AppHead title="ERP" />

<!--
    Notbehelf statt Navigation: es gibt noch keine ERP-Shell (siehe
    AppLayout.svelte), aber die Firmenliste muss erreichbar sein.
-->
<div class="flex justify-center">
    <Link
        href={companies().url}
        class="text-sm underline underline-offset-4 hover:no-underline"
    >
        Firmen →
    </Link>
</div>

<AccessPointCard {accessPoint} />
