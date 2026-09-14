<script lang="ts">
    import type { Channel } from './profile';

    /**
     * Kommunikationswege einer Firma oder Person (D-010).
     *
     * `tel:` und `mailto:` sind hier kein Zierrat — der häufigste Vorgang im
     * ERP ist „Kunde anrufen", und ohne Verweis wird die Nummer abgetippt.
     */
    let { channels, compact = false }: { channels: Channel[]; compact?: boolean } =
        $props();

    function href(channel: Channel): string | null {
        switch (channel.type) {
            case 'phone':
            case 'mobile':
                return `tel:${channel.value.replace(/\s+/g, '')}`;
            case 'email':
                return `mailto:${channel.value}`;
            case 'web':
                return channel.value.startsWith('http')
                    ? channel.value
                    : `https://${channel.value}`;
            default:
                return null;
        }
    }
</script>

{#if channels.length === 0}
    <span class="text-sm text-muted-foreground">—</span>
{:else}
    <ul class="space-y-1">
        {#each channels as channel (channel.id)}
            <li class="flex flex-wrap items-baseline gap-x-2 text-sm">
                {#if !compact}
                    <span class="w-20 shrink-0 text-xs text-muted-foreground">
                        {channel.typeLabel}
                    </span>
                {/if}
                {#if href(channel)}
                    <a
                        href={href(channel)}
                        class="underline-offset-4 hover:underline"
                    >
                        {channel.value}
                    </a>
                {:else}
                    <span>{channel.value}</span>
                {/if}
                <span class="text-xs text-muted-foreground">({channel.label})</span>
            </li>
        {/each}
    </ul>
{/if}
