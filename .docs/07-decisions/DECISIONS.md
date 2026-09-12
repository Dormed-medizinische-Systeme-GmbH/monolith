# Architecture Decisions

## ADR-001 — PostgreSQL statt Supabase als Application Platform

Status: Accepted

PostgreSQL ist die primäre Datenbank. Supabase ist keine notwendige Laufzeitplattform.

Begründung:

- Laravel soll zentraler Application Layer sein.
- direkter Laravel -> PostgreSQL Zugriff ist gewünscht.
- Vendor Lock-in soll reduziert werden.
- PostgreSQL bietet die benötigten relationalen Fähigkeiten einschließlich nativer RLS-Funktionalität.

## ADR-002 — Modular Monolith

Status: **Superseded by ADR-011** (2026-09-12)

CRM, Portal, Shop und später Inventory werden innerhalb einer Laravel-Anwendung entwickelt.

## ADR-003 — Multi-Subdomain

Status: **Superseded by ADR-012** (2026-09-12)

Mehrere Subdomains bedienen dieselbe Laravel-Codebasis.

## ADR-004 — Legacy nicht als Zielschema

Status: Accepted

Legacy-XMLs dienen der Ist-Analyse.

Universalobjekte werden nicht automatisch übernommen.

## ADR-005 — Workflow statt freie Feldbearbeitung

Status: Accepted

Fachliche Prozesse werden als kontrollierte Zustands-/Aktionsmodelle umgesetzt.

## ADR-006 — Structured Data as Source of Truth

Status: Accepted

Strukturierte Fachdaten sind primär. PDFs und andere Darstellungen sind Repräsentationen, sofern kein rechtlicher/operativer Grund für die Datei als unveränderliche Primärrepräsentation besteht.

## ADR-007 — PostgreSQL als zusätzliche Security Boundary

Status: Accepted in principle

RLS/Constraints/Trigger können zusätzliche technische Sicherheit und Integrität liefern.

Die konkrete RLS-Architektur wird vor produktiver Aktivierung spezifiziert.

## ADR-008 — Device und ServiceContract getrennt

Status: Accepted

Ein ServiceContract ist nicht gleichzeitig das vollständige Device Master Data Objekt.

## ADR-009 — Company / Person / Contact / Location getrennt

Status: Accepted

Die Legacy-Universaladresse wird nicht als Zielmodell übernommen.

## ADR-010 — Keine künstliche Vollständigkeit

Status: Accepted

Offene fachliche Fragen werden erst entschieden, wenn reale Prozesse und UI sie konkret machen.

## ADR-011 — Monorepo mit vier eigenständigen Laravel-Apps + geteiltem Core-Package

Status: Accepted (2026-09-12) · ersetzt ADR-002

Kein Modular Monolith mehr in **einer** Laravel-Anwendung. Stattdessen ein Monorepo mit
**vier eigenständigen Laravel-Projekten** (`apps/website`, `apps/shop`, `apps/crm`,
`apps/portal` — ADR-017) plus einem geteilten Composer-Package `packages/core`
(Models, Migrations, domänenübergreifende Services — Single Source of Truth). Jede App
bindet `packages/core` als lokales Path-Repository ein (Details: `PROJECT_STRUCTURE.md`).

Begründung:
- Blast-Radius-Minimierung: Änderungen an einer App bauen/deployen nur deren Container neu.
- Trotzdem eine Datenquelle: eine PostgreSQL-Instanz, ein Models-/Migrations-Stand, keine
  Drift zwischen Apps.
- Die vier Domänen sind strukturell gleichwertig — keine Sonderrolle für eine einzelne
  (weder Shop noch CRM noch Portal noch Website).
- Kein Multi-Tenancy-Muster: die vier Apps sind fachliche Module desselben Unternehmens,
  keine Mandanten.

## ADR-012 — Routing: eine Domain pro App statt Subdomain-Dispatch in einer Codebasis

Status: Accepted (2026-09-12) · ersetzt ADR-003

Jede der vier Apps bedient ihre eigene (Sub-)Domain über ihr **eigenes** Laravel-Routing.
Das bisherige zentrale `config('domains.<ctx>')` + `App\Support\ApplicationContext` +
`ResolveApplicationContext`-Middleware, das den Kontext aus dem Host **einer** Codebasis
ableitet, entfällt mit ADR-011 — es gibt keine gemeinsame Codebasis mehr, die mehrere Hosts
unterscheiden müsste. Siehe `MULTI_SUBDOMAIN.md` (neu: Multi-App-Routing).

