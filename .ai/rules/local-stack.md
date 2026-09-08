---
paths:
  - compose.yaml
  - compose.prod.yaml
  - docker/**
  - phpunit.xml
  - .env.example
---

# Lokaler Docker-Stack

`compose.yaml` = minimaler Dev-Stack: `app` (Laravel via `php artisan serve`) +
`pgsql` (Postgres 17). Kein Supabase. Ziel: `docker compose up -d --build` startet alles.

## DB-Credentials

Eine Quelle: der `DB_*`-Block in `.env`. `pgsql` bekommt `POSTGRES_*` per
`${DB_DATABASE|DB_USERNAME|DB_PASSWORD}`-Substitution (Compose liest dafür dieselbe
`.env`). `DB_HOST` muss der Compose-Servicename `pgsql` sein.

## Fallen

- **Kein `env_file: .env` im `app`-Service.** Laravel liest `.env` selbst vom
  Bind-Mount. Injiziert man die Keys als echte Container-Env-Vars, überschreiben
  sie die `<env>`-Werte in `phpunit.xml` (PHPUnit ersetzt keine bereits gesetzte
  Variable) → die ganze Testsuite läuft dann gegen `local` / pgsql statt
  `testing` / sqlite und Session-/CSRF-Tests brechen (419, „not authenticated").
- **`user: "${WWWUSER:-1000}:${WWWGROUP:-1000}"`** im `app`-Service, sonst gehören
  im Bind-Mount erzeugte Dateien (`composer install`, `artisan make:*`) root.
- Das Base-Image (`laravelsail/php84-composer`) hat **kein Node** und **kein
  `pdo_pgsql`**. `pdo_pgsql` wird in `docker/app/Dockerfile` nachinstalliert.
  Frontend-Assets (`npm run build`) müssen außerhalb des Containers gebaut werden;
  bis dahin liefern alle `@vite`-Views (login, register, dashboard, profile) im
  Dev-Server einen `ViteException`/500. Tests umgehen das via `withoutVite()` in
  `tests/TestCase.php`.

## phpunit.xml

`APP_URL=http://localhost` und `SESSION_DOMAIN=null` sind gesetzt, damit HTTP-Tests
gegen `localhost` laufen (die `.env` hat `SESSION_DOMAIN=.dormed.test` für die
geteilte Subdomain-Session). Kontext-Route-Tests mit voller URL aufrufen.

## compose.prod.yaml (Coolify Test/Staging)

Eigener Stack für das Coolify-Deployment, gebaut aus `docker/app-prod/Dockerfile`
(Multi-Stage: composer `--no-dev` → `npm run build` → `php:8.4-cli` + `pdo_pgsql`).
Nicht mit `compose.yaml` (Dev) vermischen.

- Runtime ist `php artisan serve` – nur test-tauglich; für echten Traffic auf
  FrankenPHP / php-fpm+nginx wechseln.
- `pgsql` hat ein Named Volume `pgsql-data` (Coolify persistiert das), keinen `ports:`-Eintrag.
- Migrationen laufen im Container-Start-CMD (`migrate --force`), plus `package:discover`.
- Secrets/Domains kommen aus der Coolify-UI (Environment Variables), **nicht** aus
  einer committeten Datei. Pflicht: `APP_KEY`, `APP_URL`, `DOMAIN_CRM/PORTAL/SHOP`,
  `SESSION_DOMAIN`, `DB_PASSWORD`.
- Subdomains: alle drei `https://dormed-{crm,portal,shop}.everding.it` in Coolify
  beim `app`-Service als Domains eintragen (nutzt das Server-Wildcard `*.everding.it`).
- `bootstrap/app.php` hat `trustProxies(at: '*')` – nötig hinter Coolifys Traefik,
  damit HTTPS/Secure-Cookies erkannt werden.
- `.dockerignore` hält `.env`, `vendor`, `node_modules`, `tests`, `docs` aus dem Build-Kontext.
