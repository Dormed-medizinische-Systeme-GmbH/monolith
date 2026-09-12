# Project Rules Index

Before planning or editing, find the row whose globs match the file's path and read that rule file.

| Globs | Rule file |
| --- | --- |
| `packages/core/**`, `apps/*/app/Http/Controllers/**`, `apps/*/tests/Feature/**`, `packages/core/tests/Architecture/**` | [architecture.md](architecture.md) |
| `apps/*/routes/**`, `apps/*/bootstrap/app.php`, `apps/*/app/Http/Middleware/**` | [routing.md](routing.md) |
| `compose*.yaml`, `docker/**`, `apps/*/phpunit.xml`, `.env.example` | [local-stack.md](local-stack.md) |

> Kurswechsel 2026-09-12 (ADR-011–ADR-021): Monorepo mit vier Apps (`apps/website|shop|
> crm|portal`) + geteiltem `packages/core`. Siehe `docs/01-architecture/ARCHITECTURE.md`.
> Aktueller Bau-Fokus: `packages/core` + `apps/crm` (ADR-020) — die übrigen drei Apps
> existieren bereits extern und werden erst später hereinmigriert.
