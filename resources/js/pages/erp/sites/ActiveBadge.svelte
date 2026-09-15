<script lang="ts">
    import { Badge } from '@/components/ui/badge';

    /**
     * Grün aktiv, rot stillgelegt — dieselbe Zuordnung wie beim
     * Mitarbeiterzugang, damit man sie nicht zweimal lernen muss.
     */
    let { active }: { active: boolean } = $props();
</script>

{#if active}
    <Badge variant="outline" class="border-emerald-300 bg-emerald-50 text-emerald-700">
        aktiv
    </Badge>
{:else}
    <Badge variant="outline" class="border-destructive/30 bg-destructive/10 text-destructive">
        stillgelegt
    </Badge>
{/if}
