# Implementierungsreihenfolge

> **Kurswechsel 2026-09-12** (ADR-011–ADR-025). Ersetzt die alte Reihenfolge
> vollständig — kein Ein-App-Laravel mehr, kein Breeze, keine Subdomain-in-einer-
> Codebasis. Ziel dieses Dokuments: ein frischer Agent kann jede Phase **ohne
> Rückfrage** abarbeiten, weil Struktur (`.docs/01-architecture/*`) und Fachlogik
> (`.docs/04-domain/*`, `grill-log.md`) bereits vollständig deklariert sind.

## Phase 0 — Status quo (erledigt)

- Altes Ein-App-Laravel-Skelett vollständig entfernt (ADR-018, Commit `c03afcd`).
  Repo-Root enthält aktuell **nur** Doku/Skills/Config, kein Laravel-Code.
- Architektur beschlossen: Monorepo `packages/core` (fachliche Module, SSOT) +
  `apps/{website,shop,crm,portal}` (je eigenständiges Laravel-Projekt,
  ADR-011/013).
- **Bau-Fokus**: ausschließlich `packages/core` + `apps/crm` (ADR-020). Die
  anderen drei Apps existieren bereits extern (Shop live, Portal größtenteils,
  Website als Blade-Frontpage) und werden **nicht** jetzt gebaut — nur als
  Verzeichnis-Platzhalter mit Composer-Wiring vorbereitet.
- Fachlich spezifiziert (autoritativ, keine offenen Grundsatzfragen mehr):
  `CORE.md` (D-001–025, D-078, D-092), `IDENTITY_RBAC.md` (D-026–033),
  `SERVICE.md` (D-034–045, D-059–065, D-079–085, D-091), `SCHEDULING.md`
  (D-046–050, D-088, D-090), `SALES.md` (D-051–055, D-086–087 — Phasen-/
  %-Liste D-089 steht noch aus), `BILLING.md` (D-056–077).

## Phase 1 — `packages/core`-Grundgerüst

