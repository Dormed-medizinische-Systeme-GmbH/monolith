# Lokale Entwicklung

## Ziel

Die komplette technische Basis soll lokal mit Docker Compose startbar sein.

Minimaler Stack:

```text
Laravel/PHP container
PostgreSQL container
```

Weitere Container werden erst hinzugefügt, wenn ein konkreter Bedarf besteht.

## Anforderungen

Ein neuer Entwickler soll nach Installation von Docker mit einem dokumentierten Ablauf ungefähr folgendes ausführen können:

```bash
cp .env.example .env
docker compose up -d --build
```

Der `app`-Container bootstrappt sich beim ersten Start selbst (`composer install`,
`php artisan key:generate` falls nötig, `php artisan migrate`) und startet dann
`php artisan serve` auf Port 8000.

Danach:

```bash
docker compose exec app php artisan db:seed
```

## Subdomains

Die Anwendung bedient drei Kontext-Subdomains (siehe
`docs/01-architecture/MULTI_SUBDOMAIN.md`). Lokal müssen sie auf `127.0.0.1`
zeigen – einmalig in `/etc/hosts` eintragen:

```text
127.0.0.1 dormed.test crm.dormed.test portal.dormed.test shop.dormed.test
```

Aufruf: `http://crm.dormed.test:8000`, `http://portal.dormed.test:8000`,
`http://shop.dormed.test:8000`. Die Hosts sind über `DOMAIN_CRM` / `DOMAIN_PORTAL`
/ `DOMAIN_SHOP` in `.env` konfigurierbar.

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
