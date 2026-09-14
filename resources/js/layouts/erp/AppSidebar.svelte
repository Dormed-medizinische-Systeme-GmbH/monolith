<script lang="ts">
    import { Link, page } from '@inertiajs/svelte';
    import Search from '@lucide/svelte/icons/search';
    import * as Sidebar from '@/components/ui/sidebar';
    import { dashboard } from '@/routes/erp';
    import { isCurrent, navigation } from './navigation';
    import NavUser from './NavUser.svelte';

    /**
     * Die Seitenleiste des ERP.
     *
     * Aufbau wie in Coolify: Wortmarke oben, Suche darunter, dann beschriftete
     * Gruppen, unten der angemeldete Mitarbeiter. Die Einträge kommen aus
     * `navigation.ts` — dieselbe Quelle, aus der sich die Befehlspalette
     * bedient, damit beide nicht auseinanderlaufen.
     */
    let { onOpenSearch }: { onOpenSearch: () => void } = $props();

    const url = $derived(page.url);
</script>

<Sidebar.Root collapsible="icon">
    <Sidebar.Header class="gap-2">
        <Sidebar.Menu>
            <Sidebar.MenuItem>
                <Sidebar.MenuButton size="lg" class="data-[slot=sidebar-menu-button]:!p-2">
                    {#snippet child({ props })}
                        <Link href={dashboard().url} {...props}>
                            <span
                                class="flex aspect-square size-8 items-center justify-center rounded-md bg-sidebar-primary font-semibold text-sidebar-primary-foreground"
                            >
                                D
                            </span>
                            <div class="grid flex-1 text-left leading-tight">
                                <span class="truncate text-base font-semibold">Dormed</span>
                                <span class="truncate text-xs text-muted-foreground">
                                    Integrated Management
                                </span>
                            </div>
                        </Link>
                    {/snippet}
                </Sidebar.MenuButton>
            </Sidebar.MenuItem>
        </Sidebar.Menu>

        <Sidebar.Menu>
            <Sidebar.MenuItem>
                <Sidebar.MenuButton onclick={onOpenSearch} tooltipContent="Suchen">
                    <Search />
                    <span class="text-muted-foreground">Suchen</span>
                    <kbd
                        class="ms-auto hidden rounded border bg-muted px-1.5 font-mono text-[10px] text-muted-foreground sm:inline-block"
                    >
                        Strg K
                    </kbd>
                </Sidebar.MenuButton>
            </Sidebar.MenuItem>
        </Sidebar.Menu>
    </Sidebar.Header>

    <Sidebar.Content>
        {#each navigation as group (group.label)}
            <Sidebar.Group>
                <Sidebar.GroupLabel>{group.label}</Sidebar.GroupLabel>
                <Sidebar.GroupContent>
                    <Sidebar.Menu>
                        {#each group.items as item (item.title)}
                            <Sidebar.MenuItem>
                                {#if item.href}
                                    <Sidebar.MenuButton
                                        isActive={isCurrent(item.href, url)}
                                        tooltipContent={item.title}
                                    >
                                        {#snippet child({ props })}
                                            <Link href={item.href} {...props}>
                                                <item.icon />
                                                <span>{item.title}</span>
                                            </Link>
                                        {/snippet}
                                    </Sidebar.MenuButton>
                                {:else}
                                    <!-- Noch nicht gebaut: sichtbar, aber kein Verweis ins Leere. -->
                                    <Sidebar.MenuButton
                                        aria-disabled="true"
                                        tooltipContent="{item.title} — noch nicht gebaut"
                                    >
                                        <item.icon />
                                        <span>{item.title}</span>
                                    </Sidebar.MenuButton>
                                {/if}
                            </Sidebar.MenuItem>
                        {/each}
                    </Sidebar.Menu>
                </Sidebar.GroupContent>
            </Sidebar.Group>
        {/each}
    </Sidebar.Content>

    <Sidebar.Footer>
        <NavUser />
    </Sidebar.Footer>

    <Sidebar.Rail />
</Sidebar.Root>
