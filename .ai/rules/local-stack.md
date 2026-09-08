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

## compose.prod.yaml

Vom Nutzer angelegte Kopie, aktuell inhaltlich der frühere `compose.yaml`-Stand
(lokaler Dev-Stack, nicht prod-tauglich) – nicht als Referenz behandeln, bis geklärt.
