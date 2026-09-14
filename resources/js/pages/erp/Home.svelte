<script lang="ts">
    import AppHead from '@/components/AppHead.svelte';
    import AccessPointCard from '@/components/AccessPointCard.svelte';

    let { accessPoint } = $props();
</script>

<AppHead title="ERP" />

<AccessPointCard {accessPoint} />
