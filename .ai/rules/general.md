---
paths:
  - docker-compose.yml
  - docker-compose.dev.yml
---

# General

## Zwei getrennte .env-Dateien: Laravel-App vs. Supabase-Compose-Stack
Das Repo hat ZWEI unabhängige Env-Konfigurationen im selben Verzeichnis:
- `.env` / `.env.example` → Laravel-App (APP_KEY, DB_URL, DB_SCHEMA, ...)
- `.env.supabase` / `.env.supabase.example` → docker-compose.yml (self-hosted Supabase Stack)

Niemals die Compose-Variablen in `.env`/`.env.example` mischen oder die Datei `.env.supabase`
in `.env` umbenennen — Docker Compose lädt automatisch eine Datei namens `.env` im selben
Verzeichnis, was mit Laravels `.env` kollidieren würde. Lokales `docker compose up` braucht
`--env-file .env.supabase`. `.env.supabase` ist in .gitignore, niemals committen.

Die meisten SERVICE_*-Variablen in docker-compose.yml werden von Coolify automatisch generiert
(Magic-Var-Syntax SERVICE_<TYPE>_<NAME>: FQDN/URL/USER/PASSWORD/BASE64...) — nicht manuell setzen.

Bekannter vermuteter Bug im Compose-Template (realtime-dev, ~Zeile 315):
`SECRET_KEY_BASE=${SECRET_PASSWORD_REALTIME}` — der Name beginnt mit SECRET_ statt SERVICE_,
matcht also NICHT Coolifys Magic-Var-Muster und hat keinen Fallback-Default. Muss manuell in
`.env.supabase` gesetzt werden (z. B. `openssl rand -base64 48`), sonst startet realtime-dev
vermutlich mit leerem SECRET_KEY_BASE und crasht.

## docker-compose.dev.yml ist eine gepflegte Kopie, kein sail:install-Ziel
docker-compose.dev.yml ist ein manuell gepflegtes Duplikat von docker-compose.yml (Supabase-Stack)
plus einem Sail-Service "laravel.test" (Definition aus vendor/laravel/sail/stubs/compose.stub
übernommen, OHNE eigenes "networks:" — läuft dadurch im selben Default-Netzwerk wie die
supabase-*-Services und erreicht sie per Servicename, z. B. supabase-supavisor:5432).

NIEMALS `php artisan sail:install` in diesem Repo laufen lassen — der Befehl schreibt
kompromisslos in `docker-compose.yml` am Repo-Root und hängt dabei mysql/redis/selenium/mailpit
an die BESTEHENDE (Coolify-Prod-)Datei an, reformatiert deren Einrückung komplett und
überschreibt außerdem phpunit.xml (ersetzt sqlite :memory: durch eine nicht existierende
"testing"-DB) sowie .env (DB_CONNECTION -> mysql, REDIS_HOST -> redis, MAIL_MAILER -> smtp).
Das ist bereits einmal versehentlich passiert und musste per `git checkout -- docker-compose.yml
phpunit.xml` rückgängig gemacht werden. Service-Änderungen an laravel.test gehören von Hand in
docker-compose.dev.yml, nicht per Neuinstallation.

Zwei Bugs im Original-docker-compose.yml wurden beim Duplizieren entdeckt und NUR in
docker-compose.dev.yml gefixt (Original bewusst unangetastet gelassen, da nur für Coolify-Deploy
gedacht):
1. `exclude_from_hc: true` (bei supabase-rest) ist kein gültiger Compose-Spec-Key -> bricht
   `docker compose config` mit Schema-Fehler ab. Fix: zu `x-exclude_from_hc` umbenannt.
2. Drei benannte Volumes (deno-cache, supabase-db-data, supabase-db-config) werden verwendet,
   aber nie unter einem Top-Level `volumes:` deklariert -> "refers to undefined volume" unter
   reinem docker compose. Coolify scheint das intern zu tolerieren/selbst zu ergänzen.

Lokale Secrets/JWTs (SERVICE_PASSWORD_*, SERVICE_SUPABASEANON_KEY/SERVICE_SUPABASESERVICE_KEY
als signierte JWTs) werden NICHT von Coolifys Magic-Var-Engine erzeugt, da die nur beim
Coolify-Deploy läuft. Stattdessen: `./volumes/generate-dev-env.sh` einmalig ausführen, erzeugt
`.env.supabase` (gitignored) aus der Struktur von `.env.supabase.example`.

Docker-Daemon lief in der Cloud-Sandbox nicht (Rechte-Einschränkung im Remote-Container) -> nur
`docker compose config` (Struktur/Syntax) validiert, kein echter `up`/Build getestet.
