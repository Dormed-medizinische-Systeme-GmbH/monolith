# Customer Portal

> **Kurswechsel 2026-09-12** (ADR-011, ADR-016, ADR-020): Das Portal ist die
> eigenständige App `apps/portal` — existiert größtenteils bereits (genauer Frontend-
> Stack aktuell unbekannt, vermutlich Inertia). Aktuell **kein** aktiver Aufbau — nur
> architektonisch vorbereitet (Domain `my.dormed.de`, ADR-021). Portal und Shop teilen
> sich einen Kundenlogin (ADR-016) — das löst die „Shared Identity"-Frage unten. Bau-Fokus
> liegt auf `packages/core` + `apps/crm` (ADR-020).

## Zweck

Das Portal stellt Kunden die für ihre Company freigegebenen Daten bereit.

Mögliche Inhalte:

- Locations
- Geräte
- Serviceverträge
- Wartungen
- Servicefälle
- Dokumente
- Rechnungen
- Bestellungen
- Ansprechpartner

## Isolation

Ein Customer Contact darf ausschließlich Daten sehen, die über seine berechtigte Company-Beziehung zugänglich sind.

Kein direkter Zugriff auf PostgreSQL.

Keine alleinige Sicherheit durch Frontend-Filter.

## Shared Identity (entschieden, ADR-016)

Portal und Shop teilen sich einen Kundenlogin: derselbe Kunde bewegt sich mit einem
Account/einer Session in beiden Apps (gleicher `APP_KEY` + `SESSION_DOMAIN` + gemeinsame
`sessions`-Tabelle, Details: `.docs/01-architecture/MULTI_SUBDOMAIN.md`). Die genaue
Account-/Domain-UX (Registrierung, Passwort-Reset) ist bei der Portal-Migration final zu
klären.

## Domain

Das Portal läuft als eigenständige App `apps/portal` (ADR-011) unter `my.dormed.de`
(ADR-021):

```text
my.dormed.de
```
