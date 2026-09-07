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
docker compose up -d
```

Danach:

```bash
docker compose exec app php artisan migrate
docker compose exec app php artisan db:seed
```

Die tatsächlichen Service-Namen und Befehle müssen an das finale Compose-File angepasst werden.

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
