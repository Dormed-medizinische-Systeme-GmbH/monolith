<script lang="ts">
    import { page, router } from '@inertiajs/svelte';
    import ChevronsUpDown from '@lucide/svelte/icons/chevrons-up-down';
    import CircleUser from '@lucide/svelte/icons/circle-user';
    import LogOut from '@lucide/svelte/icons/log-out';
    import * as Avatar from '@/components/ui/avatar';
    import * as DropdownMenu from '@/components/ui/dropdown-menu';
    import * as Sidebar from '@/components/ui/sidebar';
    import { logout } from '@/routes';
    import type { Auth } from '@/types/auth';

    /**
     * Der angemeldete Mitarbeiter am Fuß der Seitenleiste.
     *
     * Die Daten kommen aus den geteilten Inertia-Props (`auth.user`), nicht aus
     * einer eigenen Anfrage — sie stehen ohnehin auf jeder Seite zur Verfügung.
     */
    const user = $derived((page.props.auth as Auth | undefined)?.user ?? null);
    const name = $derived(user ? `${user.first_name} ${user.last_name}`.trim() : '');
    const initials = $derived(
        user ? `${user.first_name?.[0] ?? ''}${user.last_name?.[0] ?? ''}`.toUpperCase() : '',
    );

    const sidebar = Sidebar.useSidebar();

    function abmelden(): void {
        router.post(logout().url);
    }
</script>

{#if user}
    <Sidebar.Menu>
        <Sidebar.MenuItem>
            <DropdownMenu.Root>
                <DropdownMenu.Trigger>
                    {#snippet child({ props })}
                        <Sidebar.MenuButton
                            {...props}
                            size="lg"
                            class="data-[state=open]:bg-sidebar-accent data-[state=open]:text-sidebar-accent-foreground"
                        >
                            <Avatar.Root class="size-8 rounded-lg">
                                <Avatar.Fallback class="rounded-lg">{initials}</Avatar.Fallback>
                            </Avatar.Root>
                            <div class="grid flex-1 text-left text-sm leading-tight">
                                <span class="truncate font-medium">{name}</span>
                                <span class="truncate text-xs text-muted-foreground">
                                    {user.email}
                                </span>
                            </div>
                            <ChevronsUpDown class="ms-auto size-4" />
                        </Sidebar.MenuButton>
                    {/snippet}
                </DropdownMenu.Trigger>

                <DropdownMenu.Content
                    class="w-(--bits-dropdown-menu-anchor-width) min-w-56 rounded-lg"
                    side={sidebar.isMobile ? 'bottom' : 'right'}
                    align="end"
                    sideOffset={4}
                >
                    <DropdownMenu.Label class="p-0 font-normal">
                        <div class="flex items-center gap-2 px-1 py-1.5 text-left text-sm">
                            <Avatar.Root class="size-8 rounded-lg">
                                <Avatar.Fallback class="rounded-lg">{initials}</Avatar.Fallback>
                            </Avatar.Root>
                            <div class="grid flex-1 text-sm leading-tight">
                                <span class="truncate font-medium">{name}</span>
                                <span class="truncate text-xs text-muted-foreground">
                                    {user.email}
                                </span>
                            </div>
                        </div>
                    </DropdownMenu.Label>

                    <DropdownMenu.Separator />

                    <!--
                        Das eigene Profil gibt es noch nicht: der Einstellungsbereich
                        des Starter-Kits ist entfallen und wird eigens gebaut.
                    -->
                    <DropdownMenu.Item disabled>
                        <CircleUser />
                        Mein Profil
                    </DropdownMenu.Item>

                    <DropdownMenu.Separator />

                    <DropdownMenu.Item onSelect={abmelden}>
                        <LogOut />
                        Abmelden
                    </DropdownMenu.Item>
                </DropdownMenu.Content>
            </DropdownMenu.Root>
        </Sidebar.MenuItem>
    </Sidebar.Menu>
{/if}
