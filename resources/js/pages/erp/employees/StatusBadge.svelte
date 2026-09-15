<script lang="ts">
    import { Badge } from '@/components/ui/badge';

    /**
     * Der Zustand eines Mitarbeiterzugangs.
     *
     * Drei Fälle, die auseinandergehalten gehören — „kann sich nicht anmelden"
     * hat drei verschiedene Ursachen, und wer anruft, sagt nur den Satz:
     * stillgelegt (D-032), kein Passwort gesetzt (D-029), oder alles in Ordnung.
     *
     * Die Farben stehen hier als Tailwind-Werte und nicht als Token: die
     * Palette kennt bisher nur `destructive` (ADR-044), für „in Ordnung" und
     * „unfertig" gibt es noch keine. Sobald sie definiert sind, ist das diese
     * eine Datei.
     */
    let {
        active,
        hasPassword,
    }: { active: boolean; hasPassword: boolean } = $props();
</script>

{#if !active}
    <Badge
        variant="outline"
        class="border-destructive/30 bg-destructive/10 text-destructive"
    >
        gesperrt
    </Badge>
{:else if !hasPassword}
    <Badge variant="outline" class="border-amber-300 bg-amber-50 text-amber-700">
        ohne Passwort
    </Badge>
{:else}
    <Badge
        variant="outline"
        class="border-emerald-300 bg-emerald-50 text-emerald-700"
    >
        aktiv
    </Badge>
{/if}
