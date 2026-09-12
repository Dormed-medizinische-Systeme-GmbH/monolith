---
paths:
  - compose.yaml
  - compose.prod.yaml
  - docker/**
  - apps/*/phpunit.xml
  - .env.example
---

# Lokaler Docker-Stack

> Kurswechsel 2026-09-12 (ADR-011, ADR-014, ADR-015). Das bisherige Ein-App-Setup
> (`app` + `pgsql`) ist mit ADR-018 verworfen. Dieser Abschnitt beschreibt den
> **Zielzustand**, der noch aufgebaut werden muss — `compose.yaml`/`docker/**` existieren
> zum Zeitpunkt dieser Notiz noch im alten Zustand oder gar nicht.

## Zielbild

`compose.yaml` = Dev-Stack mit **vier** App-Services (`website`, `shop`, `crm`,
`portal` — je eigenes Laravel-Projekt aus `apps/*`, Octane/FrankenPHP, ADR-022) +
je einem `<app>-reverb`-Service wo Realtime gebraucht wird (ADR-024) + **einem**
`pgsql`-Service (Postgres 17, geteilt von allen vier) + **einem** `minio`-Service
(S3-kompatibel, ADR-025, vorerst ohne konkrete Nutzung). Kein Supabase (ADR-001).
Aktuell nur `crm` + `crm-reverb` aktiv befüllt (ADR-020), die übrigen drei App-Services
sind Platzhalter. Ziel weiterhin: `docker compose up -d --build` startet alles.

## DB-Credentials

Eine Quelle: der `DB_*`-Block in der `.env` **je App** (jede App hat ihre eigene `.env`,
da eigenständiges Laravel-Projekt) — alle zeigen auf denselben `pgsql`-Servicenamen und
dieselbe Datenbank. `pgsql` bekommt `POSTGRES_*` per `${...}`-Substitution aus einer
zentralen Compose-`.env` im Repo-Wurzelverzeichnis.

## Fallen (aus dem alten Setup übernommen, gelten weiterhin je App)

- **Kein `env_file: .env`** im App-Service. Laravel liest `.env` selbst vom Bind-Mount.
  Injizierte Container-Env-Vars überschreiben sonst `<env>`-Overrides in `phpunit.xml`.
- **`user: "${WWWUSER:-1000}:${WWWGROUP:-1000}"`** in jedem App-Service, sonst gehören im
  Bind-Mount erzeugte Dateien root.
- Das Base-Image braucht `pdo_pgsql` nachinstalliert (kein Standard in
  `laravelsail/php84-composer` o. ä.).

## Migrations (ADR-015)

Migrations laufen **nicht** im Start-CMD eines App-Services. Ein separater
`migrate`-Schritt (eigener Compose-Service mit `restart: "no"`, der einmalig
`packages/core`-Migrations fährt und dann exitet, **vor** dem Start der vier
App-Services) — lokal wie in Prod (siehe `.docs/06-infrastructure/DOCKER.md`).

## compose.prod.yaml (Coolify, ADR-014)

Vier App-Services (`website`, `shop`, `crm`, `portal`) analog zu `compose.yaml`, gebaut
aus je einem `docker/<app>/Dockerfile` (Multi-Stage: `packages/core`-Deps → App-Build →
Runtime, siehe `.docs/06-infrastructure/DOCKER.md`). Kein eigener Proxy-Service — Coolify
terminiert TLS und routet die vier Domains selbst.

- Secrets/Domains kommen aus der Coolify-UI, **nicht** aus einer committeten Datei.
  Pflicht je App: `APP_KEY`, `APP_URL`, `DB_PASSWORD`; für Portal/Shop zusätzlich
  identischer `APP_KEY` + `SESSION_DOMAIN` (Cross-App-Login, ADR-016).
- `bootstrap/app.php` je App: `trustProxies(at: '*')` — nötig hinter Coolifys Traefik.
- `.dockerignore` hält `.env`, `vendor`, `node_modules`, `tests`, `docs` aus dem
  Build-Kontext jeder App.

## Noch zu bauen

Dieses Zielbild ist zum Zeitpunkt dieser Notiz **nicht** umgesetzt — das ist ein
separater, noch zu bestätigender Ausführungsschritt (ADR-018: erst Skelett aufsetzen,
dann Altcode/Alt-Compose ersetzen).
