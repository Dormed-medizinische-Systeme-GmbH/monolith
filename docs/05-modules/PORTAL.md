# Customer Portal

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

## Shared Identity

Portal und Shop sollen perspektivisch dieselbe technische Identität verwenden können.

Die genaue Account-/Domain-UX bleibt offen.

## Subdomain

Das Portal läuft als eigener Anwendungskontext:

```text
portal.<base-domain>
```

Es ist keine zweite Laravel-Installation.
