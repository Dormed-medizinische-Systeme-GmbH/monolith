---
paths:
  - database/migrations/**
  - app/Modules/**/Models/**
---

# Datenbank-Standards

Vollständige Begründung: `.docs/04-database/DATABASE.md`. Kurzfassung — verbindlich
für jede Migration und jedes Model der Anwendung.

## Migrationen: bestehende ändern, keine neuen — bis zum Produktivgang

**Solange es keine produktive Datenbank gibt, wird eine Schemaänderung in der
Migration gemacht, die die Tabelle anlegt.** Keine Folge-Migration, die eine
Spalte umbenennt, nachträgt oder wieder entfernt.

Der Stand läuft mit `migrate:fresh --seed`, es gibt keine Baseline und keine
Daten, die eine Änderung überleben müssten. Eine Kette aus „Spalte anlegen,
Spalte umbenennen, Spalte doch wieder ändern" wäre reine Archäologie: sie
erzählt die Entstehungsgeschichte statt den Zustand, und wer das Schema lesen
will, muss vier Dateien in der richtigen Reihenfolge durchgehen.

**Ab dem ersten produktiven Deploy kippt die Regel** — dann ist jede bestehende
Migration unantastbar und jede Änderung eine neue. Diese Zeile hier ist dann zu
ersetzen.

> **Dazu gehört: nach jeder Schemaänderung `migrate:fresh --seed` laufen
> lassen.** Eine geänderte Migration wirkt sonst nicht — `migrate` sieht sie als
> erledigt an. Genau daran hängt auch der Coolify-Stand: dort läuft `migrate
> --force`, und eine geänderte Datei ändert nichts an einer Datenbank, die die
> Migration schon verbucht hat.

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

## Primärschlüssel: UUIDv7 (ADR-046)

**Jeder fachliche Datensatz und jede Identität.** Kein `id()`, kein bigint.

```php
$table->uuid('id')->primary();
$table->foreignUuid('company_id')->constrained('companies');
$table->uuidMorphs('addressable');
```

Das Model braucht dazu `use HasUuids;` — sonst versucht Eloquent, einen
Auto-Increment-Schlüssel zu lesen, den es nicht gibt. Laravel 13 erzeugt darüber
`Str::uuid7()`, also zeitgeordnet.

**Ausgenommen:** Laravels Infrastrukturtabellen (`cache`, `cache_locks`, `jobs`,
`job_batches`, `failed_jobs`). `sessions.user_id` folgt dagegen `employees.id` — der SPALTENNAME bleibt, Laravels `DatabaseSessionHandler` schreibt genau ihn.

> **Selbstreferenz gehört hinter `Schema::create`.** Anders als bei `bigserial` setzt
> Postgres den PRIMARY KEY hier per `ALTER TABLE` — und zwar NACH den Fremdschlüsseln.
> Ein FK auf die eigene Tabelle findet innerhalb der Closure noch keinen eindeutigen
> Index und bricht ab. Spalte in `create` anlegen, Constraint in einem nachgelagerten
> `Schema::table()` (siehe `companies.billing_company_id`).

## Geldbeträge

`decimal(12,2)` einheitlich. Prozentsätze `decimal(5,2)`. Menge/Quantity ist **keine**
Geldspalte — bleibt `decimal(10,2)`.

## Naming

`snake_case`, Tabellen Plural, `<singular>_id`-Fremdschlüssel. Details: `DATABASE.md`.

## Querschnitt

`SoftDeletes` + `TracksBlame` auf jedem fachlichen Modell inkl. Pivots mit
Zusatzfeldern (D-018/D-023). Jede FK-Spalte indiziert.

## Rollen und RLS (ADR-036)

Migrations laufen als `dormed_owner`; die Anwendung verbindet sich **nie** so. Für jede
neue Tabelle gilt:

- `ALTER DEFAULT PRIVILEGES` deckt `dormed_staff` und `dormed_customer` automatisch ab —
  dafür ist nichts zu tun.
- **`dormed_public` bekommt nichts automatisch.** Soll die Website die Tabelle lesen,
  braucht es ein bewusstes, einzelnes `GRANT SELECT` plus eine Policy, die auf
  Veröffentlichtes einschränkt. Das ist Absicht, keine Lücke.
- Tabellen, die ein Kunde erreichen kann, führen die Eigentümerspalte (Firma) **von
  Anfang an** mit — auch solange RLS noch nicht aktiviert ist. Nachrüsten hieße, jede
  bestehende Query zu auditieren.
