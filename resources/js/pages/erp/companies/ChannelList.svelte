<script lang="ts">
    import type { Component } from 'svelte';
    import Globe from '@lucide/svelte/icons/globe';
    import Mail from '@lucide/svelte/icons/mail';
    import Phone from '@lucide/svelte/icons/phone';
    import Printer from '@lucide/svelte/icons/printer';
    import Smartphone from '@lucide/svelte/icons/smartphone';
    import type { Channel } from './profile';

    /**
     * Kommunikationswege einer Firma oder Person (D-010).
     *
     * `tel:` und `mailto:` sind hier kein Zierrat — der häufigste Vorgang im
     * ERP ist „Kunde anrufen", und ohne Verweis wird die Nummer abgetippt.
     *
     * `compact` lässt die Symbole weg: in einer Tabellenzelle stünde neben
     * jeder Zeile ein weiteres Zeichen, und die Spalte wird dadurch nicht
     * lesbarer.
     */
    let { channels, compact = false }: { channels: Channel[]; compact?: boolean } =
        $props();

    const icons: Record<string, Component> = {
        phone: Phone,
        mobile: Smartphone,
        fax: Printer,
        email: Mail,
        web: Globe,
    };

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
    <ul class="space-y-1.5">
        {#each channels as channel (channel.id)}
            {@const Icon = icons[channel.type]}
            <li class="flex items-baseline gap-2 text-sm">
                {#if !compact && Icon}
                    <Icon
                        class="size-4 shrink-0 translate-y-0.5 text-muted-foreground"
                        aria-label={channel.typeLabel}
                    />
                {/if}
                {#if href(channel)}
                    <a href={href(channel)} class="underline-offset-4 hover:underline">
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
