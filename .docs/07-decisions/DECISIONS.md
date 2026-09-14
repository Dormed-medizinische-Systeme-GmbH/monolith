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

Status: Accepted · **konkretisiert durch ADR-036** (2026-09-14)

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

> **Pfad-Nachtrag (ADR-030, 2026-09-13):** `apps/<app>` heißt jetzt
> `<domain>/` im Wurzelverzeichnis, `packages/core` heißt `core/`. Der Inhalt dieser
> ADR bleibt gültig, nur die Pfade sind andere.

## ADR-011 — Monorepo mit vier eigenständigen Laravel-Apps + geteiltem Core-Package

Status: **Superseded by ADR-033** (2026-09-14) · ersetzte ADR-002

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

Status: **Superseded by ADR-033** (2026-09-14) · ersetzte ADR-003 — mit dem Monolithen ist Subdomain-Dispatch in **einer** Codebasis wieder der Weg, ADR-003 gilt der Sache nach erneut

Jede der vier Apps bedient ihre eigene (Sub-)Domain über ihr **eigenes** Laravel-Routing.
Das bisherige zentrale `config('domains.<ctx>')` + `App\Support\ApplicationContext` +
`ResolveApplicationContext`-Middleware, das den Kontext aus dem Host **einer** Codebasis
ableitet, entfällt mit ADR-011 — es gibt keine gemeinsame Codebasis mehr, die mehrere Hosts
unterscheiden müsste. Siehe `MULTI_SUBDOMAIN.md` (neu: Multi-App-Routing).

## ADR-013 — Fachliche Modulgrenzen leben innerhalb von `packages/core`

Status: **Superseded by ADR-033** (2026-09-14) — die Modulgrenzen und `ModuleBoundariesTest` bleiben gültig, sie liegen jetzt unter `app/Modules/` statt in einem Package

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
Deploy-Key, `docker-compose.prod.yaml`-Pattern). Coolify orchestriert die vier App-Container +
TLS-Terminierung selbst; es wird **kein** eigenständiger `docker stack deploy`/Swarm-Betrieb
aufgesetzt. Die Docker-**Build**-Strategie (ein Dockerfile pro App, Multi-Stage mit
`packages/core` als eigener Build-Stage für Cache-Effizienz, siehe `DOCKER.md`) wird aus
der Architektur-Vorlage übernommen — die Swarm-spezifische Orchestrierung (Overlay-Network,
`docker service create`, eigener Proxy-Container) nicht.

## ADR-015 — Migrations laufen als separater Deploy-Schritt, nicht im Container-Boot

Status: Accepted (2026-09-12)

`php artisan migrate` läuft **nicht mehr** im Start-Kommando eines App-Containers (bisher:
`docker-compose.prod.yaml` CMD). Stattdessen ein separater, einmaliger Migrations-Schritt
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

