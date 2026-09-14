<script lang="ts">
    import { Badge } from '@/components/ui/badge';

    /**
     * Zustand des Portalzugangs (ADR-037/042).
     *
     * Drei Fälle, die auseinandergehalten gehören: kein Zugang, aktiver
     * Zugang, gesperrter Zugang. Der dritte sieht sonst aus wie der zweite,
     * und ein gesperrter Kunde ruft an, weil er sich nicht anmelden kann.
     */
    let {
        account,
    }: {
        account: { email: string; active: boolean; lastLoginAt: string | null } | null;
    } = $props();
</script>

{#if !account}
    <span class="text-sm text-muted-foreground">kein Zugang</span>
{:else}
    <div class="flex flex-col gap-0.5">
        {#if account.active}
            <Badge variant="secondary" class="w-fit">aktiv</Badge>
        {:else}
            <Badge variant="outline" class="w-fit text-muted-foreground">gesperrt</Badge>
        {/if}
        <span class="font-mono text-xs text-muted-foreground">{account.email}</span>
        {#if account.lastLoginAt}
            <span class="text-xs text-muted-foreground">
                zuletzt angemeldet {account.lastLoginAt}
            </span>
        {/if}
    </div>
{/if}
