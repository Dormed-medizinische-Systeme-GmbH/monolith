<script lang="ts">
    /**
     * Zeigt, welcher Zugriffspunkt und welche Datenbankrolle den Request
     * bedient haben (ADR-033/036/039). Geruest, keine Fachlogik — ersetzbar,
     * sobald echte Startseiten entstehen.
     */
    interface AccessPoint {
        key: string;
        label: string;
        host: string;
        frontend: string;
        connection: string;
        role: string;
    }

    let { accessPoint }: { accessPoint: AccessPoint } = $props();

    const rows = $derived([
        { label: 'Host', value: accessPoint.host },
        { label: 'Route-Datei', value: `routes/${accessPoint.key}.php` },
        { label: 'Frontend', value: accessPoint.frontend },
        { label: 'Verbindung', value: accessPoint.connection },
        { label: 'DB-Rolle', value: accessPoint.role },
    ]);
</script>

<div
    class="flex min-h-screen flex-col items-center justify-center bg-[#FDFDFC] p-6 text-[#1b1b18]"
>
    <div class="w-full max-w-md">
        <p class="mb-1 text-xs tracking-widest text-neutral-500 uppercase">
            Dormed — Geruest
        </p>
        <h1 class="mb-6 text-2xl font-semibold">{accessPoint.label}</h1>

        <dl
            class="divide-y divide-neutral-200 rounded-lg border border-neutral-200"
        >
            {#each rows as row (row.label)}
                <div class="flex justify-between gap-4 px-4 py-3 text-sm">
                    <dt class="text-neutral-500">{row.label}</dt>
                    <dd class="font-mono text-right">{row.value}</dd>
                </div>
            {/each}
        </dl>

        <p class="mt-6 text-xs leading-relaxed text-neutral-500">
            Die DB-Rolle kommt aus <code>select current_user</code>, nicht aus der
            Konfiguration — sie beweist, welche Postgres-Rolle den Request
            tatsaechlich bedient hat.
        </p>
    </div>
</div>
