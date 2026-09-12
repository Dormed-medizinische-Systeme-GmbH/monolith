# Datenbank-Standards

Schema-übergreifende Regeln — gilt für **jede** Migration in
`packages/core/database/migrations/` (ADR-013), unabhängig vom fachlichen Modul.
Die `04-domain/*.md`-Specs legen Felder/Typen/Beziehungen je Bereich fest; dieses
Dokument legt fest, **wie** diese physisch in PostgreSQL umgesetzt werden.

## Normalisierungsziel

**3NF als Baseline, BCNF wo praktikabel.** Keine Spalte speichert etwas, das aus
anderen Spalten/Tabellen ableitbar ist — **außer** den unten explizit gelisteten,
begründeten Ausnahmen. Neue Ausnahmen werden hier eingetragen, nicht stillschweigend
im Code gelebt.

### Registrierte Ausnahmen

| Feld | Grund | Entscheidung |
| --- | --- | --- |
| `Invoice.net_total` / `tax_total` / `gross_total` | **Keine echte Normalisierungsverletzung** — GoBD-Unveränderlichkeit verlangt einen eingefrorenen Beleg-Zustand, nicht nur eingefrorene Zeilen. Eine Rechnung muss ihre Summe zum Ausstellungszeitpunkt festhalten, auch bei perfekter Normalisierung der Positionen. | **Behalten** (D-093) |
| `Invoice.recipient_*` (Name/Adresse/USt-ID, `BILLING.md`) | Historischer Snapshot, **bewusste** Duplikation eines vergangenen Zustands — eine aktuelle `Company`-Adresse darf die historische Rechnung nicht rückwirkend ändern (`DOMAIN.md` Billing-Prinzip). Kein Normalisierungsfehler: es ist ein anderer Fakt (Zustand zum Zeitpunkt X), keine zweite Kopie desselben Fakts. | **Behalten** |
| `MaintenanceDevice.maintenance_fee_snapshot`, `ServiceContract.maintenance_price` (D-065) | Gleiche Begründung wie oben — Preis-Snapshot zum Vertrags-/Einsatzzeitpunkt, keine „aktuelle" Ableitung. | **Behalten** |

### Gestrichene Denormalisierungen (D-093)

