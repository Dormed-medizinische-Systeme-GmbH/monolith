<script lang="ts">
    import { router } from '@inertiajs/svelte';
    import * as Command from '@/components/ui/command';
    import { navigation } from './navigation';

    /**
     * Strg/Cmd + K — springt in eine Fläche.
     *
     * Speist sich aus derselben `navigation.ts` wie die Seitenleiste; was dort
     * noch keinen `href` hat, taucht hier gar nicht erst auf. Später soll das
     * auch Datensätze finden — dann kommt eine Server-Abfrage dazu, die
     * Mechanik bleibt.
     */
    let { open = $bindable(false) }: { open?: boolean } = $props();

    const groups = $derived(
        navigation
            .map((group) => ({
                label: group.label,
                items: group.items.filter((item) => item.href),
            }))
            .filter((group) => group.items.length > 0),
    );

    function springe(href: string): void {
        open = false;
        router.visit(href);
    }
</script>

<Command.Dialog bind:open title="Suchen" description="Fläche öffnen">
    <Command.Input placeholder="Fläche suchen …" />
    <Command.List>
        <Command.Empty>Nichts gefunden.</Command.Empty>
        {#each groups as group (group.label)}
            <Command.Group heading={group.label}>
                {#each group.items as item (item.title)}
                    <Command.Item
                        value={item.title}
                        onSelect={() => springe(item.href!)}
                    >
                        <item.icon />
                        <span>{item.title}</span>
                    </Command.Item>
                {/each}
            </Command.Group>
        {/each}
    </Command.List>
</Command.Dialog>
