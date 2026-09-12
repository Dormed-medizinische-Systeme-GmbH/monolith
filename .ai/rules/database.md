---
paths:
  - packages/core/database/migrations/**
  - packages/core/src/Modules/**/Models/**
---

# Datenbank-Standards

Vollständige Begründung: `.docs/04-database/DATABASE.md`. Kurzfassung — verbindlich
für jede Migration/jedes Model in `packages/core`.

## Normalisierung

3NF-Baseline. Keine Spalte speichert etwas, das aus anderen Spalten ableitbar ist —
außer den in `DATABASE.md` registrierten Ausnahmen (Invoice-Summen/-Snapshots,
Preis-Snapshots). Neue Ausnahme? Erst in `DATABASE.md` eintragen, dann bauen.

## Enums: VARCHAR + DB-CHECK-Constraint (D-094)

Kein Postgres-natives `ENUM`, keine reine PHP-seitige Validierung ohne DB-Constraint.

```php
$table->string('status');
// + eigene Migration/Statement:
DB::statement("ALTER TABLE <table> ADD CONSTRAINT <table>_<col>_check
    CHECK (<col> IN (...))");
```

PHP-Enum-Klasse + DB-CHECK-Werteliste müssen synchron bleiben, beide aus derselben
`D-NNN`-Quelle in `grill-log.md`.

## Primärschlüssel

`id()` (bigint auto-increment). Kein UUID (D-095).

## Geldbeträge

`decimal(12,2)` einheitlich. Prozentsätze `decimal(5,2)`. Menge/Quantity ist **keine**
Geldspalte — bleibt `decimal(10,2)`.

## Naming

`snake_case`, Tabellen Plural, `<singular>_id`-Fremdschlüssel. Details: `DATABASE.md`.

## Querschnitt

`SoftDeletes` + `TracksBlame` auf jedem fachlichen Modell inkl. Pivots mit
Zusatzfeldern (D-018/D-023). Jede FK-Spalte indiziert.