## ADR-013 — Fachliche Modulgrenzen leben innerhalb von `packages/core`

Status: Accepted (2026-09-12)

Die fachliche Modulaufteilung aus `ARCHITECTURE.md` §4 (Core, Identity, CRM, Service,
Billing, Sales, Scheduling, Documents, Inventory, Platform, Integrations, …) bleibt
inhaltlich unverändert bestehen — sie wandert nur von `app/Modules/<Modul>/` nach
`packages/core/src/Modules/<Modul>/`. Jedes Modul deklariert weiterhin `module.php` mit
`depends_on`; der Abhängigkeitsgraph bleibt azyklisch und wird durch
`ModuleBoundariesTest` erzwungen — jetzt im Kontext von `packages/core`, nicht mehr
`app/Modules`. Die vier Apps (`apps/*`) referenzieren `packages/core` als Ganzes und
enthalten selbst **keine** Models/Migrations/domänenübergreifenden Services, nur noch
Controller/Views/Routen/App-lokale DTOs (ADR-011, Regel 1+2 aus
`03-domain-boundaries-rules.md`).

Diese Entscheidung hält die gesamte bisherige Domain-Spec-Arbeit
(`.docs/04-domain/{CORE,SERVICE,SCHEDULING,SALES}.md`, `.docs/07-decisions/grill-log.md`
D-001–D-0NN) strukturell gültig — nur der Wurzelpfad ändert sich.

## ADR-014 — Deployment bleibt auf Coolify, kein eigenständiges Docker Swarm

Status: Accepted (2026-09-12)

Trotz des Vier-Apps-Layouts bleibt **Coolify** die Deployment-Plattform (bestehender
Deploy-Key, `compose.prod.yaml`-Pattern). Coolify orchestriert die vier App-Container +
TLS-Terminierung selbst; es wird **kein** eigenständiger `docker stack deploy`/Swarm-Betrieb
aufgesetzt. Die Docker-**Build**-Strategie (ein Dockerfile pro App, Multi-Stage mit
`packages/core` als eigener Build-Stage für Cache-Effizienz, siehe `DOCKER.md`) wird aus
der Architektur-Vorlage übernommen — die Swarm-spezifische Orchestrierung (Overlay-Network,
`docker service create`, eigener Proxy-Container) nicht.

## ADR-015 — Migrations laufen als separater Deploy-Schritt, nicht im Container-Boot

Status: Accepted (2026-09-12)

`php artisan migrate` läuft **nicht mehr** im Start-Kommando eines App-Containers (bisher:
`compose.prod.yaml` CMD). Stattdessen ein separater, einmaliger Migrations-Schritt
(Coolify-Pre-Deploy-Command o. ä.) gegen die eine gemeinsame Postgres-Instanz, **vor** dem
(Neu-)Start der vier App-Container. Verhindert Race-Conditions zwischen mehreren
gleichzeitig (neu) startenden Containern, die alle denselben `packages/core`-Migrations-Stand
mitbringen.

## ADR-016 — Cross-App-Login: CRM getrennt, Portal und Shop teilen den Kundenlogin

Status: Accepted (2026-09-12)

Es gibt **keinen** einheitlichen Login über alle vier Apps. `apps/crm` (Mitarbeiter,
D-027/D-029, `IDENTITY_RBAC.md`) ist strikt getrennt von den kundenseitigen Apps — eigene
Session, eigene Nutzertabelle. `apps/portal` und `apps/shop` dagegen bedienen **denselben**
Kunden und teilen sich einen Login: derselbe Kunden-Account bewegt sich mit einer Session in
beiden Apps. `apps/website` ist überwiegend anonym, ohne Login.

Technischer Mechanismus für den geteilten Portal/Shop-Login (Vorschlag, bei Umsetzung zu
bestätigen): gleiche `SESSION_DOMAIN` + gleicher `APP_KEY` + eine gemeinsame `sessions`-
Tabelle in der einen Postgres-Instanz, genutzt **nur** von diesen beiden Apps — kein
Cross-App-Mechanismus für `crm` oder `website`.

## ADR-017 — Website als vierte gleichwertige Domäne

Status: Accepted (2026-09-12)

Neben CRM, Portal und Shop gibt es eine vierte, strukturell gleichwertige Domäne
**Website** (öffentlicher Marketing-/Info-Auftritt, überwiegend anonym). Sie folgt
demselben App/Core-Muster wie die anderen drei (ADR-011) — keine Sonderbehandlung.