Composer-Package `dormed/core`, PSR-4 `Dormed\Core\`, Struktur nach
[`../01-architecture/PROJECT_STRUCTURE.md`](../01-architecture/PROJECT_STRUCTURE.md).

1. `packages/core/composer.json` (`type: library`, keine eigene Laravel-Installation).
2. Modul **`Core`** (`src/Modules/Core/`, `depends_on: []`): `module.php`,
   `Models/User.php`, `Models/Role.php` (+ Pivot `role_user`), `PermissionService`
   — Felder exakt nach `IDENTITY_RBAC.md`.
3. Modul **`Crm`** (`src/Modules/Crm/`, `depends_on: [Core]`): `module.php`,
   Models `Company`/`Person`/`CompanyContact`/`Address`/`Location`/
   `ContactChannel`/`Consent`/`MedicalSpecialty` — Felder exakt nach `CORE.md`.
4. `database/migrations/`: `users`, `roles`, `role_user`, `cache`, `jobs`
   (Standard-Laravel) + `companies`, `people`, `company_contacts`, `addresses`,
   `locations`, `contact_channels`, `consents`, `medical_specialties`.
5. `tests/Architecture/ModuleBoundariesTest` (portiert aus dem alten Repo-Stand,
   Pfade auf `src/Modules/**` angepasst) — erzwingt `depends_on`, Azyklik, kein
   `Illuminate\Http`/App-Namespace-Import in `src/**`.

**Noch nicht in Phase 1:** Service/Scheduling/Sales/Billing-Module — die kommen
erst in Phase 7, obwohl sie bereits vollständig spezifiziert sind (kein
Vorab-Bauen ungenutzter Module, ADR-010).

## Phase 2 — `apps/crm`-Grundgerüst

1. `composer create-project laravel/laravel apps/crm`, danach Path-Repository
   auf `../../packages/core` + `require dormed/core` (siehe
   `PROJECT_STRUCTURE.md`).
2. **Fortify** (ADR-023): `composer require laravel/fortify`,
   `php artisan fortify:install`. Nur **Login** aktiv (D-032) — Registrierung,
   Passwort-Reset-Self-Service, E-Mail-Verifizierung **deaktivieren**.
3. **Inertia.js + Svelte** (ADR-019): `inertiajs/inertia-laravel` (Server),
   `@inertiajs/svelte` + `svelte` + `@sveltejs/vite-plugin-svelte` (npm).
   `Fortify::loginView(fn () => Inertia::render('Auth/Login'))`.
4. **Octane/FrankenPHP** (ADR-022): `composer require laravel/octane`,
   `php artisan octane:install --server=frankenphp`.
5. **Reverb** (ADR-024): `composer require laravel/reverb`,
   `php artisan reverb:install` — eigener Service/Prozess, nicht im
   Octane-Prozess.
6. `auth.php`: `User`-Provider zeigt auf `Dormed\Core\Modules\Core\Models\User`.

## Phase 3 — Lokaler Dev-Stack

`compose.yaml` (siehe [`../06-infrastructure/DOCKER.md`](../06-infrastructure/DOCKER.md)):

- `crm` (Octane/FrankenPHP), `crm-reverb`, `migrate` (Einmal-Container,
  `packages/core`-Migrations, ADR-015), `postgres`, `minio` (ADR-025, aktuell
  ohne konkreten Verwendungszweck).
- `website`/`shop`/`portal`: **kein** Container jetzt — Platzhalter-Verzeichnis
  reicht (ADR-020).
- Lokale Domain: `crm.dormed.test` → `/etc/hosts` (siehe
  [`../01-architecture/MULTI_SUBDOMAIN.md`](../01-architecture/MULTI_SUBDOMAIN.md)).
  `dormed.test` (Website) und `portal.dormed.test`/`shop.dormed.test` können
  parallel eingetragen werden, auch wenn die Container dafür noch fehlen.

## Phase 4 — Demo-Seeder

- Bootstrap-Admin (`is_admin`, D-028) — Interim-Login (D-029), keine
  Registrierung.
- Demo-`Company` + `Person`/`CompanyContact` (reproduzierbar, nie in Production,
  `DOCKER.md`).

## Phase 5 — Erster vertikaler Slice: Crm-Modul in `apps/crm`

`Company` / `Person` / `CompanyContact` / `Address` / `Location` — Liste,
Detailansicht, Anlegen, Bearbeiten (Inertia+Svelte-Views), Autorisierung
(`PermissionService`, Rollen aus D-031), Papierkorb/Löschen nur
`management`/`backoffice` (D-023), Audit (`TracksBlame`, portiert), Tests
(`apps/crm/tests/Feature`).

## Phase 6 — Discovery mit echtem UI

Nur noch für **echte** Restfragen, die beim Bauen auftauchen — die meisten
CRM-Fachfragen sind bereits durch D-001–025/078/092 beantwortet. Kein
grundsätzlicher Discovery-Bedarf mehr wie ursprünglich angenommen.

## Phase 7+ — weitere Module in `apps/crm` (Reihenfolge)

Alle bereits fachlich spezifiziert — direkt implementierbar, keine erneute
Diskussion nötig:

1. **Identity/RBAC-Ausbau** (SSO später, D-029) — Rollen/Permissions-Katalog
   je nachfolgendem Modul erweitern.
2. **Scheduling** (`SCHEDULING.md`, D-046–050/088/090).
3. **Service** (`SERVICE.md`, D-034–045/059–065/079–085/091/092).
4. **Sales** (`SALES.md`, D-051–055/086–087 — D-089 bis dahin nachliefern).
5. **Billing** (`BILLING.md`, D-056–077).

Danach — **noch Discovery, nicht spezifiziert**:

6. Documents (Datei-Infra, PDF-Erzeugung — Template-Kandidaten AnkaReports/
   NextReports, `DOMAIN.md`-Notiz).
7. Inventory (Produktkatalog/Ersatzteile — `INVENTORY.md`, bewusst noch nicht
   im Detail spezifiziert, ADR-010/eigene „nicht vor CRM/Service stabil"-Regel).
8. Communication.

Danach — **externe Systeme hereinmigrieren**, nicht neu bauen (ADR-020):

9. `apps/shop` (live, separates Repo, Inertia+Svelte).
10. `apps/portal` (größtenteils vorhanden).
11. `apps/website` (bestehende Blade-Frontpage).

## Keine Big-Bang-Implementierung

Nicht gleichzeitig mehrere Module/Apps parallel bauen. Jeder Slice bleibt lokal
lauffähig und testbar, bevor der nächste beginnt (unverändert aus der alten
Fassung — gilt weiterhin, jetzt auf `packages/core`-Module statt `app/Modules`
bezogen).
