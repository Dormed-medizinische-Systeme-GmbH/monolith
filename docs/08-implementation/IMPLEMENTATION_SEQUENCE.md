# Implementierungsreihenfolge

## Phase 0 — Repository bereinigen

- alte Docker-Compose-Konfiguration entfernen, sofern sie nicht benötigt wird
- bestehendes Standard-Laravel-Projekt prüfen
- vorhandene AI Rules und Laravel Skills identifizieren
- keine Legacy-Fachlogik voreilig übernehmen

## Phase 1 — Docker + PostgreSQL

Ziel:

```text
docker compose up -d
```

muss Laravel und PostgreSQL starten.

Danach:

```text
php artisan migrate
```

muss erfolgreich laufen.

## Phase 2 — Breeze/Auth

- Breeze funktionsfähig
- User-Modell funktionsfähig
- Session/Auth lokal testbar
- keine fertige Customer-/Employee-Domain erzwingen

## Phase 3 — Multi-Subdomain

- gemeinsames Laravel
- CRM Host
- Portal Host
- Shop Host
- zentraler Host-Kontext
- gemeinsame Assets
- gemeinsame Session-/Cookie-Strategie bewusst konfigurieren

## Phase 4 — Demo Seeder

Minimaler Seed:

- interne Demo-Identität
- später Demo Company
- später Demo Person/Contact

Seeder muss reproduzierbar sein.

## Phase 5 — Erster vertikaler Slice

CRM:

```text
Company
Person
CompanyContact
Address
Location
```

Dazu:

- Liste
- Detailansicht
- Anlegen
- Bearbeiten
- Autorisierung
- Audit-Basis
- Tests

## Phase 6 — Discovery mit echtem UI

Ab diesem Zeitpunkt wird die fachliche Discovery anhand der tatsächlichen Anwendung fortgesetzt.

## Phase 7+

Erst danach:

- Communication
- Service
- Sales
- Documents
- Billing
- Portal
- Shop
- Inventory