## ADR-018 — Bestehender Anwendungscode wird verworfen, Neuaufbau als eigenständiges Skelett

Status: Accepted (2026-09-12)

Der bisherige Anwendungscode wird vollständig verworfen und durch das Vier-Apps-+-
`packages/core`-Skelett (ADR-011–015) neu aufgesetzt. Betrifft insbesondere:

- `app/Modules/{Core,Crm}` (Models, `module.php`)
- die 5 bestehenden Migrationen (`companies`, `people`, `addresses`, `company_contacts`,
  `locations`)
- `routes/{crm,portal,shop}.php`, `App\Support\ApplicationContext`,
  `App\Http\Middleware\ResolveApplicationContext`, `config/domains.php`
- das Ein-App-Compose-/Dockerfile-Setup (`compose.yaml`, `compose.prod.yaml`,
  `docker/app`, `docker/app-prod`)
- `volumes/` — Altlast eines Supabase-Self-Hosted-Stacks (kong.yml, jwt.sql, pooler.exs,
  realtime.sql, `functions/hello`), obsolet seit ADR-001 (Postgres statt Supabase), ohne
  jede Referenz im aktuellen Repo

**Nicht betroffen** (bleibt unverändert erhalten): `.docs/`, `.ai/rules/`, Skills, MCP-
Konfiguration und alles, was nicht Laravel-Framework-/Anwendungscode ist. Die tatsächliche
Löschung/der Neuaufbau erfolgt erst als eigener, separat bestätigter Schritt — nicht
automatisch mit dieser Dokumentations-Synthese.

## ADR-019 — Frontend-Stack: Blade für Website, Inertia.js + Svelte für CRM (Shop/Portal-Detail bei Migration)

Status: Accepted (2026-09-12)

- **`apps/website`** (Landingpage): bleibt **Blade** — deckt sich mit den bereits
  bestehenden Blade-Views der Frontpage.
- **`apps/crm`**: **Inertia.js + Svelte** (`@inertiajs/svelte`).
- **`apps/shop`**: bereits **live**, Inertia.js + Svelte, in einem **separaten**
  Laravel-Repo außerhalb dieses Monorepos — wird zu einem späteren Zeitpunkt
  hereinmigriert (siehe ADR-020), nicht Teil der aktuellen Bauphase.
- **`apps/portal`**: existiert größtenteils bereits, vermutlich ebenfalls Inertia; genauer
  Stack aktuell **unbekannt** — wird bei der Portal-Migration geklärt.

Begründung: Svelte für CRM sorgt für Konsistenz mit dem bestehenden, bereits produktiven
Shop und ermöglicht potenziell später geteilte UI-Patterns über App-Grenzen hinweg (der
Code selbst bleibt strikt getrennt je App, ADR-011 — das ist eine Design-, keine
Code-Teilungsfrage, analog `ARCHITECTURE.md` §3).

## ADR-020 — Bauphase: harter Fokus auf `packages/core` + `apps/crm`; Website/Portal/Shop nur architektonisch vorbereitet

Status: Accepted (2026-09-12)

Die aktuelle Bauphase beschränkt sich auf **`packages/core`** (die gesamte fachliche
Domäne, `.docs/04-domain/*`, `grill-log.md` D-001ff.) und **`apps/crm`**. Die übrigen drei
Apps existieren bereits als eigenständige, teils live laufende Systeme außerhalb dieses
Repos und werden erst später hereinmigriert:

- **Shop**: separates Laravel + Inertia + Svelte-Repo, **live** im Betrieb.
- **Portal**: größtenteils bereits vorhanden, genauer Stack noch ungeklärt.
- **Website**: bestehende Blade-Views für die Frontpage.

Für diese drei wird jetzt **nur die architektonische Einbettung** vorbereitet
(Verzeichnisplatzhalter unter `apps/`, Composer-Path-Repository-Wiring zu
`packages/core`, Domain-Zuordnung, ADR-016-Login-Mechanismus für Portal/Shop) — **kein**
aktiver Aufbau von Fachlogik oder Views, bis die jeweilige Migration konkret angegangen
wird.

## ADR-021 — Live-Domains

Status: Accepted (2026-09-12)

| App | Live-Domain |
| --- | --- |
| `website` (Landingpage) | `dormed.de` |
| `portal` | `my.dormed.de` |
| `shop` | `shop.dormed.de` |
| `crm` | `crm.dormed.de` (bereits aktiv) |

Die bisherigen Coolify-Staging-Domains (`dormed-{crm,portal,shop}.everding.it`,
`.ai/rules/local-stack.md`) bleiben für Test/Staging bestehen — die `dormed.de`-Domains
sind das Produktions-Ziel.