Status: **Superseded by ADR-038** (2026-09-14) — die Website bleibt gleichwertig, ist aber keine eigene Anwendung mehr, sondern eine Domain-Route im Monolithen

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
- das Ein-App-Compose-/Dockerfile-Setup (`docker-compose.yaml`, `docker-compose.prod.yaml`,
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

Lokal (Dev): `dormed.test` als Basis-Domain (analog `dormed.de`) bedient **die
Website** direkt, ohne Subdomain (genau wie `dormed.de` live keine Subdomain hat).
Zusätzlich Subdomains `crm.dormed.test` / `portal.dormed.test` / `shop.dormed.test` —
je auf `127.0.0.1` in `/etc/hosts`. Details: `.docs/01-architecture/MULTI_SUBDOMAIN.md`,
`.docs/02-development/LOCAL_DEVELOPMENT.md`.

## ADR-022 — Laravel Octane als App-Server, Driver FrankenPHP

Status: **Superseded by ADR-034** (2026-09-14) — kein Octane; nginx + php-fpm

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

Status: **Superseded by ADR-027** (2026-09-13) — Reverb wird vorerst nicht gebaut.
Die Aussage „eigener Prozess, keine geteilte Instanz" bleibt gültig, falls Realtime
später kommt.

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
- ~~Foto-Dokumentation von Service-/Wartungseinsätzen~~ — **zurückgenommen durch
  ADR-028**: der Bucket ist vollständig öffentlich und trägt daher keine Kundendaten.
  Wo Einsatzfotos liegen, wird im Bereich Documents entschieden.

Ein Bucket/eine Nutzung wird erst angelegt, wenn der jeweilige fachliche Bereich
(Inventory bzw. Documents) konkret gegrillt wird (ADR-010) — der Service steht schon,
damit spätere Slices ihn direkt nutzen können, ohne Infrastruktur nachzuziehen.

## ADR-026 — Deployment-Schnitt: eine Compose-Anwendung für alle Apps, Zustandsbehaftetes getrennt

Status: **Superseded by ADR-033/ADR-041** (2026-09-14) · präzisierte ADR-014 — die Begründung zu **Postgres und MinIO als eigene Coolify-Ressourcen** (Backups, eigener Lebenszyklus) bleibt gültig und wird von ADR-041 übernommen; hinfällig sind der Vier-Apps-Teil und der Dev-Stack-Teil

Die vier Apps werden in Coolify als **eine einzige Anwendung** aus **einer**
`docker-compose.prod.yaml` mit **einer** `.env` deployt — bewusst **ohne** die Möglichkeit,
einzelne Apps über die Coolify-UI separat zu starten oder auszurollen.

**Begründung — Schema-Integrität.** Die vier Apps teilen sich eine Postgres-Instanz
und `packages/core` (ADR-011). Bei einem Core-Update sind sie deshalb **nicht**
unabhängig: vier getrennte Deploys erzeugen ein Zeitfenster, in dem eine App bereits
neuen Core-Code gegen das neue Schema fährt, während eine andere noch alten Code
gegen dasselbe Schema fährt. Coolify ersetzt bei einem Compose-Deploy alle Container
in einem Vorgang, sobald der Build durch ist — das schließt dieses Fenster.

**Zustandsbehaftetes bleibt draußen.** „Alle Apps in einer Compose" heißt
ausdrücklich **nicht** „Postgres auch":

```text
Coolify Application (Compose)  →  website · shop · crm · portal   ein Deploy, eine .env
Coolify Database               →  postgres                        eigener Lebenszyklus
```

PostgreSQL läuft als **Coolify-eigene Datenbank-Ressource**, nicht als Service in der
App-Compose. Grund: Coolify sichert automatisiert nur seine eigenen Datenbank-
Ressourcen — ein selbst in der Compose definierter `postgres`-Container bekommt weder
geplante Backups noch Restore aus der UI. Bei einer Datenbank, die alle vier Apps
teilen, ist das der Unterschied zwischen vorhandenem Backup und gesichertem Volume im
besten Fall. Zusätzlicher Effekt: die Datenbank überlebt jedes App-Deployment
unberührt. Gleiches gilt für **MinIO** (ADR-025), sobald es genutzt wird.

**Bewusst getragene Konsequenzen:**

- Eine Blade-Änderung an `website` startet auch `crm` neu. Das ist der Gegenwert für
  die Integrität und bei vier Apps mit geteiltem Core vertretbar.
- Was wegfällt, ist der Per-App-Knopf in der Coolify-UI, **nicht** die Fähigkeit:
  `docker compose restart crm` funktioniert weiterhin per SSH auf dem Server.
- Die Build-Effizienz leidet nicht. Der Multi-Stage-Cache aus `DOCKER.md` wirkt
  weiterhin — unveränderte Apps bauen schnell aus dem Layer-Cache. Verloren geht
  Deploy-Granularität, nicht Build-Granularität.

**Migrations (ADR-015)** laufen als **Pre-Deploy-Command dieser einen Anwendung**,
mit `php artisan migrate --force --isolated`. Die ursprüngliche Sorge, an welche der
vier Apps der Schritt gehört, entfällt: es gibt genau eine Anwendungsressource, und
sie umfasst alle vier Apps. `--isolated` macht einen versehentlichen Doppelstart
folgenlos.

> **Verbleibendes Betriebsrisiko:** Beim Rollout laufen alte Container kurz gegen das
> bereits migrierte Schema. Additive Migrationen sind unkritisch; eine Spalte
> umzubenennen oder zu löschen reißt die noch nicht ersetzten Container ab.
> Destruktive Änderungen daher im **Expand/Contract-Muster**: erst hinzufügen,
> ausrollen, in einem **späteren** Deploy entfernen.

**Dev bleibt getrennt davon:** `docker-compose.yaml` startet lokal weiterhin **alles**
inklusive Datenbank mit einem `docker compose up` (siehe
`.ai/rules/local-stack.md`). Die Prod-Trennung Datenbank/Apps gilt dort nicht.

## ADR-027 — Realtime/Reverb vertagt

Status: Accepted (2026-09-13) · **supersedes ADR-024**

Laravel Reverb wird **vorerst nicht gebaut**. Es gibt keinen `<app>-reverb`-Service,
weder in `docker-compose.yaml` noch in `docker-compose.prod.yaml`.

**Begründung:** Kein Anwendungsfall im aktuell spezifizierten Umfang erzwingt Push.
Die in `.docs/06-infrastructure/REALTIME.md` genannten Fälle:

- „Kunde bucht Wartung → Mitarbeiter erhält Live-Information" hängt an `apps/portal`,
  das nach ADR-020 nicht in der aktuellen Bauphase liegt.
- „Ticket-/Servicestatus ändert sich" und „Management-Dashboard aktualisiert sich"
  funktionieren ohne Push — das Cockpit (D-126) ist eine Sammlung von Listen und
  Kennzahlen, die beim Laden aktuell ist.

Ein eigener Reverb-Container kostet dagegen von Tag eins einen zweiten langlebigen
Prozess mit eigenem Port, eigenem Healthcheck und eigener Betriebssorge — für eine
Fähigkeit, die noch niemand abruft.

**Nachrüstbar, kein Umbau.** Reverb ist ein additiver Schritt: Service hinzufügen,
`BROADCAST_CONNECTION` setzen, Events und Channels ergänzen. Nichts am jetzt
gebauten Skelett muss dafür umgestellt werden.

**Was aus ADR-024 gültig bleibt, wenn Realtime kommt:** ein eigener Prozess/Container
pro App (nicht im selben Container-Command wie der HTTP-Server, da ein Container
genau ein `CMD` hat und zwei Daemons sonst einen Supervisor bräuchten), und **kein**
von allen vier Apps geteilter Reverb-Prozess.

## ADR-028 — Object Storage: ein öffentlicher Bucket für Marketing-Assets; Signaturen gehören in die Datenbank

Status: Accepted (2026-09-13) · **präzisiert ADR-025**

### Der Schnitt

```text
CRM        → schreibt (einziger Schreibpfad)  ─┐
                                                ├─→  MinIO (ein Bucket, 100 % öffentlich)
packages/core / Postgres → Metadaten, Zuordnung, Version, fachlicher Kontext

Website / Shop  →  lesen direkt aus dem Bucket (nur Leserechte)
```

- **MinIO auf demselben Host**, wie in ADR-025 vorgesehen. **Cloudflare davor als
  Cache** — der Origin liefert dann nur Cache-Misses aus, die großen Prospekt-PDFs
  gehen an der Maschine vorbei. Ein vollständig öffentlicher Bucket ist dafür der
  ideale Fall, weil alles cachebar ist.
- **Ein einziger, vollständig öffentlicher Bucket.** Keine ACLs je Objekt, keine
  signierten URLs, keine Pfadkonventionen für Sichtbarkeit.
- **Nur das CRM schreibt.** Website und Shop haben ausschließlich Lesezugriff. Ein
  Schreibpfad, eindeutige Verantwortung.

**Das verletzt ADR-011 nicht.** Die vier Apps kommunizieren weiterhin nicht direkt
miteinander — sie teilen sich Infrastruktur, genau wie bei Postgres. Die fachliche
Zuordnung („welches Bild gehört zu welchem Artikel") lebt in Postgres via
`packages/core`, der Object Storage hält nur die Bytes. Dieselbe Integrationsform,
nur ein zweiter Speicher.

### Was **nicht** in den Bucket gehört

Weil der Bucket vollständig öffentlich ist, ist jede Datei darin für jeden
erreichbar, der die URL kennt oder rät. Es gibt keine Zugriffsprüfung, die man
später nachrüsten könnte. Deshalb:

- **Unterschriften** (`MaintenanceReport.signature_image`, D-045) → **in die
  Datenbank**, nicht in den Object Storage.
- **Foto-Dokumentation von Service-/Wartungseinsätzen** → **nicht** in diesen Bucket.
  ADR-025 nannte sie als geplante Nutzung; das ist hiermit **zurückgenommen**. Wo sie
  stattdessen liegen, wird im Bereich **Documents** entschieden (siehe unten).

**Der Bucket trägt damit ausschließlich öffentliche Marketing-Assets:** Produktbilder
für Website, Shop und CRM-Produktverwaltung sowie Hersteller-Prospekte.

### Signaturen: `bytea` in Postgres

Eine erfasste Unterschrift ist ein **kleines** Bild — typisch 5–30 KB als PNG. Für
diese Größenklasse ist die Datenbank nicht nur vertretbar, sondern die bessere Wahl:

- Sie gehört **atomar** zum `MaintenanceReport`. Ein Datensatz, eine Transaktion,
  kein verwaister Blob, wenn ein Upload fehlschlägt.
- Sie ist automatisch vom **Datenbank-Backup** erfasst (ADR-026: Coolify sichert die
  Datenbank-Ressource) — kein zweiter Sicherungsweg für ein rechtlich relevantes
  Beweisstück.
- Der Zugriff ist über die bestehende Autorisierung abgedeckt, ohne ein zweites
  System mit eigenem Rechtekonzept.

Spalte: `bytea` (Laravel `binary`). Alternative wäre, die Vektordaten des
Signature-Pads als SVG-Text abzulegen — kleiner und beliebig skalierbar; für einen
Nachweis ist die gerenderte Bitmap aber das robustere Artefakt.

### Fotos sind eine **andere** Größenklasse — offen

> Die Begründung oben trägt **nicht** für Einsatzfotos. Ein Foto ist 1–5 MB, und
> mehrere je Bericht. Als `bytea` würden sie die Datenbank und jedes Backup
> aufblähen — das ist kein Best-Practice-Fall mehr, sondern der klassische
> Anti-Pattern.
>
> **Entschieden in ADR-029:** gemountetes Volume unter `storage/` im CRM-Projekt,
> ausgeliefert über eine Policy-geprüfte Route. Bewusst getrennt vom Object Storage,
> weil die Fotos später eine Aufräumregel brauchen. Der öffentliche Bucket aus dieser
> ADR bleibt davon unberührt.

## ADR-029 — Einsatzfotos auf ein gemountetes Volume im CRM, getrennt vom Object Storage

Status: Accepted (2026-09-13) · **schließt den offenen Punkt aus ADR-028**

Foto-Dokumentation von Wartungen und Servicefällen liegt **im CRM-Projekt unter
`storage/`, auf einem gemounteten Volume** — nicht im Object Storage und nicht als
`bytea` in der Datenbank.

**Begründung (Nutzer):** Für die Fotos wird später eine **Aufräumregel** gebraucht,
und der Object Storage soll davon **getrennt** bleiben. Das ist der fachliche Kern:
die beiden Speicher haben unterschiedliche Lebensdauern. Der öffentliche Bucket trägt
dauerhafte Marketing-Assets (ADR-028); Einsatzfotos sind zeitlich begrenzte Nachweise.
Zwei Lebenszyklen, zwei Speicher — eine Aufräumregel über einen gemeinsamen Speicher
wäre eine dauerhafte Fehlerquelle.

### Ablage

- **Nur `apps/crm`.** Website, Shop und Portal brauchen die Fotos nicht.
- **`storage/app/private/...`**, ausgeliefert über eine Route mit Policy-Prüfung —
  **nie** direkt.

> **Falle:** **nicht** `storage/app/public`. Dieses Verzeichnis wird per
> `storage:link` nach `public/` symlinkt und ist damit ohne jede Prüfung aus dem Netz
> erreichbar — genau der Zustand, den ADR-028 für Kundendaten ausschließt.

### Betriebliche Konsequenzen

- **Das Volume muss in Coolify als Persistent Storage deklariert sein.** Nach ADR-026
  ersetzt jeder Deploy **alle** App-Container. Ein nicht deklariertes Volume wäre bei
  jedem Deploy weg.
- **Es ist nicht im Backup.** Coolify sichert automatisiert nur seine eigenen
  Datenbank-Ressourcen (ADR-026). Für dieses Volume braucht es einen **eigenen**
  Sicherungsweg. Das ist dieselbe Klasse Problem wie bei einem Postgres-Container in
  der App-Compose, nur auf der Dateiebene. **Offen** — siehe unten.
- **Der Platz wächst.** Fotos sind 1–5 MB, mehrere je Bericht, und sie sammeln sich,
  bis die Aufräumregel existiert. Auf dem gemeinsamen Host ist der Plattenplatz zu
  beobachten.

### Offen

- **Die Aufräumregel selbst** — Aufbewahrungsdauer und Auslöser. In der
  Documents-Runde **erneut vertagt** (D-145): „später entscheiden", wenn absehbar ist,
  wie schnell das Volume wächst. Bis dahin wird nichts automatisch gelöscht.
- **Sicherung des Volumes** — Weg und Frequenz. Nicht vergessen: ohne das ist die
  Foto-Dokumentation der einzige Teil der Anwendung ohne Backup.

## ADR-030 — Repository-Layout: App-Ordner im Wurzelverzeichnis, benannt nach ihrer Domain

Status: **Superseded by ADR-033/ADR-035** (2026-09-14) · ersetzte das Layout aus ADR-011/ADR-013 — es gibt keine App-Ordner mehr; die Anwendung liegt im Wurzelverzeichnis, das Altsystem unter `.legacy/`

Kein `apps/`-Zwischenverzeichnis und kein `packages/`. Jede App liegt als eigener
Ordner im Repo-Wurzelverzeichnis und **heißt wie ihre Domain**:

```text
/dormed.de/        Website
/erp.dormed.de/    Mitarbeiter-Anwendung (ADR-031)
/my.dormed.de/     Kundenportal
/shop.dormed.de/   Shop
/core/             geteiltes Composer-Package (vormals packages/core)
```

Aus dem Ordnernamen ist damit sofort ersichtlich, welche Anwendung unter welcher
Adresse läuft. Die **fachliche** Gliederung ändert sich dadurch **nicht**: Module
liegen weiterhin unter `core/src/Modules/<Modul>/` mit Namespace
`Dormed\Core\Modules\<Modul>\`, und die zwei Achsen aus `ARCHITECTURE.md` §1/§4
(Deployment-Einheit vs. fachliche Domäne) bleiben orthogonal.

**Konkrete Folge, die leicht übersehen wird:** Das Path-Repository in der
`composer.json` jeder App zeigt jetzt auf **`../core`**, nicht mehr auf
`../../core` — der Baum ist eine Ebene flacher.

`ADR-011` und `ADR-013` bleiben inhaltlich gültig (vier eigenständige Apps, Module
innerhalb des geteilten Pakets, `module.php` mit `depends_on`, azyklischer Graph) —
nur die Pfade sind andere. Die historischen ADR-Texte werden **nicht** rückwirkend
umgeschrieben; maßgeblich für Pfade ist ab hier
`.docs/01-architecture/PROJECT_STRUCTURE.md`.

## ADR-031 — Die Mitarbeiter-Anwendung heißt ERP; CRM ist eine Domäne darin

Status: Accepted (2026-09-13)

`crm.dormed.de` wird **`erp.dormed.de`** (lokal `erp.dormed.test`) — und das ist
nicht nur eine Umbenennung des Ordners, sondern der fachlich richtigere Name.

Die Anwendung trägt längst mehr als Kundenbeziehungen: **Service** (Wartung,
Servicefälle, Geräte), **Billing** (Fakturierung, Zahlungen, Mahnwesen),
**Inventory** (Warenwirtschaft, Lager, Bestellwesen) und **Sales**. Warenwirtschaft
und Fakturierung sind kein CRM. „ERP" beschreibt, was da tatsächlich entsteht.

**CRM** bleibt als **Domäne** bestehen — Firmen, Personen, Kontakte
(`core/src/Modules/Crm/`, `.docs/05-modules/CRM.md`, `.docs/04-domain/CORE.md`) —
und steht gleichrangig neben Service, Billing, Sales und Inventory.

**Was sich dadurch nicht ändert:** Modulname `Crm`, Namespace
`Dormed\Core\Modules\Crm\`, Permission-Präfix `crm.*` (D-030). Die sind
modulbezogen, nicht anwendungsbezogen, und bleiben korrekt.

## ADR-032 — Technikerflow als PWA im ERP; MVP online-only

Status: Accepted (2026-09-13)

Der Technikerflow ist **kein eigenes Projekt**, sondern ein mobil optimierter
Bereich **innerhalb von `erp.dormed.de`**. Ein Login, ein Deployment, kein
zusätzlicher Container, keine zweite Authentifizierungsschicht. Der zeitweise
angelegte Ordner `app/` im Wurzelverzeichnis entfällt damit.

**Begründung:** Die beiden Fähigkeiten, die den Proof of Concept ausmachen, brauchen
kein natives Projekt. `getUserMedia` mit einem Overlay über dem Video-Element
funktioniert auf iOS Safari und Android Chrome; Unterschriftenerfassung per
Signature-Pad ohnehin. Ein natives oder Capacitor-Projekt würde einen zweiten
Technologie-Stack und eine Build-/Signier-/Store-Kette einführen, ohne für diesen
Umfang etwas beizutragen.

### MVP-Umfang — höchste Priorität

1. **Regulärer Login mit E-Mail und Passwort** (Interim-Auth aus D-032; SSO bleibt
   ein späterer Slice, D-029).
2. **Nur der Technikerflow**, in abgespeckter Form — als Showcase und als Nachweis,
   dass die folgenden zwei Dinge tragen:
   - **Unterschriftenerfassung** (→ `MaintenanceReport.signature_image`, D-045;
     Ablage als `bytea` in der Datenbank, ADR-028).
   - **Pflichtfotos mit Aufnahmeanleitung**: die geöffnete Kamera zeigt als Overlay
     eine Überschrift, **was genau** zu fotografieren ist. Der Schritt ist erst
     abgeschlossen, wenn das Foto vorliegt. Ablage auf dem gemounteten Volume
     (ADR-029).

### Online-only

**Der MVP braucht keine Offline-Fähigkeit.** Das bestätigt **D-041**, das den
Offline-Wartungsbericht bereits bewusst als späteren Slice markiert hat. Erfassung
und Upload laufen online; ein Verbindungsabriss ist im MVP kein behandelter Fall.

> **Bekannte Einschränkung, bewusst getragen:** Techniker arbeiten in Praxisräumen
> mit schlechtem Empfang. Für den Showcase ist das unkritisch, für den Produktivgang
> nicht — dann braucht es mindestens lokales Zwischenspeichern von Formularstand und
> aufgenommenen Fotos, bis der Upload durchgeht. Das ist beim Ausbau des
> Technikerflows zu entscheiden, nicht jetzt.

## ADR-033 — Echter Monolith: eine Laravel-Anwendung, kein Core-Package, keine App-Trennung

Status: Accepted (2026-09-14) · **supersedes ADR-011, ADR-012, ADR-013, ADR-026** ·
**reduziert ADR-030**

ERP, Portal und Shop sind **eine** Laravel-Anwendung mit **einer** Datenbank. Es gibt
kein `core`-Composer-Package, keine vier App-Ordner und keinen geteilten Datenbankzugriff
mehrerer Anwendungen. Die Domains sind Routing, keine Deployment-Einheiten.

**Begründung — der bisherige Schnitt war ein Integration-Database-Pattern.** Vier Apps auf
einer geteilten Postgres-Instanz mit geteiltem Code-Package erzeugen die Kosten der
Verteilung ohne deren Nutzen. ADR-026 hat das bereits eingestanden und musste die
Unabhängigkeit per Beschluss wieder abschaffen („eine Compose, ein Deploy, eine `.env`"),
um Schema-Integrität zu retten. Übrig blieben vier `vendor/`-Bäume, vier `composer.json`,
vier Laravel-Upgrade-Pfade — und trotzdem genau ein Deploy-Artefakt.

**Was die Treiber wirklich verlangt haben:**

| Treiber | Braucht dafür getrennte Apps? |
| --- | --- |
| Physische Domain-Trennung | Nein — Reverse-Proxy-Konfiguration |
| Verschiedene Zugriffspunkte | Nein — Route-Groups, Guards, Middleware |
| Scalability | Nein — N Replicas desselben Images; eine Aufteilung *kostet* Zyklen |
| Datenintegrität | **Nein, im Gegenteil** — eine Transaktion über HTTP hinweg gibt es nicht |

Der Monolith ist die sparsamste Form pro Request. Aufteilen hebt keine Obergrenze an; es
erlaubt getrennten Teams getrennte Releases und lässt eine heiße Komponente allein
skalieren. Beides trifft bei einem Entwickler und dieser Lastklasse nicht zu.

**Single Source of Truth ist die tragende Anforderung** (Nutzer, explizit): der Shop läuft
nicht getrennt. Damit ist die in der Architekturdiskussion offene Frage entschieden —
Shop, ERP und Portal sind **ein** Bounded Context, nicht drei.

**Kapazitätsnachweis** (Grundlage der Entscheidung, Zahlen vom Nutzer):

```text
20 Mitarbeiter × 0,125 req/s                       =   2,5 req/s
1600 Portalkunden, 5 % gleichzeitig (pessimistisch) =   4   req/s
Shop (B2B, überwiegend Cache-Hits)                  =   5   req/s
                                                      ------
realistische Spitze                                   ~10–20 req/s

Kapazität: 6 Kerne × (1000 ms / 70 ms)              = ~85 req/s Volllast
                                                    = ~40 req/s komfortabel
```

Datenbestand: 50.000 Legacy-Adressen (Firmen + Ansprechpartner zusammen), Termine und
Tickets in ähnlicher Größenordnung. Im Zielschema aufgeteilt grob 200.000 Zeilen; gesamt
unter 1 Mio Zeilen, 1–3 GB mit Indizes. Das passt vollständig in den Postgres-Cache.
**Faktor 15–30 Reserve auf die realistische Spitze** — die Obergrenze ist auf absehbare
Zeit keine Randbedingung.

**Konsequenzen:**

- `core/` und `erp.dormed.de/` werden gelöscht (beide am 2026-09-13 erzeugt, kein Verlust).
- Fachliche Modulgrenzen aus ADR-013 wandern nach `app/Modules/<Modul>/`, in **dieselbe**
  Anwendung. `ModuleBoundariesTest` bleibt gültig und erzwingt sie dort weiter.
- Die spätere Mobile-App ist eine `/api/v1`-Route-Group in derselben Anwendung, kein
  eigener Dienst. Ein `api.dormed.de` als separates Deployment wird **nicht** gebaut:
  es kostet Serialisierung, Service-Auth und Netzwerk-Fehlermodi — und opfert genau die
  Transaktionsintegrität, die der Haupttreiber war. Herauslösen ist später ein Tagesprojekt;
  ein verteiltes System wieder zusammenzuziehen ist es nicht.
- Compose reduziert sich auf: ein App-Service (bei Bedarf N Replicas) + Queue-Worker.
  Postgres und MinIO bleiben eigene Coolify-Ressourcen (ADR-026-Begründung zu Backups gilt
  unverändert weiter, nur ohne den Rest von ADR-026).
- **Zentrale Zugangsdaten sind damit kein Problem mehr:** eine Anwendung, eine `.env`,
  eine Stelle, an der Postgres- und MinIO-Credentials referenziert werden. Die Frage nach
  einem geteilten Env über vier Apps hinweg entfällt ersatzlos.

> **Der echte Preis dieser Entscheidung** ist nicht das Hosting, sondern die
> Schema-Zusammenführung: die 28 Migrations und `Customer`/`Order`/`Product`/`Address` des
> Alt-Shops müssen mit `Company`/`Person`/`Address` und dem Inventory-Artikelstamm
> (`article_id`, D-121) zusammengeführt werden. Das ist die Projektarbeit und das
> Projektrisiko.

## ADR-034 — nginx + php-fpm statt Laravel Octane

Status: Accepted (2026-09-14) · **revidiert ADR-022**

Die Anwendung läuft hinter **klassischem nginx + php-fpm** mit OPcache. **Laravel Octane
wird nicht eingesetzt.**

**Begründung — Octane löst ein Problem, das diese Anwendung nicht hat.** Octane spart den
Framework-Bootstrap von ~20 ms pro Request. Bei der Last aus ADR-033 (~2,5 req/s aus dem
ERP, Spitze 10–20 req/s) liegt die Maschine im einstelligen Prozentbereich Auslastung. Der
Gewinn ist nicht messbar, die Kosten sind es:

- **State-Leaks.** Langlebige Worker lassen statische Properties, Singletons mit
  Request-Zustand und Container-Bindings zwischen Requests überleben. Eine Fehlerklasse,
  die bei php-fpm nicht existiert.
- **Feste Worker-Zahl.** Bei Octane belegt ein langsamer Request dauerhaft einen von N
  Slots — bei dieser Domäne konkret: PDF-Erzeugung für Wartungs- und Messprotokolle
  (D-038/D-044), Rechnungsläufe, DATEV-Export, DHL-/PayPal-Aufrufe. php-fpm startet
  stattdessen dynamisch Kinder nach und ist deutlich gutmütiger.
- **Betrieb.** Kein `octane:reload` im Deploy, kein Worker-Lifecycle, keine Packages, die
  sich anders verhalten als ihre Dokumentation beschreibt.

**Nicht der Grund:** der Monolith. Octane ist orthogonal zur Codestruktur — ein Monolith
kann auf Octane laufen, verteilte Dienste auf php-fpm. Die Entscheidung fällt allein über
Last und Betriebskosten.

**Nachrüstbar.** Octane ist additiv (`octane:install`, Prozessmodell umstellen). Wenn die
Last je in die Nähe von einigen hundert req/s kommt, ist der Schritt offen — dann aber mit
einer Auditierung auf State-Leaks, nicht nebenbei.

> **Randnotiz:** FrankenPHP ist auch **ohne** Octane-Worker-Mode nutzbar (klassischer
> Modus, ein Binary, HTTP/2, Auto-HTTPS, Static-File-Serving). Das wäre die Variante, die
> von ADR-022 am meisten erhält. Entschieden wurde bewusst der langweiligere Weg:
> nginx + php-fpm ist der Pfad, für den jede Anleitung, jedes Monitoring-Rezept und jede
> Fehlermeldung im Netz ohne Übersetzung passt.

**Queue-Regel, unabhängig vom App-Server:** Alles, was nicht zuverlässig unter ~200 ms
bleibt, geht in die Queue. Das ist keine Optimierung, sondern eine Designbedingung —
sie ist der Grund, warum die feste Worker-Zahl oben überhaupt zum Problem werden konnte.

## ADR-035 — Altsystem als Referenz unter `.legacy/`, nicht als Teil der Anwendung

Status: Accepted (2026-09-14) · **ergänzt ADR-018, reduziert ADR-030**

Die beiden real existierenden Alt-Anwendungen bleiben im Repository liegen, aber
**außerhalb** des Monolithen:

```text
.legacy/dormed.de/        bestehender Blade-Auftritt
.legacy/shop.dormed.de/   bestehende Inertia/Svelte-Shop-App
```

**Zweck:** fachliche Referenz beim Neubau. Der Alt-Shop ist die einzige belastbare
Ist-Quelle für Katalog-, Bestell- und Checkout-Verhalten — für Inventory existiert nach
D-108 gar kein Export. Solange am Alt-Shop noch etwas geändert wird, lassen sich die
Änderungen in diesen Ordner ziehen und beim Nachbau im Monolithen berücksichtigen.

**Bewusst `.legacy/` statt eines Punkt-Präfixes je Ordner** (`.dormed.de/`,
`.shop.dormed.de/`): ein Verzeichnis, das einmal ausgeschlossen wird, statt zwei — und
zwar in Pint, PHPStan, PHPUnit, CI, Docker-Build-Context und den `.ai/rules`-Globs. Der
Punkt vorne erfüllt den Zweck (hebt sich ab, fällt aus Standard-Globs), nur eben an einer
Stelle. Der Nutzungszweck „Änderungen hineinziehen" bleibt identisch.

**Konsequenzen:**

- Kein Code aus `.legacy/` wird eingebunden, autoloaded, getestet oder deployt. Die Ordner
  sind Lesestoff, keine Abhängigkeit.
- `.dockerignore` muss `.legacy/` ausschließen, sonst wandern zwei `vendor/`- und
  `node_modules/`-Bäume in jeden Build-Context.
- Die Globs in `.ai/rules/index.md` verweisen auf `*dormed.de/...` und würden auf
  `.legacy/shop.dormed.de/**` weiterhin zutreffen. Sie sind beim Umbau anzupassen.
- ADR-018 (Anwendungscode wird verworfen, Neuaufbau) bleibt gültig — `.legacy/` ist
  ausdrücklich **keine** Migration von Code, sondern eine Ablage von Vorlagen.

## ADR-036 — RLS-Architektur: zwei Applikationsrollen und eine Session-Variable, kein Benutzer je Kunde

Status: Accepted (2026-09-14) · **konkretisiert ADR-007**

ADR-007 hat RLS im Grundsatz beschlossen und die Ausarbeitung offen gelassen. Hier ist sie.

### Die Fehlannahme zuerst

RLS-Policies hängen **nicht** an Datenbankbenutzern. Sie können auf
**Session-Konfigurationsparameter** zugreifen, die die Anwendung pro Request setzt.
**Es braucht also keinen Postgres-Benutzer je Kunde** — 1600 Kunden bedeuten nicht 1600
Rollen, sondern eine Rolle und eine Variable.

### Drei Rollen, nicht mehr

| Rolle | Zweck | Rechte | Eigentümer |
| --- | --- | --- | --- |
| `dormed_owner` | führt ausschließlich Migrations aus | alle | **ja** |
| `dormed_staff` | ERP-Requests (Mitarbeiter) | SELECT/INSERT/UPDATE/DELETE | nein |
| `dormed_customer` | Portal- und Shop-Requests (eingeloggte Kunden) | SELECT/INSERT/UPDATE/DELETE | nein |
| `dormed_public` | anonyme Requests auf `dormed.de` und im Shop-Katalog | **SELECT** auf Veröffentlichtes, **INSERT** nur auf Anfragen | nein |

**`dormed_public` ist die schärfste der vier Grenzen** und der Grund, warum sich der
Aufwand schon vor dem Portal lohnt. Die öffentliche Website zeigt nach ADR-038 Produkte
und Produktbilder aus derselben Datenbank, in der Rechnungen, Servicefälle und
Kundendaten liegen. Diese Rolle bekommt auf all das **keine Grants** — nicht „darf es
nicht", sondern *kann* es nicht. Ein Fehler im öffentlichen Blade-Template, eine
vergessene Scope-Bedingung oder eine SQL-Injection im anonymen Bereich läuft damit gegen
eine Verbindung, die Kundendaten schlicht nicht sieht.

Ihre einzigen Schreibrechte sind `INSERT` auf die Zieltabelle des Kontaktformulars.
Kein `UPDATE`, kein `DELETE`, nirgends.

**Warum die Trennung Eigentümer/Anwendung nicht verhandelbar ist:** Postgres wendet
RLS-Policies auf den Tabelleneigentümer **nicht** an. Verbindet Laravel sich als die
Rolle, die die Tabellen angelegt hat — der Standardfall nach `php artisan migrate` —
ist RLS **still und ohne Fehlermeldung wirkungslos**. Ebenso bei `SUPERUSER` oder
`BYPASSRLS`; beide Attribute dürfen die Anwendungsrollen nie tragen.

### Policy-Form (fail-closed)

```sql
ALTER TABLE invoices ENABLE ROW LEVEL SECURITY;

CREATE POLICY staff_full_access ON invoices
  FOR ALL TO dormed_staff
  USING (true);

CREATE POLICY customer_own_company ON invoices
  FOR ALL TO dormed_customer
  USING (company_id = NULLIF(current_setting('app.company_id', true), '')::bigint);
```

> **`NULLIF` ist Pflicht, nicht Kosmetik** (beim Bau des Gerüsts festgestellt,
> 2026-09-14). Ohne es liefert eine gesetzte, aber **leere** Variable — was eine
> Middleware ohne Firmenkontext schreibt — den Ausdruck `''::bigint`, und der wirft
> einen Fehler, statt null Zeilen zu liefern. Aus fail-closed würde fail-loud:
> auffällig, aber nicht das, was hier beschrieben ist.

Für den öffentlichen Bereich tritt die Policy **hinter** die Grants zurück — auf
`invoices` hat `dormed_public` gar keine erst. Wo sie etwas sehen darf, begrenzt die
Policy auf Veröffentlichtes:

```sql
CREATE POLICY public_published_only ON products
  FOR SELECT TO dormed_public
  USING (is_available AND published_at IS NOT NULL);
```

Der entscheidende Punkt ist das Fehlverhalten: `current_setting(..., true)` liefert `NULL`,
wenn die Variable nicht gesetzt ist. `company_id = NULL` ist niemals wahr — ein Kunde,
dessen Kontext nicht gesetzt wurde, sieht **null Zeilen**, nicht alle. Vergisst die
Middleware ihre Aufgabe, bricht das Portal; es leakt nicht. Das ist die richtige
Fehlerrichtung und der Grund für diese Form.

### Anbindung in Laravel

Drei Verbindungen in `config/database.php` auf dieselbe Datenbank, mit unterschiedlichen
Credentials — die Verbindung folgt dem Zugriffspunkt, nicht dem Datensatz:

| Verbindung | Rolle | Wer |
| --- | --- | --- |
| `pgsql` | `dormed_staff` | ERP-Routen |
| `pgsql_customer` | `dormed_customer` | Portal- und Shop-Routen **nach** Login |
| `pgsql_public` | `dormed_public` | `dormed.de` und anonymer Shop-Katalog |

Die Route-Groups schalten per Middleware auf ihre Verbindung; die Kundenverbindung setzt
zusätzlich `app.company_id` aus der Session. Die ERP-Routen setzen die Variable nie, die
öffentlichen Routen brauchen sie nicht.

> **Die Umschaltung gehört an die Route-Group, nicht ans Model.** Ein `$connection` auf
> einem Eloquent-Model würde die Grenze an den Datensatz hängen statt an den Zugriffspunkt
> — dasselbe `Product` wird aber je nach Aufrufer über eine andere Rolle gelesen. Genau
> das ist der Zweck der Konstruktion.

**Jeder Zugriffspunkt benennt seine Rolle ausdrücklich — auch das ERP.** `UseStaffConnection`
sieht wie ein No-Op aus, weil `pgsql` ohnehin der Standard ist. Beim Bau des Gerüsts hat sich
gezeigt, warum es trotzdem gebraucht wird: „Standard" ist nichts, worauf eine Sicherheitsgrenze
sich verlassen darf. In der Testumgebung, in einem Artisan-Command und in einem Queue-Job ist
er jeweils ein anderer. Ein Zugriffspunkt, der seine Rolle erbt statt sie zu nennen, wechselt
sie lautlos mit dem Kontext.

**Anforderung an die Umsetzung:** Die Variable muss bei **jedem neuen
Verbindungsaufbau** gesetzt werden, auch bei einem Reconnect mitten im Request — nicht
einmalig im Middleware-Aufruf. Sonst arbeitet eine wiederhergestellte Verbindung ohne
Kontext weiter (was nach obiger Policy zu leeren Ergebnissen führt, also auffällt, aber
als Fehlerbild schwer zu lesen ist).

> **Laravel hat dafür keinen Hook** (geprüft an Laravel 13, 2026-09-14): ein
> `afterConnecting` existiert nicht. Was es gibt, ist `Connection::beforeExecuting()`.
> Die Umsetzung vergleicht darin die aktuelle PDO-Instanz mit der zuletzt
> konfigurierten und setzt die Variable nur beim Wechsel neu — ein Reconnect erzeugt
> eine neue Instanz und wird so erkannt.
>
> **Darin zwingend `$pdo->exec()`, nicht `$connection->unprepared()`:** `unprepared()`
> läuft durch `Connection::run()` und würde genau diesen Callback erneut auslösen.
> Endlosrekursion.

### Session, Cache und Queue gehören an eine feste Verbindung

Beim Bau des Gerüsts aufgefallen und leicht zu übersehen: `SESSION_DRIVER=database`,
`CACHE_STORE=database` und `QUEUE_CONNECTION=database` laufen über die
**Standardverbindung** — und würden der Umschaltung mitfolgen. Ein Request auf
`dormed.de` wollte seine Session dann über `dormed_public` lesen, das darauf keine
Grants hat.

```dotenv
SESSION_CONNECTION=pgsql
DB_CACHE_CONNECTION=pgsql
DB_CACHE_LOCK_CONNECTION=pgsql
DB_QUEUE_CONNECTION=pgsql
```

Sessions, Cache und Jobs sind **Infrastruktur, keine Fachdaten**. Sie kennen keinen
Zugriffspunkt und dürfen keinen kennen.

> **Unverträglich mit PgBouncer im Transaction-Mode.** Eine sitzungsweite Variable
> überlebt dort den Verbindungswechsel nicht und kann im schlimmsten Fall an den nächsten
> Request geraten. Bei der Last aus ADR-033 wird kein Pooler gebraucht; wird je einer
> eingeführt, muss die Variable auf `set_config(..., true)` innerhalb einer expliziten
> Transaktion umgestellt werden. **Diese Zeile ist der Grund, warum ein Pooler hier keine
> beiläufige Betriebsentscheidung ist.**

### Grants — die Stelle, die sonst jede Migration bricht

Die Anwendungsrollen besitzen die Tabellen nicht und brauchen deshalb explizite Rechte.
Ohne `ALTER DEFAULT PRIVILEGES` gilt jede Grant nur für die zum Zeitpunkt der Vergabe
existierenden Tabellen — die nächste Migration legt eine Tabelle an, auf die die
Anwendung keinen Zugriff hat, und das fällt erst zur Laufzeit auf:

```sql
ALTER DEFAULT PRIVILEGES FOR ROLE dormed_owner IN SCHEMA public
  GRANT SELECT, INSERT, UPDATE, DELETE ON TABLES TO dormed_staff, dormed_customer;
ALTER DEFAULT PRIVILEGES FOR ROLE dormed_owner IN SCHEMA public
  GRANT USAGE, SELECT ON SEQUENCES TO dormed_staff, dormed_customer;
```

> **Postgres gibt jeder neuen Datenbank CONNECT an die Pseudo-Rolle `PUBLIC`** — sichtbar
> als führendes `=Tc/owner` in `pg_database.datacl`, ebenso `USAGE` auf dem Schema
> `public` in `pg_namespace.nspacl`. Ein `GRANT CONNECT` an die eigenen Rollen ist ohne
> vorherigen Widerruf also wirkungslose Kosmetik: verbinden darf ohnehin jeder, der
> irgendein Login hat. Die Migration widerruft deshalb zuerst:
>
> ```sql
> REVOKE CONNECT ON DATABASE "dormed" FROM PUBLIC;
> REVOKE USAGE ON SCHEMA public FROM PUBLIC;
> ```
>
> **Nicht zu verwechseln:** `PUBLIC` ist Postgres' Jedermann-Pseudorolle, `dormed_public`
> ist unsere anonyme Anwendungsrolle. Die Namensähnlichkeit ist unglücklich und die
> Bedeutung entgegengesetzt.

**`dormed_public` steht bewusst nicht in diesem Block.** Für sie gibt es keine
Default-Privileges: jede Tabelle, die sie lesen darf, wird **einzeln und bewusst**
freigegeben. Eine neue Migration gibt dieser Rolle damit standardmäßig **nichts** — das
ist die gewünschte Richtung. Der Preis ist eine Zeile pro öffentlich sichtbarer Tabelle,
und dieser Preis ist der eigentliche Schutz.

### RLS ersetzt keine Autorisierung

Die Permission-Matrix der fünf Abteilungen (D-125/D-136/D-137) bleibt **vollständig** in
Laravel-Policies und Gates. RLS bildet sie **nicht** ab: die Einschränkung eines
`sales`-Benutzers ist „kein Zugriff auf `billing.*`" — eine Tabellen- und Aktionsfrage,
keine Zeilenfrage. Das in SQL-Policies zu kodieren wäre schwer zu testen und schwer zu
ändern.

RLS ist hier ausschließlich **Zeileneigentum für Kunden** und wirkt als Netz unter der
Anwendungslogik: es fängt die vergessene `where`-Klausel, die rohe Query und den
Reporting-Pfad ab. Genau dort, wo ein Fehler teuer ist — Kunde A sieht Rechnungen von
Kunde B ist ein meldepflichtiger DSGVO-Vorfall, kein Bug-Ticket.

### Was jetzt zu tun ist und was später

**Jetzt, weil kostenlos beim Aufsetzen und teuer als Nachrüstung:**

1. Die drei Rollen anlegen; der Migrate-Schritt läuft als `dormed_owner`, die Anwendung
   nie.
2. `ALTER DEFAULT PRIVILEGES` einrichten.
3. Auf jeder Tabelle, die ein Kunde je erreichen kann, die Eigentümerspalte von Anfang an
   mitführen.

**Später, beim Bau des Portals:** `ENABLE ROW LEVEL SECURITY` und die Policies. Vorher
gibt es keine Kundenverbindung, die etwas anfassen könnte.

Punkt 1 ist der tragende Teil: er kostet heute nichts und erspart die Nachrüstung, bei der
sonst jede bestehende Query auditiert werden müsste.

### Testauflage

RLS-Fehler sind unsichtbar, bis sie es nicht mehr sind. Es braucht einen Test, der über
die **echte Kundenverbindung** prüft, dass Kunde A die Zeilen von Kunde B nicht lesen kann
— nicht über eine Laravel-Policy, die man dabei umgeht. Dieser Test ist der wertvollste
im ganzen Projekt.

**Geklärt durch ADR-037:** Shop-Kunde und CRM-Kontakt sind **dieselbe** Entität. Das
Prädikat zeigt damit auf die Firma des eingeloggten Kontakts. Die dort notierte
Rückfrage — ein Kontakt an mehreren Firmen — ist die einzige verbliebene Unschärfe in
der Policy-Formulierung.

## ADR-037 — Kundenidentität ist der CRM-Kontakt; Zugänge werden aus dem Kontakt heraus verwaltet

Status: Accepted (2026-09-14) · **schließt die offene Frage aus ADR-033 und ADR-036**

Ein **Shop-Besteller, ein Portal-Nutzer und ein CRM-Ansprechpartner sind dieselbe
Entität.** Es gibt keinen separaten „Shop-Kunden" neben dem CRM-Kontakt, der später
abgeglichen werden müsste.

**Begründung (Nutzer, am Arbeitsablauf):** Ein Kunde ruft an und kennt weder seine
E-Mail-Adresse noch sein Passwort. Der Mitarbeiter muss den Zugang **direkt aus dem
Kontaktdatensatz heraus** zurücksetzen können — ohne Systemwechsel, ohne Suche in einer
zweiten Benutzerverwaltung, ohne die Frage, welcher der beiden Datensätze der richtige
ist. Das funktioniert nur, wenn der Kontakt der Zugang ist.

Diese Anforderung ist der eigentliche Grund für die SOT-Entscheidung aus ADR-033. Zwei
getrennte Systeme mit Abgleich hätten hier genau die Reibung erzeugt, die vermieden
werden soll.

**Konsequenzen:**

- Die Kontaktansicht im ERP bekommt einen Bereich für den Zugang: Status (eingeladen /
  aktiv / gesperrt), Passwort zurücksetzen, Zugang sperren. Das ist eine
  **Mitarbeiteraktion auf einem Kundendatensatz** und braucht eine eigene Ability im
  Vokabular aus D-136 — Zugänge verwalten ist nicht dasselbe wie Kontakt bearbeiten,
  und nicht jede Abteilung soll es dürfen.
- **RLS (ADR-036):** `app.company_id` leitet sich aus der Firma des eingeloggten Kontakts
  ab. Damit steht das Policy-Prädikat.
- Mitarbeiter und Kunden bleiben **getrennte Anmeldewege** (ADR-016 gilt insoweit
  weiter): ein Kunde meldet sich nie im ERP an, ein Mitarbeiter nie im Portal. Dass beide
  Identitäten fachlich an Personen hängen, ändert daran nichts.

> **Offen — gehört in die Schema-Zusammenführung, nicht hierher:**
>
> 1. ~~**Tabellenform.**~~ **Entschieden in ADR-042:** getrennte Tabellen, Models und
>    Guards. `users.role_id` bleibt `NOT NULL`, D-124 unverändert.
> 2. **Ein Kontakt an mehreren Firmen.** `company_contacts` ist eine Zuordnungstabelle.
>    Ist eine Person Ansprechpartner bei zwei Firmen, ist `app.company_id` nicht eindeutig
>    — dann braucht die Sitzung eine aktive Firma mit Umschaltung, oder die Policy muss
>    auf eine Menge prüfen. Erst zu klären, wenn der Fall real vorkommt; die Policy ist
>    dann eine Zeile, die Sitzungsführung nicht.

## ADR-038 — `dormed.de` ist Teil des Monolithen; Produktdaten kommen aus der einen Datenbank

Status: Accepted (2026-09-14) · **supersedes ADR-017**, ergänzt ADR-033

Die öffentliche Website ist **keine** eigenständige Anwendung, sondern eine Domain-Route
im Monolithen — wie ERP, Portal und Shop.

**Begründung:** Die Produkte und Produktbilder auf der Website sollen aus derselben
Datenbank kommen wie Katalog, Warenwirtschaft und Shop. Ein Produkt wird einmal gepflegt
und erscheint auf der Website, im Shop und im ERP. Damit hat die Website eine echte
Datenanbindung und fällt nicht mehr unter „null Domänendaten" — die Begründung, die sie
als separates Deployment vertretbar gemacht hätte, trägt nicht mehr.

Der Nutzer nennt das Ergebnis **„integrated management software"**: alle vier
Zugriffspunkte auf einer Wahrheit, kein Export, kein Sync, kein zweiter Pflegeort.

**Konsequenzen:**

- Vier Hostnames, eine Anwendung, ein Deployment. Subdomain-Routing ist damit wieder das
  Mittel der Wahl — ADR-003 gilt der Sache nach erneut (siehe ADR-012).
- Der anonyme Zugriff der Website erfolgt über `dormed_public` (ADR-036). Das ist keine
  Formalie: die Website liest jetzt aus einer Datenbank, in der Rechnungen und
  Servicefälle liegen, und darf genau zwei Dinge — Veröffentlichtes lesen und Anfragen
  anlegen.
- Produktbilder brauchen einen öffentlich auslieferbaren Ablageort. Der öffentliche
  Bucket aus ADR-028 ist dafür gedacht und passt: Marketing-Assets, keine Kundendaten.

> **Übergangszustand, bewusst getragen (Stand 2026-09-14).** Die 28 Prospekt-PDFs liegen
> **doppelt** im Repository, byte-identisch, je 39 MB:
>
> | Ort | Rolle |
> | --- | --- |
> | `public/assets/pdf/` | wird **heute ausgeliefert** — die Blade-Seiten verlinken `/assets/pdf/…` |
> | `database/seeders/pdf/` | Quelle für den Seed in den Bucket |
>
> Das ist keine Schlamperei und **darf nicht „aufgeräumt" werden**, solange die Views
> noch feste Pfade verlinken. Wer `public/assets/pdf/` löscht, nimmt der laufenden
> Website 28 Downloads.
>
> **Aufgelöst wird es mit dem Umbau auf Objektspeicher:** Die PDFs wandern in den
> Bucket, ihre Adresse kommt aus dem Artikeldatensatz statt aus dem Markup, und erst
> **danach** entfällt `public/assets/pdf/`. Dieselbe Bewegung wie bei den Produktdaten
> insgesamt — es ist der eigentliche Inhalt dieser ADR.
- Ein Produkt braucht ein **Veröffentlichungsmerkmal**, das Website-Sichtbarkeit von
  Shop-Verfügbarkeit und ERP-Katalogpflege trennt. Nicht jeder Artikel im Warenwirtschafts-
  Stamm gehört auf die Website. Das ist beim Inventory-Schema zu berücksichtigen
  (`INVENTORY.md`).

## ADR-039 — Frontend: Inertia + Svelte für ERP, Portal und Shop; Blade für `dormed.de`

Status: Accepted (2026-09-14)

Zwei Frontend-Stacks in einer Anwendung, getrennt entlang der Domain — bewusst, nicht
gewachsen:

| Zugriffspunkt | Stack | Warum |
| --- | --- | --- |
| ERP, Portal, Shop | **Inertia + Svelte** | Anwendungsflächen: Zustand, Formulare, Listen, Interaktion |
| `dormed.de` | **Plain Blade + Blade-Komponenten** | Inhaltsseite: SEO, Erstladezeit, keine Hydration nötig |

**Begründung:** Die Website ist das Gegenteil einer Anwendung — sie wird von Suchmaschinen
gelesen, von Erstbesuchern geöffnet und zeigt überwiegend statischen Inhalt mit
Produktdaten. Server-gerendertes HTML ohne JavaScript-Runtime ist dafür die bessere
Antwort, und es ist zugleich das, was die bestehende Seite heute schon tut — der Stack
wird also nicht gewechselt, sondern beibehalten.

Für ERP, Portal und Shop gilt das Umgekehrte: Technikerflow (ADR-032), Cockpit (D-126),
Checkout und Stammdatenmasken sind Anwendungsflächen. Der Alt-Shop läuft bereits auf
Inertia/Svelte — dieser Teil wird beim Neubau fortgeschrieben, nicht ersetzt.

**Dass beides koexistiert, ist kein Kompromiss, sondern der Zweck.** Inertia ist pro
Route wählbar: eine Route liefert eine Inertia-Antwort, die nächste eine Blade-View,
beide aus denselben Controllern und Models. Es gibt keinen Bruch, der überbrückt werden
müsste.

> **Grenze, damit es zwei Stacks bleiben und nicht drei:** Keine Inertia-Seiten unter
> `dormed.de`, keine Blade-Vollseiten im ERP. Blade-**Komponenten** bleiben überall
> zulässig — Inertia rendert seine Wurzel-View ohnehin durch Blade.

## ADR-040 — `.legacy/` bleibt ungetrackt; die Altprojekte behalten ihre eigene Versionierung

Status: Accepted (2026-09-14) · **präzisiert ADR-035**

`.legacy/` steht in `.gitignore` des Monorepos. Die Altanwendungen bleiben **eigenständige
Git-Projekte**, die separat geklont und gepullt werden.

**Begründung:** Sie sind Referenz, kein Quellcode dieses Repositorys. Würden sie
mitgetrackt, entstünde ein zweiter Wahrheitsort für Code, der anderswo weiterentwickelt
wird — mit Merge-Konflikten gegen ein Projekt, das gar nicht hierher gehört. Ungetrackt
bleibt die Beziehung einseitig und richtig: hereinziehen, nachlesen, nie zurückschreiben.

**Bestand bei der Entscheidung:**

| Projekt | Eigenes Repository | War im Monorepo getrackt |
| --- | --- | --- |
| `shop.dormed.de` | ja, eigenes `.git` im Ordner | nein |
| `dormed.de` | ja, auf GitHub | **ja, 556 Dateien** — ausgetragen |
| `my.dormed.de` | ja, noch nicht geklont | nein |

**Zum Austragen von `dormed.de`** wurde `git rm -r --cached` verwendet, nicht `git rm`:
die Dateien bleiben unangetastet auf der Platte und verlassen nur den Index. Der zuletzt
getrackte Stand bleibt zusätzlich in der Historie dieses Repositorys abrufbar. Damit ist
der Schritt in beide Richtungen folgenlos — der maßgebliche Stand liegt ohnehin im
GitHub-Repository der Website.

**Konsequenzen:**

- `.dockerignore` schließt `.legacy/` aus, sonst wandern mehrere `vendor/`- und
  `node_modules/`-Bäume in jeden Build-Context.
- Kein Werkzeug dieses Repositorys greift auf `.legacy/` zu: nicht Pint, nicht PHPStan,
  nicht PHPUnit, nicht die CI, nicht die `.ai/rules`-Globs.
- Ein frisch geklontes Monorepo hat kein `.legacy/`. Das ist beabsichtigt — wer die
  Referenz braucht, klont sie dazu.

> **Nachzutragen:** `README.md` soll die drei Repository-URLs und die Klon-Ziele unter
> `.legacy/` nennen, damit die Referenz auffindbar bleibt. Die URLs liegen noch nicht vor.

## ADR-041 — Dev und Prod sind bewusst verschieden: flüchtiger Compose-Stack lokal, Dockerfile in Produktion

Status: Accepted (2026-09-14) · ersetzt den Dev-Teil von ADR-026

### Dev: alles flüchtig, ein Befehl

`docker-compose.yaml` startet Anwendung, Vite, PostgreSQL und MinIO. **Postgres und
MinIO liegen auf `tmpfs`** — im RAM, ohne Volume. Ein `down` vergisst alles, ein `up`
baut den Stand aus Migrations und Seedern neu auf.

**Begründung — der Seeder bleibt dadurch lauffähig.** Wenn der einzige Weg zu Daten bei
jedem Start durch den Seeder führt, kann er nicht unbemerkt verrotten. Genau dieser Lauf
wird in Produktion **einmal** gegen die echte Datenbank gefahren, um eine Ausgangsbasis
herzustellen (Nutzer). Ein Dev-Stack mit persistentem Volume würde den Seeder nach dem
ersten Tag nie wieder ausführen — und der Produktivlauf wäre dann der erste echte Test.

Zweiter Effekt: **Datenbank- und Object-Storage-Seed bleiben aufeinander abgestimmt.**
Produktbilder und Prospekte liegen in MinIO, ihre Metadaten in Postgres. Beide zusammen
zu vergessen und beide zusammen neu aufzubauen ist die einzige Form, in der die
Verknüpfung nicht auseinanderlaufen kann.

Dritter Effekt: Zugangsdaten. Weil der Stack flüchtig ist und nur lokal läuft, stehen
alle Werte als Standardwerte in `.env.example`. `cp .env.example .env` genügt — es gibt
nichts zu schützen und nichts von Hand zu verdrahten.

### Dev-Image: Sails Runtime, **ohne** den `sail`-Wrapper

Der App-Container baut aus `./vendor/laravel/sail/runtimes/8.4` — dem Build-Kontext, den
`laravel/sail` als Dev-Abhängigkeit ohnehin mitbringt. Das liefert PHP 8.4 mit allen
Extensions (inkl. `pgsql`, `gd`, `imagick`, `intl`) und Node 24 im selben Image, ohne ein
eigenes Dev-Dockerfile, das gepflegt werden müsste.

**Nicht benutzt wird `./vendor/bin/sail`.** Der Wrapper erwartet seine eigene generierte
Compose-Datei und fügt eine Indirektionsebene hinzu; die hier nötigen Eigenheiten —
`tmpfs` statt Volumes, ein `minio-init`-Schritt, vier Hostnames auf einen Container,
ein eigener `setup`-Einmalschritt — schreiben sich direkt klarer als über die
Sail-Konfiguration. `boost.json` bleibt deshalb bei `"sail": false`.

Das ist die Mitte zwischen „eigenes Dockerfile pflegen" und „Sail übernimmt alles":
**Sails Image, unsere Compose.**

### Tests laufen gegen PostgreSQL, nicht gegen SQLite

Die Datenschicht ist bewusst Postgres-spezifisch: Rollen und RLS (ADR-036), CHECK-Constraints
statt nativer Enums (D-094). Auf SQLite zu testen gäbe falsche Sicherheit — genau die
Zusicherungen, auf die es ankommt, existieren dort nicht.

`phpunit.xml` zeigt deshalb auf eine **eigene Datenbank `dormed_test`**, angelegt von
einer Inline-`config` in der Dev-Compose beim Start. Ohne sie teilen Tests und Dev-Stand dieselbe Datenbank,
und jeder Testlauf räumt den Seed weg — der Browser zeigt danach eine leere Anwendung.

Die Testverbindung ist `pgsql_owner`: Migrations sind Eigentümer-Arbeit. Die Zugriffspunkte
schalten pro Route-Group selbst um, genau wie in Produktion.

> Treiberspezifische Migrationen — die Rollen-Migration ist eine — prüfen den Treiber und
> werden sonst zum No-Op. Sonst bricht jeder Lauf gegen eine andere Datenbank ab.

### Prod: Dockerfile, vorerst keine Compose

Das Produktions-Image wird aus einem Multi-Stage-`Dockerfile` **im Repo-Wurzelverzeichnis**
gebaut (nginx + php-fpm, ADR-034). Es gibt **keinen `docker/`-Ordner**: die nginx-, php-
und supervisor-Konfiguration steht als Heredoc im Dockerfile, das Init-Skript der
Testdatenbank als Inline-`config` in der Dev-Compose. Ein Monolith mit einem Image braucht
kein Verzeichnis dafuer — was das Image ausmacht, steht in der Datei, die es beschreibt. Postgres und S3 sind externe Coolify-Ressourcen; ihre Zugangsdaten
werden **einmal** in die Env der einen Anwendung eingetragen (ADR-033).

**Eine `docker-compose.prod.yaml` gibt es zunächst nicht** (Nutzer). Coolifys
Dockerfile-Build-Pack deployt ein Image direkt, und persistente Mounts — etwa das
Einsatzfoto-Volume aus ADR-029 — lassen sich dort ebenfalls konfigurieren. Für den
aktuellen Umfang genügt das.

> **Der Auslöser, ab dem eine Prod-Compose fällig wird, ist der Queue-Worker — nicht die
> Datenbank.** Ein Container hat genau ein `CMD`; `php artisan queue:work` ist ein
> zweiter langlebiger Prozess und braucht deshalb entweder eine zweite Ressource oder
> einen Supervisor im Container. Sobald die Queue-Regel aus ADR-034 real wird (PDF-Läufe,
> DATEV-Export, DHL/PayPal), ist eine Compose mit `app` + `worker` der saubere Schnitt.
> Bis dahin wäre sie eine Datei, die nichts tut.

Die Reihenfolge ist damit richtig herum: Das Dockerfile ist das Artefakt, das **beide**
Wege brauchen. Eine Compose sind zwanzig Zeilen darüber und jederzeit nachrüstbar — der
umgekehrte Weg nicht.

> **Die Frontend-Stufe braucht PHP** (beim ersten echten Build festgestellt,
> 2026-09-14). Das Wayfinder-Vite-Plugin erzeugt die typisierten Routenhelfer,
> indem es `php artisan wayfinder:generate` aufruft — der Asset-Build hängt damit
> an einer startfähigen PHP-Anwendung. Das übliche Laravel-Rezept „`node:alpine`
> für Assets, `composer` für Vendor" scheitert daran mit `php: not found`, und
> zwar als Build-Abbruch. Die `assets`-Stufe setzt deshalb auf der `vendor`-Stufe
> auf und installiert Node dazu, nicht umgekehrt.
>
> **Warum das zweimal unbemerkt blieb:** `docker build … | tail -4` liefert den
> Exit-Code von `tail`, nicht den des Builds. Zwei „erfolgreiche" Builds waren in
> Wahrheit Fehlschläge. Bei Build-Befehlen `set -o pipefail` oder `PIPESTATUS`
> verwenden — oder gar nicht durch `tail` leiten.

### Was aus ADR-026 gültig bleibt

Postgres und MinIO/S3 sind in Produktion **eigene Coolify-Ressourcen**, nicht Services
der Anwendung: Coolify sichert automatisiert nur seine eigenen Datenbank-Ressourcen, und
die Daten überleben jedes App-Deployment unberührt. Diese Begründung trägt unverändert.

**Migrations** laufen weiterhin als separater Deploy-Schritt, nie im Container-Boot
(ADR-015) — und als `dormed_owner`, nie als die Rolle, mit der die Anwendung arbeitet
(ADR-036).

> **Verbleibendes Betriebsrisiko (unverändert aus ADR-026):** Beim Rollout laufen kurz
> alte Container gegen das bereits migrierte Schema. Additive Migrationen sind
> unkritisch; eine Spalte umzubenennen oder zu löschen reißt sie ab. Destruktive
> Änderungen daher im **Expand/Contract-Muster**: erst hinzufügen, ausrollen, in einem
> späteren Deploy entfernen.

## ADR-042 — Getrennte Tabellen, Models und Guards für Mitarbeiter und Kunden

Status: Accepted (2026-09-14) · **schließt die offene Frage 1 aus ADR-037**

Mitarbeiter und Kunden teilen sich **keine** Tabelle, kein Model und keinen Auth-Guard.

| | Tabelle | Model | Guard | Umfang |
| --- | --- | --- | --- | --- |
| Mitarbeiter | `users` | `User` | `staff` | ~20 |
| Kunde | `customer_accounts` | `CustomerAccount` | `customer` | 1.600+ |

`users.role_id` bleibt damit **unverändert `NOT NULL`** — D-124 muss nicht revidiert
werden, und keine Berechtigungsprüfung muss den Fall „Benutzer ohne Abteilung"
behandeln.

**Begründung — strukturelle statt konventionelle Trennung.** Bei einer gemeinsamen
Tabelle wäre das Einzige, was 1.600 Kundenkonten von 20 Mitarbeiterkonten trennt, ein
`where type = 'staff'`, das niemand vergessen darf. Ein vergessener Filter in einer
Mitarbeiterabfrage liefert dann den gesamten Kundenbestand aus. Bei getrennten Tabellen
**kann** ein Kunde in einer Mitarbeiterabfrage nicht vorkommen.

Das ist dieselbe Logik wie bei `dormed_public` in ADR-036: nicht „darf nicht", sondern
„kann nicht". Die Architektur bleibt darin konsistent.

### Der Kundenzugang hängt am Kontakt, liegt aber nicht in `people`

ADR-037 verlangt, dass ein Mitarbeiter den Zugang **aus dem Kontaktdatensatz heraus**
verwaltet. Das ist eine Beziehung, keine Spaltenfrage: `customer_accounts.person_id`
als FK auf `people`.

Bewusst **nicht** als Spalten direkt auf `people`:

- Von ~50.000 Personen im Adressstamm hat nur ein Bruchteil je einen Zugang. Die
  Auth-Spalten wären auf der Stammdatentabelle überwiegend leer.
- Auth-Belange wachsen — Passkeys, 2FA, fehlgeschlagene Anmeldungen, Sperren,
  `last_login_at`. Die gehören nicht in eine CRM-Stammdatentabelle.
- Passwort-Hashes liegen so in genau einer schmalen Tabelle, die fast nichts abfragt.
- Zugang entziehen heißt eine Zeile löschen, nicht sechs Spalten auf `NULL` setzen.

### Konsequenzen

- **Fortify** (ADR-023) bedient **beide** Guards über eine Installation mit
  `ignoreRoutes()` und Umschaltung je Domain-Group (ADR-043). Geteilt wird die
  Mechanik, nie die Identität.
- **`TracksBlame`** (`created_by`/`updated_by`, D-018) zeigt weiterhin eindeutig auf
  `users` und bedeutet damit immer „welcher **Mitarbeiter**". Kundenaktionen werden,
  wo sie festgehalten werden müssen, über die fachliche Beziehung abgebildet
  (z. B. `orders.person_id`), nicht über die Blame-Spalten.
- **Fremdschlüssel:** Was ein Kunde erzeugt, zeigt auf `people`, nicht auf `users`.
  Diese Unterscheidung ist bei jeder neuen Tabelle zu treffen.
- **RLS (ADR-036):** `app.company_id` leitet sich über
  `customer_accounts → people → company` ab.
- **Ein Mitarbeiter, der im eigenen Shop bestellt, braucht zwei Konten.** Bewusst
  akzeptiert — ein Randfall gegen eine dauerhafte Sicherheitsgrenze.

### Fortify und der zweite Guard — was wirklich geht

Geprüft an **Fortify v1.39** (nicht aus dem Gedächtnis). Die verbreitete Aussage
„Fortify kann nur einen Guard" ist zu grob. Tatsächlich:

**Per Request umschaltbar.** Der zentrale Guard hängt an einem `bind`, keinem
`singleton`:

```php
// FortifyServiceProvider::register()
$this->app->bind(StatefulGuard::class, fn () => Auth::guard(config('fortify.guard', null)));
```

Das wird bei **jeder** Auflösung neu ausgewertet. Eine Middleware, die vor dem
Controller `config(['fortify.guard' => 'customer', 'fortify.passwords' => 'customers'])`
setzt, lenkt Login, Logout und Passwortbestätigung damit auf den Kundenguard um.
`Fortify::ignoreRoutes()` existiert, man registriert Fortifys Controller also selbst —
zweimal, je Domain-Group. Mehrere Passwort-Broker mit **eigener Token-Tabelle** sind in
`config/auth.php` ohnehin nativ vorgesehen, das ist kein Kunstgriff.

**Am Boot festgenagelt — und das ist die einzige echte Grenze.** `configurePasskeys()`
läuft in `register()` und setzt dort statisch:

```php
LaravelPasskeys::useUserModel($model);          // ein Model, prozessweit
config(['passkeys.guard' => config('fortify.guard', 'web')]);
```

**Passkeys und 2FA bedienen deshalb genau einen der beiden Guards.** Eine
Config-Umschaltung zur Laufzeit bewegt sie nicht mehr.

**Für diesen Fall kostet das nichts:** Passkeys und 2FA gehören zu `staff` — 20
Mitarbeiter mit Zugriff auf den gesamten Kundenbestand. Kunden melden sich mit E-Mail
und Passwort an. Die Grenze fällt also genau dort, wo ohnehin geschnitten würde.

> **Revidiert durch ADR-043 (2026-09-14).** Der Absatz unten galt, solange Passkeys
> geplant waren und 2FA nur für Mitarbeiter. Beides hat sich geändert: Passkeys
> entfallen — damit fällt die einzige harte Grenze — und 2FA wird beidseitig gewollt,
> was den teuren Teil verdoppeln würde. **Der Kundenzugang läuft jetzt über dieselbe
> Fortify-Installation.** Die Trennung von Tabellen, Models und Guards bleibt davon
> unberührt. Der Absatz bleibt als Begründungsspur stehen.

**~~Trotzdem wird der Kundenzugang nicht über Fortify gebaut.~~** Zwei Gründe:

1. Die Config-Umschaltung ist **globaler veränderlicher Zustand pro Request**. Alles,
   was *vor* der Middleware aufgelöst wird — eine andere Middleware, ein
   Route-Model-Binding, ein Event-Listener — sieht noch den alten Guard. Das ist die
   Sorte Fehler, die einmal im Jahr auftritt und einen Tag kostet.
2. Ohne Passkeys und 2FA bleibt von Fortify für Kunden nur Standardkram: Login,
   Logout, Passwort-Reset über einen eigenen Broker. Das von Hand zu schreiben ist
   **weniger** Code als die Umschaltmechanik — und es kommt ohne globalen Zustand aus.

Der Mehraufwand dieser ADR ist damit kleiner als zunächst notiert: ein
Login-Controller, ein Logout, ein Passwort-Broker mit eigener Token-Tabelle. Kein
Kampf gegen das Framework.

> **Guard-Benennung:** `staff` und `customer` statt `web` und `customer`. Der
> Standard-Guard heißt bewusst nicht `web`, damit ein `auth()`-Aufruf ohne expliziten
> Guard nicht stillschweigend auf die Mitarbeiterseite fällt. Tabellen- und Guard-Namen
> sind beim Bau anpassbar; die Trennung ist es nicht.

## ADR-043 — Keine Passkeys; optionales TOTP-2FA auf beiden Seiten; Entra-SSO löst den Mitarbeiter-Login ab

Status: Accepted (2026-09-14) · **präzisiert ADR-042 und ADR-023** · konkretisiert D-029/D-032

### Passkeys entfallen ersatzlos

Weder für Mitarbeiter noch für Kunden. `Features::passkeys()` fliegt aus
`config/fortify.php`, mitsamt Migration, Model-Trait und den vier Svelte-Komponenten
aus dem Template (Nutzer).

### TOTP-2FA für beide — immer freiwillig

| | Anmeldung | 2FA |
| --- | --- | --- |
| Mitarbeiter (`staff`) | E-Mail + Passwort — **Interim** (D-032) | TOTP, **optional** |
| Kunde (`customer`) | E-Mail + Passwort | TOTP, **optional** |

**Kein Zwang auf beiden Seiten** (Nutzer). Für Mitarbeiter, weil der Passwort-Login
ohnehin nur die Übergangslösung ist; für Kunden, weil Arztpraxen-Personal sich ein paar
Mal im Jahr anmeldet und Pflicht-2FA dort vor allem verlorene Geräte und Reset-Anrufe
bei denselben 20 Mitarbeitern erzeugt.

**Was Kundenkonten stattdessen schützt:** Rate-Limiting am Login und RLS als Netz
darunter (ADR-036). Ein übernommenes Kundenkonto sieht strukturell nur die eigene Firma
— nicht, weil die Anwendung es verbietet, sondern weil die Datenbankrolle nichts
anderes hergibt.

### Der Mitarbeiter-Login ist eine Übergangslösung

Ziel ist **Microsoft-Entra-SSO** (D-029): `entra_oid`-Upsert, `roles`-Claim aus den
Entra App Roles → `users.role_id` (`IDENTITY_RBAC.md`). E-Mail und Passwort existieren,
**weil sie vorher existieren müssen** — ohne funktionierenden Login lässt sich kein SSO
darauf aufsetzen (Nutzer).

> **Daraus folgt eine Prioritätsregel:** Mitarbeiter-2FA ist **Wegwerfarbeit**. Sobald
> Entra übernimmt, macht Microsoft MFA zentral per Richtlinie und die App-seitige
> 2FA-Flanke ist tot. Sie wird deshalb mitgenommen, **weil Fortify sie geschenkt
> mitbringt** — aber es wird nichts hineininvestiert: keine eigene UI-Politur, keine
> Recovery-Code-Sonderwege, keine Erzwingungslogik.
>
> **Kunden-2FA ist umgekehrt die dauerhafte Variante** — Kunden wandern nie nach Entra.

### Folge für ADR-042: Fortify bedient jetzt doch beide Guards

ADR-042 hatte empfohlen, den Kundenzugang von Hand zu bauen. **Diese Empfehlung ist
hinfällig**, und zwar aus zwei Gründen, die beide aus dieser ADR stammen:

1. **Die einzige harte Grenze war Passkeys.** `LaravelPasskeys::useUserModel()` wird in
   `register()` prozessweit statisch gesetzt und ließ sich pro Request nicht bewegen.
   Ohne Passkeys ist diese Wand weg — alles Übrige hängt an
   `config('fortify.guard')`, das zur Laufzeit ausgewertet wird.
2. **2FA wird jetzt auf beiden Seiten gewollt.** Das ist der aufwendige Teil eines
   Auth-Flusses — TOTP-Secret, Bestätigung, Recovery-Codes, Challenge-Schritt. Ihn
   zweimal von Hand zu bauen wiegt schwerer als die Umschaltmechanik, die ADR-042 als
   zu teuer eingeschätzt hatte.

Der Kundenzugang läuft daher **über dieselbe Fortify-Installation**:
`Fortify::ignoreRoutes()`, Fortifys Controller je Domain-Group eigenständig
registrieren, und eine Middleware setzt pro Group `fortify.guard` und
`fortify.passwords`. Zwei Guards, zwei Provider, zwei Passwort-Broker mit je eigener
Token-Tabelle — alles in `config/auth.php` nativ vorgesehen.

**Unverändert bleibt ADR-042 im Kern:** getrennte Tabellen, getrennte Models, getrennte
Guards. Geteilt wird nur die Mechanik, nie die Identität.

> **Abweichung beim Bau (2026-09-14), bewusst und befristet.** Der Kundenlogin
> ist zunächst **von Hand** gebaut (`Customer\SessionController`,
> `Customer\LoginRequest`), nicht über die geteilte Fortify-Installation.
>
> Grund: Der Ausschlag oben kam von **Kunden-2FA** — der Challenge-Fluss ist der
> teure Teil, den man nicht zweimal schreiben will. Gebaut ist er noch nicht.
> Solange bliebe von Fortify hier nur Standardkram, und die dafür nötige
> Umschaltung von `fortify.guard` per Middleware ist globaler Zustand pro
> Request, den man sich ohne Gegenwert einhandelte.
>
> **Wenn Kunden-2FA kommt, wird umgestellt** — `Customer\SessionController` ist
> dann der Ort, an dem es passiert. Die Entscheidung dieser ADR bleibt gültig,
> nur ihr Zeitpunkt verschiebt sich.

> **Der Footgun, der bleibt.** Die Umschaltung ist globaler veränderlicher Zustand pro
> Request: Was *vor* der Middleware aufgelöst wird — eine andere Middleware, ein
> Route-Model-Binding, ein Event-Listener — sieht noch den alten Guard. Zwei Regeln
> dagegen, beide verbindlich:
>
> 1. Die Umschaltung sitzt in der **äußersten** Middleware der Domain-Group.
> 2. **Eigener Code nennt den Guard immer explizit** (`Auth::guard('customer')`), nie
>    implizit über den Standard. Der Standard-Guard heißt `staff` (ADR-042) — ein
>    vergessenes Argument fällt damit auf die Mitarbeiterseite und muss auffallen.

### Aufräumarbeit im Template (Phase 2)

Passkeys hängen an zehn Stellen und werden **nicht jetzt** entfernt — der
Doku-Durchgang läuft zuerst:

```text
config/fortify.php                          Features::passkeys() entfernen
database/migrations/…_create_passkeys_table.php
app/Providers/FortifyServiceProvider.php
app/Http/Controllers/Settings/SecurityController.php
app/Models/User.php
routes/settings.php
resources/js/components/{PasskeyRegister,PasskeyVerify,ManagePasskeys,PasskeyItem}.svelte
resources/js/types/auth.ts
```

Ebenfalls abzuschalten, weil D-032 nur Login vorsieht: `Features::registration()`,
`Features::resetPasswords()` und `Features::emailVerification()` **für `staff`** —
für `customer` bleibt der Passwort-Reset dagegen nötig.

## ADR-044 — Kein Dark Mode; eine helle Farbpalette

Status: Accepted (2026-09-14) · ergänzt ADR-039

Es gibt **keinen** Dark Mode und keine Umschaltung. Eine Farbpalette, an Light angelehnt
(Nutzer).

**Begründung:** Zwei Paletten zu pflegen kostet bei jeder neuen Fläche doppelt — jede
Farbe, jeder Rand, jeder Schatten muss in beiden Varianten stimmen und in beiden geprüft
werden. Für eine Anwendung, die 20 Mitarbeiter im Büro und Kunden auf einer Produktseite
bedient, ist das Aufwand ohne Gegenwert. Die Entscheidung fällt **jetzt**, weil sie
später jede bis dahin gebaute Fläche erneut anfassen würde.

**Entfernt (2026-09-14), nicht nur abgeschaltet:**

```text
app/Http/Middleware/HandleAppearance.php        Cookie → View-Variable
resources/js/lib/theme.svelte.ts                Umschaltlogik, localStorage, matchMedia
resources/js/components/AppearanceTabs.svelte
resources/js/pages/settings/Appearance.svelte   Einstellungsseite + Route + Navigationseintrag
```

Dazu: `@custom-variant dark` und der komplette `.dark`-Block aus `resources/css/app.css`,
das `@class(['dark' => …])` aus `app.blade.php`, der `appearance`-Cookie aus
`encryptCookies`, die `Appearance`-Typen — und **80 `dark:`-Utilities aus 19 Dateien**.

`Sonner` (Toasts) ist fest auf `theme="light"` gesetzt; die QR-Code-Invertierung im
Zwei-Faktor-Dialog entfällt.

> **Die portierte Website war nicht betroffen.** Ihre Treffer auf `appearance` und `dark:`
> waren CSS-Eigenschaften (`appearance: none`) und Custom Properties (`--dark: rgb(…)`) —
> geprüft, nicht angenommen.

**Verbindlich für alles Weitere:** keine `dark:`-Utilities, keine `.dark`-Klasse, keine
`prefers-color-scheme`-Abfrage. Wer eine zweite Palette braucht, hebt diese ADR auf.

## ADR-045 — Object Storage unter eigenem Hostnamen `cdn.`; die Anwendung steht nicht im Abrufweg

Status: Accepted (2026-09-14) · **schließt die offene Stelle in ADR-028**

ADR-028 hat MinIO mit Cloudflare davor beschlossen, aber nie benannt, **unter welcher
Adresse** der Bucket erreichbar ist. Das ist die Antwort.

### Zwei Adressen für dasselbe Objekt

| | Wer | Wofür |
| --- | --- | --- |
| `AWS_ENDPOINT` | das AWS-SDK in PHP | Hochladen, Löschen, Signieren. Läuft im internen Netz und **erreicht nie einen Browser** |
| `AWS_URL` | der Browser | Basis für `Storage::url()` — das, was als `src`/`href` im Markup steht |

`Storage::url()` **baut nur eine Zeichenkette**. Sobald das HTML ausgeliefert ist, ist die
Anwendung aus dem Weg und der Browser holt die Datei direkt beim Speicher.

```text
Browser → cdn.dormed.de → Cloudflare (Cache) → Proxy → MinIO
```

Laravel kommt darin nicht vor. Der Proxy routet einen Hostnamen auf einen **anderen
Container** — dasselbe Mittel wie bei den vier App-Domains, nur mit anderem Ziel.

> **`cdn.` ist kein fünfter Zugriffspunkt.** Keine Route, kein Guard, keine
> Datenbankrolle. ADR-033 bleibt unberührt.

### Warum `cdn.` und nicht `s3.`

Diese Adressen stehen später in Suchindizes, in gespeicherten PDFs und in versendeten
E-Mails — sie sind praktisch unveränderlich. `s3.` backt den **Anbieter** in die Adresse;
ein Wechsel von MinIO auf R2 oder Hetzner Object Storage machte den Namen falsch, ohne
dass er sich korrigieren ließe, ohne Links zu brechen. `cdn.` benennt den **Zweck**.

### Umsetzung

```dotenv
AWS_ENDPOINT=http://minio:9000                      # nur das SDK
AWS_URL=http://cdn.dormed.test:9000/dormed-public   # landet im Markup
```

Produktion: `cdn.dormed.de` **ohne Port**, vom Proxy direkt auf MinIO geroutet. Lokal mit
`:9000`, weil Port 80 die Anwendung belegt — `127.0.0.1 cdn.dormed.test` in `/etc/hosts`.

**Voraussetzung, die dabei fehlte:** `league/flysystem-aws-s3-v3` war nicht installiert.
`FILESYSTEM_DISK=s3` stand seit dem Aufsetzen in der `.env`, jeder Zugriff auf
`Storage::disk('s3')` wäre mit `Class "…\PortableVisibilityConverter" not found`
abgebrochen — auch die Linkerzeugung. Nachinstalliert; der erste Seeder, der ein
Produktbild hochlädt, wäre sonst darüber gestolpert.

**Verifiziert am 2026-09-14:** Upload durch Laravel über das interne Netz, Abruf über
`cdn.dormed.test` mit HTTP 200 und korrektem Inhalt — ohne PHP im Abrufweg.

### Nicht-öffentliche Dateien

Für die ist der Weg **nicht** „durch die Anwendung durchreichen", sondern eine
**vorsignierte URL**: Die Anwendung prüft die Berechtigung und gibt einen zeitlich
begrenzten Direktlink aus. Autorisierung in der App, Auslieferung aus dem Speicher.

Betrifft diesen Bucket vorerst nicht — ADR-028 hält Unterschriften und Einsatzfotos
ausdrücklich heraus, genau weil er vollständig öffentlich ist.
