---
paths:
  - docker-compose*.yaml
  - docker/**
  - phpunit.xml
  - .env.example
---

# Lokaler Stack

> Stand 2026-09-14 (ADR-041). Ersetzt den Vier-App-Dev-Stack vollständig.
> Hintergrund und Kostenrechnung: `.docs/06-infrastructure/HOSTING.md`.

## Start

```bash
cp .env.example .env && php artisan key:generate
docker compose up -d --build
```

| | |
| --- | --- |
| Website | http://dormed.test |
| Shop | http://shop.dormed.test |
| ERP | http://erp.dormed.test |
| Portal | http://my.dormed.test |
| Object Storage | http://cdn.dormed.test:9000/dormed-public (ADR-045) |
| MinIO-Konsole | http://localhost:9001 (`dormed` / `secret12345`) |

> **Port 80, ein Port für alle vier.** Getrennte Ports würden nichts trennen — zugeordnet
> wird am Host-Header. Port 80, weil ein nacktes `erp.dormed.test` im Browser sonst auf
> 443 und danach auf 80 geht: beide leer, `ERR_CONNECTION_REFUSED` auf allen vier Hosts.
> Das ist zugleich näher an Produktion, wo der Proxy auf 443 terminiert und es gar keine
> sichtbaren Ports gibt.

Voraussetzung in `/etc/hosts`:

```text
127.0.0.1 dormed.test erp.dormed.test my.dormed.test shop.dormed.test
127.0.0.1 cdn.dormed.test
```

Alle vier Hostnames zeigen auf **denselben** Container — es ist eine Anwendung
(ADR-033).

## npm, nicht pnpm

`"packageManager": "npm@12.0.2"` in der `package.json` legt es fest — corepack weist ein
versehentliches `pnpm install` damit ab. Die `pnpm-workspace.yaml` des Starter-Kits ist
entfernt: sie tat nichts und behauptete das Gegenteil vom Rest des Repos.

Begründung: pnpms Stärke ist das Verlinken vieler Pakete in einem Monorepo — wir haben
bewusst das Gegenteil (ein Paket, ein Monolith, ADR-033). Ihr einziger hier relevanter
Vorteil, die strengere Auflösung, war im Starter-Kit ohnehin per
`publicHoistPattern: ['@inertiajs/core']` wieder ausgehebelt.

## Alles ist flüchtig — das ist Absicht

Postgres und MinIO liegen auf `tmpfs`. Ein `down` vergisst alles, ein `up` migriert und
seedet neu.

- **Nie ein Volume für `pgsql` oder `minio` in die Dev-Compose eintragen.** Der Seeder
  ist der einzige Weg zu Daten und bleibt genau dadurch dauerhaft lauffähig — es ist
  derselbe Lauf, der in Produktion einmal die Ausgangsbasis herstellt (ADR-041).
- **Datenbank-Seed und Object-Storage-Seed gehören zusammen.** Produktbilder und
  Prospekte liegen in MinIO, ihre Metadaten in Postgres. Wer das eine ändert, ändert das
  andere mit.
- Zugangsdaten stehen im Klartext in `.env.example`. Das ist kein Versehen: der Stack ist
  flüchtig und rein lokal.

## Services

| Service | Zweck |
| --- | --- |
| `app` | die Anwendung, Port 80 → `APP_PORT` |
| `vite` | Inertia/Svelte-Dev-Server (ADR-039), Port 5173 |
| `setup` | Einmal-Schritt `migrate --force --seed`, läuft bei jedem `up` |
| `pgsql` | PostgreSQL 17, tmpfs |
| `minio` | S3-Ersatz, tmpfs |
| `minio-init` | legt den Bucket an und schaltet ihn öffentlich (ADR-028) |

Zwei Datenbanken: `dormed` für den Dev-Stand, `dormed_test` für den Testlauf (angelegt aus
einer Inline-`config` in der Compose). Ohne die Trennung räumt jeder `php artisan test` den
Seed weg.

**Es gibt keinen `docker/`-Ordner.** Genau drei Docker-Artefakte liegen im
Wurzelverzeichnis: `Dockerfile` (Produktion, Konfiguration als Heredoc darin),
`docker-compose.yaml` und `.dockerignore`. Nichts davon gehört woandershin.

Das Image des App-Containers baut aus `vendor/laravel/sail/runtimes/8.4` — Sails Runtime,
**ohne** den `sail`-Wrapper. Kein eigenes Dev-Dockerfile, das gepflegt werden müsste.
Befehle laufen normal, nicht über `./vendor/bin/sail`.

## Zwei Adressen für dasselbe MinIO (ADR-045)

Die häufigste Stolperstelle:

- `AWS_ENDPOINT=http://minio:9000` — aus Sicht des **PHP-Containers**. Nur das SDK.
- `AWS_URL=http://cdn.dormed.test:9000/dormed-public` — aus Sicht des **Browsers**.
  `Storage::url()` baut daraus die Links; ab da ist Laravel aus dem Weg.

Wer nur einen der beiden setzt, bekommt entweder Verbindungsfehler beim Upload oder
Links, die im Browser nicht auflösen.

## Befehle im Container immer mit `-u sail`

```bash
docker compose exec -u sail app php artisan test
docker compose exec -u sail app composer require …
```

**Ohne `-u sail` läuft `exec` als root**, und alles, was dabei entsteht, gehört danach
root: neu erzeugte Klassen aus `make:*`, `vendor/`-Pakete nach einem `composer require`,
Caches unter `bootstrap/` und `storage/`. Auf dem Host lassen sie sich dann nicht mehr
überschreiben — der Fehler heißt „Keine Berechtigung" und sieht nach allem Möglichen aus,
nur nicht nach seiner Ursache.

Reparatur, falls es doch passiert ist:

```bash
docker compose exec app chown -R sail:sail /var/www/html/vendor /var/www/html/storage
```

## Abgebrochene Testläufe hinterlassen Sperren

`php artisan test` gegen `dormed_test` abzubrechen (Strg-C, Timeout, `kill`) lässt eine
Verbindung im Zustand **`idle in transaction`** mit offenem `BEGIN` zurück. Der nächste
Lauf blockiert dann beim `drop table` von `RefreshDatabase` — er **hängt**, er schlägt
nicht fehl. Das sieht aus wie ein kaputter Test und ist keiner.

```bash
docker compose exec app pkill -f "artisan test"        # im CONTAINER, nicht auf dem Host
docker compose exec -T pgsql psql -U dormed -tAc \
  "select pg_terminate_backend(pid) from pg_stat_activity
   where datname='dormed_test' and pid <> pg_backend_pid()"
```

Bei zerschossenem Schema (`relation "users" already exists`) hilft nur der Neuaufbau:
`docker compose down && docker compose up -d` — alles ist flüchtig.

## Migrations

`migrate` läuft im `setup`-Service, **nie** im Start-CMD der App (ADR-015). Kein
`--isolated`: der Cache-Lock läge in `cache_locks`, einer Tabelle, die erst die
Migration anlegt, die gerade laufen soll. Gegen eine leere Datenbank scheitert das
immer. Es fährt ohnehin genau ein Container.

## Kein Supabase (ADR-001), kein Reverb (ADR-027), kein Octane (ADR-034)

Direkter Laravel → PostgreSQL-Zugriff. Kein zusätzlicher API-Proxy, kein
Websocket-Daemon, keine langlebigen PHP-Worker.

## Produktion benutzt diese Datei nicht

Dort sind Postgres und S3 externe Coolify-Ressourcen und das Image kommt aus dem
`Dockerfile` im Wurzelverzeichnis (nginx + php-fpm). Eine `docker-compose.prod.yaml` gibt es
bewusst noch nicht — sie wird fällig, sobald der Queue-Worker als zweiter Prozess
gebraucht wird (ADR-041).