Lokal (Dev): `dormed.test` als Basis-Domain (analog `dormed.de`), Subdomains
`crm.dormed.test` / `portal.dormed.test` / `shop.dormed.test` / `website.dormed.test` —
je auf `127.0.0.1` in `/etc/hosts`. Details: `.docs/01-architecture/MULTI_SUBDOMAIN.md`,
`.docs/02-development/LOCAL_DEVELOPMENT.md`.

## ADR-022 — Laravel Octane als App-Server, Driver FrankenPHP

Status: Accepted (2026-09-12)

Alle Apps laufen hinter **Laravel Octane** statt `php-fpm`/`artisan serve` für
Performance (persistenter Application-Bootstrap zwischen Requests). Als Octane-Treiber:
**FrankenPHP** (`php artisan octane:install --server=frankenphp`) — Begründung:

- Läuft als einzelnes statisches Binary, offizielles Docker-Image
  (`dunglas/frankenphp`), keine zusätzliche Extension-Kompilation wie bei Swoole.
- Ist Laravels aktuell empfohlener Octane-Standard für neue Projekte.
- Bringt HTTP/2 und eingebaute Static-File-Auslieferung mit, passt gut zum
  Coolify-Reverse-Proxy-Setup (ADR-014).

Gilt zunächst für `apps/crm` (aktueller Bau-Fokus, ADR-020); die übrigen Apps
übernehmen denselben Server, sobald sie aktiv gebaut werden.

## ADR-023 — Laravel Fortify als Auth-Backend für CRM (Login-only)

Status: Accepted (2026-09-12)

`apps/crm` nutzt **Laravel Fortify** (headless Auth-Backend) statt der bisherigen
Breeze-Scaffolding-Controller — passend zu Inertia.js + Svelte (ADR-019), da Fortify
keine eigenen Blade-Views vorschreibt, sondern nur Routen/Actions liefert, die die
App selbst rendert (`Fortify::loginView(fn () => Inertia::render('Auth/Login'))`).

Feature-Umfang bewusst **minimal**, passend zu D-032 (kein Self-Service):
**nur Login** aktiviert. Registrierung, Passwort-Reset-Self-Service und E-Mail-
Verifizierung bleiben **deaktiviert** — Nutzeranlage/Passwort-Reset laufen weiterhin
über die Admin-Einladungsfunktion (`.docs/03-security/IDENTITY_RBAC.md`).

## ADR-024 — Reverb: eigener Service pro App, keine geteilte Instanz

Status: Accepted (2026-09-12)

Laravel Reverb (`.docs/06-infrastructure/REALTIME.md`) läuft als **eigener,
langlebiger Prozess** neben dem jeweiligen Octane-App-Prozess (nicht im selben
Prozess/Container-Command wie der HTTP-Server) — analog zu Laravels eigenem
Reverb-Betriebsmodell. Jede App, die Realtime braucht, bekommt bei Bedarf ihren
eigenen Reverb-Service (kein einzelner, von allen vier Apps geteilter Reverb-Prozess)
— konsistent mit „keine direkte Kommunikation zwischen den Apps" (ADR-011): Reverb
gehört zur jeweiligen App, nicht zu `packages/core`. Aktuell nur für `apps/crm`
relevant (ADR-020).

## ADR-025 — MinIO/S3-Object-Storage als Compose-Service (vorbereitend, ohne aktuelle Nutzung)

Status: Accepted (2026-09-12)

Ein **MinIO**-Service (S3-kompatibel, `.docs/06-infrastructure/STORAGE.md` nennt ihn
bereits als Kandidaten) wird als Compose-Service vorgesehen — **aktuell ohne
konkreten Verwendungszweck**, rein infrastrukturelle Vorbereitung. Geplante spätere
Nutzung:

- Produktbilder (Website/Landingpage, Shop, CRM-Produktverwaltung im Management-Bereich)
- Foto-Dokumentation von Service-/Wartungseinsätzen (`MaintenanceReport`/`ServiceCase`,
  `.docs/04-domain/SERVICE.md` „Fotos/Nachweise → documents, polymorph am Report")

Ein Bucket/eine Nutzung wird erst angelegt, wenn der jeweilige fachliche Bereich
(Inventory bzw. Documents) konkret gegrillt wird (ADR-010) — der Service steht schon,
damit spätere Slices ihn direkt nutzen können, ohne Infrastruktur nachzuziehen.
