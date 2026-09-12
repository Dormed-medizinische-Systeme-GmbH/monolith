# Domäne — Sales (Verkaufschancen / Opportunity)

Autoritative deklarative Spec. Entscheidungen **D-051 – D-055**
([`../07-decisions/grill-log.md`](../07-decisions/grill-log.md)). Feld-Herkunft:
[`../09-legacy/xml/Verkaufschancen-Felder.md`](../09-legacy/xml/Verkaufschancen-Felder.md) (34 F.).

Modul: `packages/core/src/Modules/Sales/` (Namespace `Dormed\Core\Modules\Sales\`, `depends_on: [Core]`, ADR-013).

## Grundsatz

Eine `Opportunity` gehört zu einer `Company` und **ist zugleich das Angebot**
(D-052) — kein separates Quote-Objekt. Eine einzige Pipeline-Achse `stage` mit
Endzuständen `gewonnen` / `verloren` (D-051).

## Opportunity

| Feld | Typ | Null | Notiz |
| --- | --- | :-: | --- |
| `company_id` | FK → `companies` | – | ← `AccountInformation` |
| `number` | string | – | Nummernkreis ← `OPPORTUNITYNUMBER` |
| `title` | string | – | |
| `stage` | enum | – | `lead` · `qualifiziert` · `angebot` · `verhandlung` · `gewonnen` · `verloren` (Werte offen) |
| `lost_reason` | string | ✓ | nur bei `stage = verloren` |
| `probability` | smallint | – | % 0–100 (← `Probability`) |
| `owner_id` | FK → `users` | ✓ | verantwortliche Person (← `PersonInCharge`); Default kommt von `Company.responsible_sales_id`, das wiederum aus `sales_territories` per PLZ vorbelegt wird (D-084) — manuell überschreibbar |
| `customer_budget` | decimal(12,2) | ✓ | ← `BUDGET` |
| `payment_terms` | string | ✓ | ← `ZAHLUNGKONDITIONEN` |
| `lead_source` | enum/Lookup | ✓ | Werte **offen** (← `Source`) |
| `opened_at` | date | – | ← `Start_dt` |
| `expected_close_at` | date | ✓ | ← `end_dt` |
| `competitor` | string | ✓ | ← `Competitors` / `DO_VC_MITBEWERBER` |
| `competitor_note` | text | ✓ | ← `CompetitorNotes` / `DO_VC_MITBEWERBER_NOTIZ` |
| `cooperation_type` | string | ✓ | ← `KOOPERATION` |
| `cooperation_partner` | string | ✓ | ← `KOOPERATIONSPARTNER` |
| `notes` | text | ✓ | ← `Keyword` / `Notes2` |

`SoftDeletes` (D-018), `TracksBlame`.

**Beziehungen:** `company()` `belongsTo`, `owner()` `belongsTo` `User`,
`items()` `hasMany` `OpportunityItem`, `appointments()` `morphMany` (D-046),
`serviceContract()` `hasOne` `ServiceContract` (nullable Rückverweis, D-053).

**Abgeleitet** (nicht gespeichert, D-052): `total_amount` = Σ `items`,
`weighted_amount` = `total_amount × probability/100`, `marginal_return`
(Deckungsbeitrag) = Σ (`quantity × (unit_price − unit_cost)`).

## OpportunityItem

| Feld | Typ | Null | Notiz |
| --- | --- | :-: | --- |
| `opportunity_id` | FK | – | cascade |
| `position` | smallint | – | |
| `product_id` | FK → `products` | ✓ | Produktkatalog (Bereich Inventory) — bis dahin `description` |
| `description` | string | – | |
| `quantity` | decimal(10,2) | – | |
| `unit_price` | decimal(12,2) | – | netto |
| `unit_cost` | decimal(12,2) | ✓ | für Deckungsbeitrag |
| `discount_percent` | decimal(5,2) | ✓ | |

## Workflow

`stage`-State-Machine (Übergänge server-seitig, IDENTITY_RBAC „Business-Workflow"):
- Vorwärts durch die offenen Stufen; `gewonnen` / `verloren` sind terminal.
- `verloren` erfordert `lost_reason`.
- **`gewonnen` erzeugt nichts automatisch** (D-053). Der Folge-`ServiceContract`
  wird manuell angelegt und über `service_contracts.opportunity_id` zurückverknüpft.

## Verworfen

`VENDORINFORMATION2/3` · `AttorneyInFact` · `CurrencyNat` ·
`Alarm` (→ `Appointment`) · `LASTCONTACTINSALESPROCESS` (abgeleitet) ·
`OppTotalAmount` / `RelativeAmount` / `MARGINALRETURN*` (abgeleitet) ·
`ProductPositionsDisplay` (→ `items`) · `DO_SV_VORGANSSART` (tote `DO_SV*`-Familie, D-001).

`VCAWKZ` (Legacy Territory-Feld) selbst bleibt verworfen (die konkrete Legacy-Codierung
wird nicht übernommen) — das **Konzept** eines PLZ-Gebietsmodells für Vertrieb kommt
aber über `sales_territories` zurück (D-084, revidiert D-054s „kein Territory-Modell").

## `sales_territories` (neu, D-084 — revidiert D-054)

Eigenständige, unabhängige PLZ-Gebietstabelle (echte Von-Bis-Bereiche, analog
`travel_zones`/`service_territories` in `SERVICE.md`, jeweils **eigene** Grenzen):

| Feld | Typ | Notiz |
| --- | --- | --- |
| `postal_code_from`, `postal_code_to` | string | PLZ-Bereich |
| `default_sales_rep_id` | FK → `users` | |
| `is_active` | boolean | |

Schlägt `Company.responsible_sales_id` bei Company-Anlage automatisch vor (D-016/D-084)
— **kein** Autorisierungsbezug, weiterhin rein informativ, manuell überschreibbar.

## Offene Punkte

| # | Punkt | Wohin |
| --- | --- | --- |
| 1 | `stage`-, `lead_source`-Enum-Werte | Rückfrage Nutzer |
| 2 | `DORMEDABTEILUNG` („Bereich") — Produktbereich vs. Abteilung, behalten? | Rückfrage Nutzer |
| 3 | `discount` / `unit_cost` an `OpportunityItem` — wirklich nötig? | Rückfrage Nutzer |
| 4 | Produktkatalog (`products`) | Bereich Inventory |
| 5 | Angebots-PDF-Erzeugung | Bereich Dokumente |
| 6 | Sales-Aktivitäten (Anrufe/Mails/Notizen-Timeline) | Bereich Communication (später) |
