# Lokale Entwicklung

> **Kurswechsel 2026-09-12** (ADR-011, ADR-015, ADR-020). Kein einzelner `app`-Container
> mehr — vier App-Container + ein separater Migrations-Schritt. Aktueller Bau-Fokus:
> `packages/core` + `apps/crm` (ADR-020); die übrigen drei Container existieren vorerst
> nur als architektonischer Platzhalter.

## Ziel

Die komplette technische Basis soll lokal mit Docker Compose startbar sein.

Zielstack (ADR-011, `.docs/06-infrastructure/DOCKER.md`):

```text
website container   (apps/website, Blade — Platzhalter)
shop container       (apps/shop, Inertia+Svelte — Platzhalter, Migration später)
crm container        (apps/crm, Inertia+Svelte — aktueller Bau-Fokus)
portal container     (apps/portal — Platzhalter)
postgres container   (eine Instanz, geteilt von allen vier)
```

Weitere Container (redis, reverb, minio, mailpit) werden erst hinzugefügt, wenn ein
konkreter Bedarf besteht.

## Anforderungen

Ein neuer Entwickler soll nach Installation von Docker mit einem dokumentierten Ablauf
ungefähr folgendes ausführen können:

```bash
cp .env.example .env
docker compose up -d --build
```

Migrations laufen **nicht** mehr im Boot eines App-Containers, sondern als separater
Einmal-Schritt gegen `packages/core/database/migrations` (ADR-015):

```bash
docker compose run --rm migrate
docker compose exec crm php artisan db:seed
```

## Domains (lokal)

Jede App bedient ihre eigene Domain (kein zentrales Subdomain-Dispatch mehr, ADR-012 —
siehe `.docs/01-architecture/MULTI_SUBDOMAIN.md`). Website läuft **ohne** Subdomain
direkt auf der Basis-Domain (wie `dormed.de` live). Lokal auf `127.0.0.1` zeigen lassen
– einmalig in `/etc/hosts` eintragen:

```text
127.0.0.1 dormed.test crm.dormed.test portal.dormed.test shop.dormed.test
```

Live-Domains (Prod, ADR-021): `dormed.de` (Website), `my.dormed.de` (Portal),
`shop.dormed.de` (Shop), `crm.dormed.de` (CRM).

## Environment

Secrets und lokale Zugangsdaten gehören in `.env`.

`.env` wird nicht committed.

`.env.example` dokumentiert notwendige Variablen ohne echte Secrets.

## PostgreSQL

Die Anwendung verbindet sich direkt mit PostgreSQL.

Keine Supabase-API und kein zusätzlicher API-Proxy für normale Laravel-Datenbankzugriffe.

## Produktionsdaten

Produktionsdaten werden niemals durch den lokalen Demo-Seeder bereitgestellt.

Produktionsumgebungen erhalten eine eigene PostgreSQL-Datenbank.

## Ziel

Local:

```text
Docker Compose
    |
    +-- Laravel
    |
    +-- PostgreSQL
```

Production:

```text
Production Laravel
       |
       v
Production PostgreSQL
```

Die Infrastruktur darf später erweitert werden, ohne das fachliche Laravel-Modell von Docker Compose abhängig zu machen.
