# Domäne — Sales (Verkaufschancen / Opportunity)

Autoritative deklarative Spec. Entscheidungen **D-051 – D-055**
([`../07-decisions/grill-log.md`](../07-decisions/grill-log.md)). Feld-Herkunft:
[`../00-legacy/Verkaufschancen/Verkaufschancen-Felder.md`](../00-legacy/Verkaufschancen/Verkaufschancen-Felder.md) (34 F.).

Modul: `packages/core/src/Modules/Sales/` (Namespace `Dormed\Core\Modules\Sales\`, `depends_on: [Core, Inventory]`, ADR-013 — Inventory neu durch D-099/D-117, Katalogbezug der Positionen).

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
| `lead_source` | enum `messe` \| `empfehlung` \| `website_anfrage` \| `kaltakquise` \| `bestandskunde_cross_upsell` \| `sonstige` | ✓ | ← `Source` (D-086) |
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

**Abgeleitet** (nicht gespeichert, D-052): je Position `net_line_amount` =
`quantity × unit_price × (1 − discount_percent/100)`; `total_amount` = Σ
`net_line_amount`, `weighted_amount` = `total_amount × probability/100`,
`marginal_return` (Deckungsbeitrag) = Σ (`quantity × (unit_price × (1 −
discount_percent/100) − unit_cost)`).

## OpportunityItem

| Feld | Typ | Null | Notiz |
| --- | --- | :-: | --- |
| `opportunity_id` | FK | – | cascade |
| `position` | smallint | – | |
| `article_id` | FK → `articles` | ✓ | **ersetzt `product_id` (D-099)** — Artikelkatalog, siehe [`INVENTORY.md`](INVENTORY.md) |
| `offering_id` | FK → `offerings` | ✓ | **neu (D-117)** — Leistungskatalog, paralleler Katalog neben den Artikeln |
| `description` | string | – | bei Katalogpositionen vorbelegt, bei freien Positionen (`diverse`, D-118) frei |
| `quantity` | decimal(10,2) | – | |
| `unit_price` | decimal(12,2) | – | netto, **gesnapshottet** aus `Article.sale_price` / `Offering.sale_price` (D-104) |
| `unit_cost` | decimal(12,2) | ✓ | für Deckungsbeitrag — gesnapshottet aus `Article.purchase_price` (D-104) |
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
| 1 | `stage`/`probability`-Phasenliste (Legacy `DistributionPhase` inkl. %) | Rückfrage Nutzer, folgt (D-089) |
| 2 | ~~`DORMEDABTEILUNG`~~ | ✅ verworfen (D-087) |
| 3 | ~~`lead_source`-Enum-Werte~~ | ✅ gelöst (D-086) |
| 4 | ~~`discount`/`unit_cost` an `OpportunityItem`~~ | ✅ beide nötig — `unit_cost` für `marginal_return`, `discount_percent` fließt in `net_line_amount` (D-052-Formel präzisiert) |
| 5 | ~~Produktkatalog (`products`)~~ | ✅ gelöst — `articles` + `offerings`, [`INVENTORY.md`](INVENTORY.md) (D-099/D-117) |
| 6 | Angebots-PDF-Erzeugung | Bereich Dokumente |
| 7 | Sales-Aktivitäten (Anrufe/Mails/Notizen-Timeline) | Bereich Communication (später) |
