<script lang="ts">
    import ArrowDownIcon from '@lucide/svelte/icons/arrow-down';
    import ArrowUpIcon from '@lucide/svelte/icons/arrow-up';
    import ChevronsUpDownIcon from '@lucide/svelte/icons/chevrons-up-down';
    import { Button } from '@/components/ui/button';

    /**
     * Sortierbare Kopfzelle. Zeigt die Richtung nur an der aktiven Spalte —
     * ein Pfeil an jeder Spalte sagt nichts darüber aus, wonach sortiert ist.
     */
    let {
        label,
        column,
        activeColumn,
        direction,
        onSort,
    }: {
        label: string;
        column: string;
        activeColumn: string;
        direction: 'asc' | 'desc';
        onSort: (column: string) => void;
    } = $props();

    const isActive = $derived(activeColumn === column);
</script>

<Button
    variant="ghost"
    size="sm"
    class="-ml-3 h-8 data-[active=true]:font-semibold"
    data-active={isActive}
    onclick={() => onSort(column)}
>
    {label}
    {#if !isActive}
        <ChevronsUpDownIcon class="size-3.5 opacity-50" />
    {:else if direction === 'asc'}
        <ArrowUpIcon class="size-3.5" />
    {:else}
        <ArrowDownIcon class="size-3.5" />
    {/if}
</Button>
