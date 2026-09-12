# Domäne — Billing: Rechnungen / Zahlungen / Mahnwesen

Autoritative deklarative Spec. Entscheidungen **D-056 – D-077**
([`../07-decisions/grill-log.md`](../07-decisions/grill-log.md)). Greenfield, kein
Legacy-XML.

Modul: `packages/core/src/Modules/Billing/` (Namespace `Dormed\Core\Modules\Billing\`,
`depends_on: [Core, Service, Sales]`, ADR-013).

## Grundsatz

- **Kein Sammelrechnungslauf, keine Bündelungs-Engine.** Die größte „Sammelrechnung"
  ist eine `Maintenance` (mehrere Geräte, ein Tag, eine Praxis, D-059) → eine
  Rechnung (D-066).
- **`Invoice` hat genau eine Quelle**: `Maintenance` **oder** `ServiceCase` **oder**
  eine gewonnene `Opportunity` **oder** manuell (begrenzt, permission-gated,
  D-057).
- **Rechnungsempfänger** kommt aus `Company.billing_company_id` (D-004/D-066) und wird
  bei Rechnungsstellung **eingefroren** (Name, Adresse, USt-ID) — eine spätere
  Adressänderung an der Company darf die historische Rechnung nicht verändern
  (`DOMAIN.md` Billing-Prinzip).
- **Sage/KHK ist komplett abgelöst** (D-068, revidiert D-056/D-009) — der Monolith
  übernimmt Fakturierung, Zahlungsabgleich und Mahnwesen vollständig selbst, ohne
  Sync-Brücke. Vollwertige Buchhaltung (Kontenrahmen, GuV/BWA) bleibt extern beim
  Steuerberater-Büro; der Monolith liefert nur einen periodischen DATEV-Export
  (D-075).
- **e-Rechnungs-fähig** (D-058/D-074): das Datenmodell trägt alle ZUGFeRD/XRechnung-
  Pflichtfelder; der Format-Export selbst ist ein späterer ROADMAP-Slice.

```text
Maintenance ──┐
ServiceCase ──┼──► Invoice (1 Quelle, eingefroren) ──► InvoiceItem (n)
Opportunity ─┘         │
   manuell ────────────┘
                        │
                        ├── Storno ──► Gutschrift (eigener Beleg, D-069)
                        │
                        └── Payment (n:m über payment_invoice, D-071)
```

---

## Invoice

| Feld | Typ | Null | Notiz |
| --- | --- | :-: | --- |
| `type` | enum `rechnung` \| `gutschrift` | – | D-069 |
| `number` | string | – | unique, Nummernkreis **je Jahr und Typ**: `RE-2026-000123` / `GS-2026-000045` (D-067) |
| `status` | enum `entwurf` \| `gestellt` \| `versendet` \| `storniert` | – | Dokument-Lifecycle. `gestellt` = Nummer vergeben, Inhalt eingefroren |
| `payment_status` | enum `offen` \| `teilbezahlt` \| `bezahlt` \| `ueberfaellig` | – | **abgeleitet** aus zugeordneten `payments` + `due_at` (D-071) — orthogonal zu `status` |
| `dunning_level` | enum `keine` \| `zahlungserinnerung` \| `mahnung_1` \| `mahnung_2` | – | default `keine`, manuell eskaliert, keine Sprünge (D-072) |
| `last_dunning_sent_at` | datetime | ✓ | |
| `source_type` / `source_id` | morph | ✓ | `Maintenance` \| `ServiceCase` \| `Opportunity` \| `null` (manuell) — D-057 |
| `credited_invoice_id` | FK → `invoices` | ✓ | nur bei `type = gutschrift`, Rückverweis aufs Original (D-069) |
| `company_id` | FK → `companies` | – | Leistungsempfänger (die servicierte/belieferte Praxis) |
| `recipient_company_id` | FK → `companies` | – | Rechnungsempfänger zum Zeitpunkt der Erstellung (= `company.billing_company_id` oder `company_id` selbst) |
| `recipient_name`, `recipient_street`, `recipient_postal_code`, `recipient_city`, `recipient_country_code`, `recipient_vat_id` | string | ✓ | **eingefroren** bei Erstellung — historische Rechnung bleibt unverändert, egal was sich an der Company später ändert |
| `issued_at` | date | ✓ | Rechnungsdatum, gesetzt beim Übergang zu `gestellt` |
| `service_date` | date | – | Leistungsdatum, **automatisch** aus `Maintenance.performed_at` / `ServiceCase.finalized_at` (D-070) |
| `due_at` | date | ✓ | `issued_at` + `payment_terms_days` |
| `payment_terms_days` | integer | – | Zahlungsziel |
| `payment_means` | enum `ueberweisung` \| `lastschrift` | – | D-074 |
| `sepa_mandate_reference` | string | ✓ | nur bei `lastschrift` |
| `routing_id` | string | ✓ | Leitweg-ID, nur B2G-Sonderfälle (D-074) |
| `currency` | string(3) | – | default `EUR` |
| `net_total`, `tax_total`, `gross_total` | decimal(12,2) | – | berechnet bei `gestellt`, danach unveränderlich |
| `sent_at` | datetime | ✓ | Versanddatum |
| `cancelled_at` | datetime | ✓ | |
| `notes` | text | ✓ | |

**Regeln**
- Eine Rechnung mit `status = gestellt` oder höher ist **unveränderlich** (Positionen,
  Beträge, Empfänger-Snapshot). Änderungsbedarf → Storno + neue Rechnung.
- `payment_status = ueberfaellig` wird abgeleitet (`due_at` überschritten, nicht
  `bezahlt`/`teilbezahlt` mit vollem Betrag), nicht manuell gesetzt.
- `dunning_level` darf serverseitig nur um eine Stufe steigen, nie übersprungen werden
  (Business-Workflow-Guard, analog `IDENTITY_RBAC.md`).

---

## InvoiceItem

Positionen — bei Rechnungserstellung aus `Service.line_items` bzw.
`Sales.opportunity_items` übernommen und **eigenständig eingefroren** (keine
Live-Referenz mehr auf die Quelle).

| Feld | Typ | Null | Notiz |
| --- | --- | :-: | --- |
| `invoice_id` | FK → `invoices` | – | |
| `position` | smallint | – | |
| `description` | string | – | |
| `quantity` | decimal(10,2) | – | |
| `unit` | string | ✓ | Stk / Std / Pauschale (Anzeige) |
| `unit_code` | string | ✓ | UN/ECE-Recommendation-20-Code (z. B. `C62`, `HUR`) — XRechnung-Pflichtfeld |
| `unit_price` | decimal(12,2) | – | netto |
| `net_amount` | decimal(12,2) | – | `quantity × unit_price` |
| `tax_category` | enum `standard_19` \| `reverse_charge` \| `export_tax_free` \| `other_tax_free` | – | je Position, nicht am Kopf (D-074) |
| `tax_rate` | decimal(5,2) | – | z. B. `19.00` / `0.00` |
| `tax_amount` | decimal(12,2) | – | |
| `is_chargeable` | boolean | – | **neu (D-129)**, default `true` |
| `non_charge_reason` | enum `garantie` \| `kulanz` \| `vertrag` | ✓ | **neu (D-129)**, Pflicht wenn `is_chargeable = false` |

Bei `type = gutschrift`: identische Positionen wie das Original, Beträge negiert
(D-069).

**Nicht berechnete Positionen (D-129).** Garantie-, Kulanz- und
Full-Service-Leistungen werden **auf der Rechnung ausgewiesen**, nicht
weggelassen: die Position bleibt mit Menge und Beschreibung erhalten,
`net_amount` und `tax_amount` sind `0`, und sie zählt **nicht** in
`net_total`/`tax_total`/`gross_total`. Der Kunde sieht damit, welche Leistung er
erhalten und was sie ihn nicht gekostet hat; der Grund am Datensatz macht
auswertbar, was Kulanz und Garantie im Jahr gekostet haben. Die
Unveränderlichkeit nach `gestellt` (D-093) gilt unverändert.

---

## Payment

| Feld | Typ | Null | Notiz |
| --- | --- | :-: | --- |
| `received_at` | date | – | |
| `amount` | decimal(12,2) | – | |
| `currency` | string(3) | – | default `EUR` |
| `bank_reference` | string | ✓ | Verwendungszweck / End-to-End-Referenz aus dem Kontoauszug |
| `import_batch_id` | FK → `bank_statement_imports` | ✓ | |
| `status` | enum `nicht_zugeordnet` \| `teilweise_zugeordnet` \| `zugeordnet` | – | D-071 |

**Matching (D-071):** automatisch per Rechnungsnummer im `bank_reference`; kein
eindeutiger Treffer → `nicht_zugeordnet`, manuelle Zuordnung durch die Buchhaltung.

### `payment_invoice` (Pivot, n:m)

| Feld | Typ | Notiz |
| --- | --- | --- |
| `payment_id` | FK → `payments` | |
| `invoice_id` | FK → `invoices` | |
| `amount` | decimal(12,2) | Anteil dieser Zahlung an dieser Rechnung |

Bildet sowohl Sammelüberweisungen (eine Zahlung deckt mehrere Rechnungen — z. B.
Managementgesellschaft, D-004/D-066) als auch Teilzahlungen (mehrere Zahlungen auf
eine Rechnung) ab.

### `bank_statement_imports`

| Feld | Typ | Notiz |
| --- | --- | --- |
| `imported_at` | datetime | |
| `format` | string | z. B. `camt053` / `mt940` / `csv` / `manual` — **Parser austauschbar**, Format final offen (D-071) |
| `imported_by` | FK → `users` | |

---

## Mahnwesen (D-072)

Drei Stufen, **manuell** ausgelöst, serverseitig erzwungene Reihenfolge:

```text
keine → zahlungserinnerung → mahnung_1 → mahnung_2 → (Inkasso, außerhalb des Systems)
```

Keine automatische Mahngebühr- oder Verzugszins-Berechnung (§288 BGB) jetzt — späterer
Ausbau bei Bedarf (ADR-010). Mahn-**Dokumente** (PDF) → Bereich Documents.

---

## Storno / Gutschrift (D-069)

Nur **Vollstorno**: erzeugt automatisch einen `type = gutschrift`-Beleg mit
`credited_invoice_id` → Original, identische Positionen mit negierten Beträgen, eigener
Nummernkreis (`GS-…`). Original-Status → `storniert`, bleibt unverändert erhalten.
Keine Teil-Gutschriften jetzt.

---

## Rechnungsquellen (D-057)

| Quelle | Positionen kommen aus |
| --- | --- |
| `Maintenance` (abgeschlossen + bestätigt) | Fahrtzone (`Company.travel_zone`) 1× + Σ `maintenance_devices` (`maintenance_fee_snapshot` + `line_items`), D-059/D-061/D-065 |
| `ServiceCase` (abgeschlossen + bestätigt) | Fahrtzone 1× je Anfahrt (D-076) + ad-hoc `line_items` (Teile/Arbeitszeit nach Tarifstufe, D-060/D-063) |
| gewonnene `Opportunity` (D-053) | `opportunity_items` |
| manuell | freie Positionen, `billing.invoices.create_manual`-Permission, Ausnahme nicht Norm |

Keine separate wiederkehrende Vertragsgebühr-Rechnung — die Grundgebühr läuft über die
Wartungsrechnung mit.

---

## DATEV-Export (D-075)

Periodischer Export von Rechnungen + Zahlungen als DATEV-fähige Buchungssätze für das
externe Steuerberater-Büro. Kein Rückfluss. Braucht perspektivisch eine
Erlöskonto-Zuordnung je `InvoiceItem` bzw. Positionsart — Kontenrahmen/Mapping-Detail
noch offen (siehe unten).

---

## Offene Punkte

| # | Punkt | Wohin |
| --- | --- | --- |
| 1 | Bank-Import-Format final (CAMT.053 vs. MT940 vs. beides) | Rückfrage Nutzer / Bank-Recherche |
| 2 | Kontenrahmen/Erlöskonto-Zuordnung für DATEV-Export | Bei Umsetzung, mit Steuerberater abstimmen |
| 3 | Mahngebühren/Verzugszinsen automatisiert | Späterer Slice (ADR-010) |
| 4 | `sepa_mandate_reference` auf `Company` oder `Invoice`? | Detail bei Umsetzung |
| 5 | `debitor_number`-Format/Vergabe (manuell vs. fortlaufend) | Detail bei Umsetzung (D-073) |
| 6 | Exakter Snapshot-Zeitpunkt der Rechnungsadresse (Entwurf-Anlage vs. `gestellt`) | Detail bei Umsetzung |
| 7 | Teil-Gutschriften (einzelne Positionen) | Späterer Slice, falls fachlich gefordert |
