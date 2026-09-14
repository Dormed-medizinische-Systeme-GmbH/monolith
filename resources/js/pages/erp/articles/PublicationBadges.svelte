<script lang="ts">
    import { Badge } from '@/components/ui/badge';

    /**
     * Die drei Schalter aus ADR-038 — Katalogpflege, Website, Shop.
     *
     * Sie sind unabhängig voneinander, deshalb drei Kennzeichnungen und keine
     * einzelne Statusangabe: ein Gerät kann im Katalog aktiv sein, auf der
     * Website stehen und trotzdem nicht bestellbar sein.
     */
    let {
        active,
        isPublic,
        orderable,
    }: { active: boolean; isPublic: boolean; orderable: boolean } = $props();
</script>

<div class="flex flex-wrap gap-1">
    {#if !active}
        <Badge variant="outline" class="text-muted-foreground">inaktiv</Badge>
    {/if}
    {#if isPublic}
        <Badge variant="secondary">Website</Badge>
    {/if}
    {#if orderable}
        <Badge variant="secondary">Shop</Badge>
    {/if}
    {#if active && !isPublic && !orderable}
        <span class="text-sm text-muted-foreground">nur intern</span>
    {/if}
</div>
