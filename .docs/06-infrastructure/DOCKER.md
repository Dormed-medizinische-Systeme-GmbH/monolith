# Docker

> **Kurswechsel 2026-09-12** (ADR-011, ADR-014, ADR-015). Vier App-Container statt
> einem, Coolify bleibt die Plattform (kein eigenständiges Docker Swarm), Migrations
> laufen als separater Schritt.

## Container-Layout

Ein Container pro App, plus Postgres, plus Proxy — alle vier Apps nach identischem
Muster:

```text
compose.yaml / compose.prod.yaml
├── website
├── shop
├── crm
├── portal
└── postgres     (eine Instanz, eine DB, geteilt von allen vier Apps)
```

Optional lokal später: `redis`, `reverb`, `minio`, `mailpit` — nur hinzufügen, wenn
Entwicklungsbedarf besteht.

**Orchestrierung: Coolify** (ADR-014). Coolify verwaltet die vier Container + die
TLS-Terminierung selbst — **kein** eigenständiger `docker stack deploy`/Swarm-Betrieb,
kein separat gepflegter Proxy-Container außerhalb von Coolifys eigenem Traefik.

## Netzwerk

- Ein gemeinsames Docker-Network verbindet alle vier App-Container mit `postgres`
  (lokal: Compose-Bridge-Network; Prod: was Coolify vorgibt).
- Die vier App-Container kommunizieren **nicht direkt miteinander**
  (`../01-architecture/ARCHITECTURE.md` §2, `03-domain-boundaries-rules.md` Regel 2a) —
  Integration ausschließlich über die gemeinsame Postgres-Instanz via `packages/core`.
- TLS nur am Coolify-Proxy-Rand. Intern (App ↔ Postgres, App ↔ App-Netzwerk) unverschlüsselt
  — das ist gewollt, kein Sicherheitsversehen.

## Docker-Build pro App

Jedes `apps/*`-Verzeichnis hat ein eigenes Dockerfile mit Multi-Stage-Build. Wichtig für
Cache-Effizienz und den gewünschten Effekt „nur betroffene Container neubauen":

```dockerfile
# Stage 1: Core-Package-Dependencies (ändert sich seltener als App-Code)
FROM composer:2 AS core-deps
WORKDIR /core
COPY packages/core /core
RUN composer install --no-dev --optimize-autoloader

# Stage 2: App-spezifischer Build (ändert sich häufig)
FROM composer:2 AS app-build
WORKDIR /app
COPY apps/crm /app
COPY --from=core-deps /core /packages/core
RUN composer install --no-dev --optimize-autoloader

# Stage 3: Runtime
FROM php:8.4-fpm AS runtime
COPY --from=app-build /app /var/www/html
COPY --from=app-build /packages/core /var/www/packages/core
```

Effekt: Ändert sich nur App-Code (Stage 2), bleibt der Core-Layer (Stage 1) im
Docker-Cache erhalten → schnellerer Rebuild. Ändert sich `packages/core`, wird die
Cache-Invalidierung über `COPY packages/core` korrekt ausgelöst und **alle vier** Apps
müssen neu gebaut werden (ADR-011/ARCHITECTURE.md §1).

## Wann wird was neu gebaut/deployed?

| Änderung an | Betrifft |
| --- | --- |
| `apps/shop/**` (Views, Controller, Routen, Frontend) | nur `shop`-Container |
| `apps/crm/**` | nur `crm`-Container |
| `apps/website/**`, `apps/portal/**` | jeweils nur der eigene Container |
| `packages/core/**` (Models, Migrations, Domain-Services) | **alle vier Container** müssen neu gebaut und neu deployed werden |
| Coolify-Proxy-/Domain-Konfiguration | nur die Coolify-UI, kein Container-Rebuild |

## PostgreSQL

PostgreSQL läuft als eigener Container mit persistentem Volume, geteilt von allen vier
Apps. Die Datenbank darf bei `docker compose down` nicht unbeabsichtigt gelöscht werden.

## Migrations im Deployment (ADR-015)

Migrations laufen **nicht** als Teil des Boot-Vorgangs eines der vier App-Container
(bisher: `migrate --force` im Start-CMD von `compose.prod.yaml` — das entfällt).
Stattdessen: ein separater, kurzlebiger Migrations-Schritt (Coolify-Pre-Deploy-Command
oder eigener Einmal-Container), der `packages/core`-Migrations einmalig gegen `postgres`
fährt, **bevor** die vier App-Container mit neuem Core-Stand hochgefahren werden. Das
verhindert Race-Conditions, bei denen mehrere Container gleichzeitig versuchen zu
migrieren.

## Production

Production ist infrastrukturell unabhängig vom lokalen Compose-Setup (weiterhin gültig).
Das lokale Compose-Setup ist keine Aussage darüber, wie Production zwingend betrieben
werden muss — Coolify übernimmt dort die Orchestrierung.

## Migration (Daten)

Production erhält eine neue PostgreSQL-Instanz. Die Anwendung wird anschließend über
einen definierten Import-/Migration-Prozess mit Live-Daten gefüllt. Es gibt keinen
automatischen Transfer lokaler Demo-Daten in Production.

## Offene Punkte, die vor der Umsetzung geklärt werden sollten

- Reihenfolge beim Rollout eines Core-Updates in Coolify (Migrations-Schritt zuerst, dann
  alle vier Container neu deployen, oder Zero-Downtime/Blue-Green pro App?) — noch nicht
  final festgelegt.
- Healthchecks pro App-Service in Coolify — analog zum bisherigen `compose.prod.yaml`
  (`/up`-Endpoint), für alle vier Apps zu wiederholen.
- Genauer Mechanismus für den geteilten Portal/Shop-Login (siehe
  `../01-architecture/MULTI_SUBDOMAIN.md`) — Session-Tabelle, `APP_KEY`-Sharing.