- **`ServiceContract.next_due_at`** — gestrichen. Ursprünglich als Planungs-Cache
  vorgeschlagen (D-037), widerspricht aber der höchstmöglichen Normalisierung.
  Wird **live berechnet**: `MAX(maintenance_devices.performed_at über diesen
  Vertrag) + maintenance_interval_months`, bei Bedarf über eine DB-View oder
  Query-Scope, **nicht** über eine gecachte Spalte. Planungsansichten
  („welche Verträge sind fällig") joinen/berechnen live — bei Performance-Bedarf
  später eine **Materialized View**, keine denormalisierte Tabellenspalte.
- **`users.name`** — gestrichen (war nur Breeze-Kompatibilität, D-032/ADR-023
  ersetzt Breeze durch Fortify). `User::getNameAttribute()` als Accessor
  (`"{$this->first_name} {$this->last_name}"`), keine Spalte.

## Enum-Speicherung (D-094)

**VARCHAR + DB-CHECK-Constraint.** Konsistent mit ADR-007 (Postgres als
zusätzliche Integrity Boundary): die Werteliste wird **zusätzlich zur** Laravel-
seitigen PHP-Enum-Validierung auch auf DB-Ebene erzwungen — ein direkter
DB-Zugriff (Reporting-Tool, manuelles SQL, Bug in der Anwendungsschicht) kann
keinen ungültigen Wert schreiben.

```php
Schema::table('service_contracts', function (Blueprint $table) {
    $table->string('status');
});
DB::statement("ALTER TABLE service_contracts ADD CONSTRAINT service_contracts_status_check
    CHECK (status IN ('offen','aktiv','gekuendigt','verschrottet','kein_interesse'))");
```

- **Kein** Postgres-natives `CREATE TYPE ... AS ENUM` — `ALTER TYPE`-Einschränkungen
  (kein Entfernen von Werten, Transaktions-Sperren bei manchen Änderungen) passen
  schlecht zu einem Projekt, dessen Wertelisten sich noch durch Grill-Entscheidungen
  weiterentwickeln (z. B. D-089 steht noch aus).
- Wertelisten-Änderung = neue Migration, die den CHECK-Constraint droppt und neu
  anlegt (`ALTER TABLE ... DROP CONSTRAINT ...` + `ADD CONSTRAINT ...`). Das ist
  gewollter Reibungspunkt — jede Änderung ist ohnehin eine bewusste `D-NNN`-Entscheidung.
- Laravel-seitig: PHP-`enum`-Klasse je Feld (`App\Enums\...` bzw.
  `Dormed\Core\Modules\<Modul>\Enums\...`), Model-Cast `'status' =>
  ServiceContractStatus::class`. Die PHP-Enum-Werte und der DB-CHECK-Constraint
  müssen **synchron** gehalten werden — beide werden aus derselben `D-NNN`-Quelle
  in `grill-log.md` gepflegt.

## Naming Conventions

- Tabellen: `snake_case`, Plural (`companies`, `service_contracts`).
- Spalten: `snake_case`, Singular.
- Fremdschlüssel: `<singular_tabellenname>_id` (`company_id`, `service_contract_id`).
- Pivot-Tabellen: alphabetisch sortierte Singular-Namen (`company_contacts` ist die
  Ausnahme — eigenständige Entität mit eigenen Feldern, kein reines Pivot; echte
  Pivots ohne Zusatzfelder: `payment_invoice`, `service_case_devices`; `maintenance_devices`
  bereits Entität mit Zusatzfeldern → eigener Name statt `maintenances_devices`).
- Boolesche Spalten: `is_*` / `has_*`-Präfix wo sinnvoll (`is_primary`, `is_active`).
- Zeitstempel: `*_at` (Timestamp) vs. `*_date`/`*_on` (reines Datum ohne Uhrzeit) —
  konsistent nach Feldbedeutung aus den `04-domain/*.md`-Specs übernehmen.

## Primärschlüssel (D-095)

**Auto-Increment `bigint` (Laravel-Standard `id()`)**, kein UUID.

Begründung: Alle fachlichen Nummernkreise mit externer Sichtbarkeit (Rechnung,
Vertrag, Opportunity, ServiceCase, Maintenance) haben bereits ein eigenes,
fachliches `number`-Feld (D-067 u. a.) — der DB-PK ist rein intern und muss nicht
extern vorhersagbar/nicht-erratbar sein. Auto-Increment ist kompakter (8 Byte vs.
16 Byte UUID), schneller zu indizieren, und Laravels Tooling (Eloquent, Migrations)
ist darauf optimiert. Kein Multi-Master-/Offline-Sync-Bedarf, der UUIDs erfordern
würde (D-041: Offline-Wartungsbericht ist bewusst ein späterer Slice, keine
aktuelle Anforderung).

## Geldbeträge

**`decimal(12,2)` einheitlich** für alle Geldbeträge (vereinheitlicht — einzelne
`04-domain/*.md`-Tabellen nannten uneinheitlich `decimal(10,2)`; wird bei
Migrationserstellung auf `decimal(12,2)` korrigiert, deckt auch größere Beträge
wie `Opportunity.customer_budget` ab, ohne je Feld einzeln zu entscheiden).
Prozentsätze/Stundensätze-Prozent: `decimal(5,2)`.

## Soft-Delete / Audit (Querschnitt, unverändert aus D-018/D-023)

- `SoftDeletes` (`deleted_at`) auf **jedem** fachlichen Modell inkl. Pivots mit
  Zusatzfeldern (`company_contacts`, `maintenance_devices`, …) — nicht auf reinen
  Pivots ohne Zusatzfelder (`payment_invoice`, `service_case_devices`).
- `created_by` / `updated_by` via `TracksBlame` (portiert nach `packages/core/src/Support/`).
- `created_at` / `updated_at` Standard-Laravel-Timestamps überall.

## Indizierung

- Jede Fremdschlüssel-Spalte indiziert (Laravel legt das bei `foreignId()`
  automatisch an).
- Zusätzlich zu indizieren: `number`-Spalten (unique Index, unabhängig vom PK),
  `postal_code_from`/`postal_code_to` in den drei Territory-Tabellen
  (`travel_zones`, `service_territories`, `sales_territories`, D-084) für die
  Bereichs-Lookups.
- Volltextsuche (Company-Name, Person-Name) → kein Index-Vorschlag jetzt, erst bei
  konkretem Performance-Bedarf (ADR-010).

## Offene Punkte

| # | Punkt | Wohin |
| --- | --- | --- |
| 1 | RLS-Policies (ADR-007 „vor produktiver Aktivierung spezifizieren") | eigener Security-Slice |
| 2 | Materialized View für `ServiceContract`-Fälligkeitsübersicht (falls Performance es erfordert) | bei Umsetzung |
