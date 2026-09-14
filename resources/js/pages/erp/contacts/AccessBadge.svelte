<script lang="ts">
    import { Badge } from '@/components/ui/badge';

    /**
     * Zustand des Portalzugangs (ADR-037/042).
     *
     * Drei Fälle, die auseinandergehalten gehören: kein Zugang, aktiver
     * Zugang, gesperrter Zugang. Der dritte sieht sonst aus wie der zweite,
     * und ein gesperrter Kunde ruft an, weil er sich nicht anmelden kann.
     */
    let { email, active }: { email: string | null; active: boolean } = $props();
</script>

{#if !email}
    <span class="text-sm text-muted-foreground">kein Zugang</span>
{:else if active}
    <div class="flex flex-col gap-0.5">
        <Badge variant="secondary" class="w-fit">aktiv</Badge>
        <span class="font-mono text-xs text-muted-foreground">{email}</span>
    </div>
{:else}
    <div class="flex flex-col gap-0.5">
        <Badge variant="outline" class="w-fit text-muted-foreground">gesperrt</Badge>
        <span class="font-mono text-xs text-muted-foreground">{email}</span>
    </div>
{/if}
