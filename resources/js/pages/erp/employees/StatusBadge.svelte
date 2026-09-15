<script lang="ts">
    import { Badge } from '@/components/ui/badge';

    /**
     * Der Zustand eines Mitarbeiterzugangs.
     *
     * Drei Fälle, die auseinandergehalten gehören — „kann sich nicht anmelden"
     * hat drei verschiedene Ursachen, und wer anruft, sagt nur den Satz:
     * stillgelegt (D-032), kein Passwort gesetzt (D-029), oder alles in Ordnung.
     */
    let {
        active,
        hasPassword,
    }: { active: boolean; hasPassword: boolean } = $props();
</script>

{#if !active}
    <Badge variant="outline" class="text-muted-foreground">stillgelegt</Badge>
{:else if !hasPassword}
    <Badge variant="outline" class="text-muted-foreground">ohne Passwort</Badge>
{:else}
    <Badge variant="secondary">aktiv</Badge>
{/if}
