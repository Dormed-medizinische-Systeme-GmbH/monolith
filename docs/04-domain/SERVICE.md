# Domäne — Service: Device / ServiceContract / Maintenance / ServiceCase

Autoritative deklarative Spec. Entscheidungen **D-034 – D-045**
([`../07-decisions/grill-log.md`](../07-decisions/grill-log.md)). Feld-Herkunft:
[`../09-legacy/xml/Servicevertraege-Zuordnung.md`](../09-legacy/xml/Servicevertraege-Zuordnung.md),
[`../09-legacy/xml/Tickets-Zuordnung.md`](../09-legacy/xml/Tickets-Zuordnung.md).
Prinzipien: [`../05-modules/SERVICE.md`](../05-modules/SERVICE.md).

Modul: `app/Modules/Service/` (Namespace `App\Modules\Service\`, `depends_on: [Core]`).

## Grundsatz

Der Legacy-Servicevertrag (83 F.) und das Legacy-Ticket (112 F.) vermischen
Vertrag + Gerät + Komponenten + Netzwerk-Config + Wartungszyklus + Wartungs-
Checkliste + Messprotokoll + Rechnungspositionen. Zerlegung:

```
ServiceContract 1──1 Device 1──n DeviceComponent
       │                │
       │ 1              │ 1
       n                n
   Maintenance      (ServiceCase n, unabhängig)
       │ 1
       1
  MaintenanceReport ──> ChecklistTemplate (version-pinned)
       │
       0..1
  MeasurementProtocol
```

**Wartung ≠ Servicefall** — vollständig getrennte Modelle, getrennte Workflows
(D-039). Der Ursprung bestimmt den zulässigen Prozess.

---

## Device

Das medizintechnische System (Ultraschallgerät …). **Existiert nur über einen
ServiceContract** (D-035): `service_contract_id` required + unique (1:1).
Steht an einer `Location` (D-007).

| Feld | Typ | Null | Notiz |
| --- | --- | :-: | --- |
| `service_contract_id` | FK → `service_contracts` | – | unique (1:1) |
| `location_id` | FK → `locations` | – | Gerätestandort |
| `manufacturer` | string | – | ← `SYSTEM_HERSTELLER` |
| `model_name` | string | ✓ | Kategorie/Bezeichnung ← `SYSTEM_BEZEICHNUNG` |
| `article_number` | string | ✓ | ← `SYSTEM_ARTIKELNUMMER` |
| `serial_number` | string | – | ← `SYSTEM_SERIENNUMMER` |
| `year_built` | string(8) | ✓ | ← `SYSTEM_BAUJAHR` |
| `delivered_on` | date | ✓ | ← `SYSTEM_AUSLIEFERUNGSDATUM` |
| `operating_system` | string | ✓ | ← `SYSTEM_OS` |
| `software_version` | string | ✓ | ← `SYSTEM_SW` |
| `options` | text | ✓ | ← `SYSTEM_OPTIONEN` |
| — Netzwerk / DICOM (Felder direkt am Device, D-034) — | | | |
| `ip_address` | string | ✓ | ← `SYSTEM_IPADRESSE` |
| `mac_address` | string(17) | ✓ | ← `SYSTEM_MACADRESSE` |
| `gateway` | string | ✓ | |
| `dhcp` | boolean | – | default `false` |
| `system_user` | string | ✓ | ← `SYSTEM_BENUTZER` |
| `system_password` | string (verschlüsselt) | ✓ | ← `SYSTEM_PASSWORD` |
| `storage_ae_title` | string | ✓ | DICOM Storage ← `SYSTEM_STORAGE_TITLE` |
| `storage_port` | integer | ✓ | ← `SYSTEM_STORAGE_PORT(_NEW)` |
| `worklist_ae_title` | string | ✓ | ← `SYSTEM_WORKLISTE_TITLE` |
| `worklist_port` | integer | ✓ | ← `SYSTEM_WORKLIST(E)_PORT(_NEW)` |

Der `DO_SVV_PRAXISSW*`-Block aus CORE.md (D-001-Ausnahme, 14 Felder Praxis-IT) →
in Runde „Device/Praxis-IT" noch zuzuordnen: teils Device (dieses hier), teils
`Location` (Praxis-Netz allgemein). **Offen.**

---

## DeviceComponent

Zubehör/Baugruppen eines Device, `n` je Device, **typisiert** (D-034). Ersetzt
`SONDE1..5_*`, `PRINTER_*`, `WAGEN_*`, `SONOGDT_*`.

| Feld | Typ | Null | Notiz |
| --- | --- | :-: | --- |
| `device_id` | FK → `devices` | – | cascade |
| `type` | enum `probe` \| `printer` \| `cart` \| `gdt` \| `other` | – | Sonde / Drucker / Wagen / SonoGDT |
| `article_number` | string | ✓ | |
| `description` | string | ✓ | |
| `serial_number` | string | ✓ | |
| `license` | string | ✓ | nur `gdt` (← `SONOGDT_LIZENZ`) |
| `position` | smallint | ✓ | Reihenfolge (Sonde 1..5) |

---

## ServiceContract

Die Servicevereinbarung für genau ein Device.

| Feld | Typ | Null | Notiz |
| --- | --- | :-: | --- |
| `company_id` | FK → `companies` | – | Vertragspartner |
| `number` | string | – | Nummernkreis (← `GWAUTONUM`) |
| `contract_type` | enum | – | Vertragsart — Werte **offen** (Full-Service / Wartung / …) |
| `status` | enum | – | `aktiv` · `gekuendigt` · `ausgelaufen` · `entwurf` — Werte **offen** |
| `signed_on` | date | ✓ | ← `VERTRAGS_DATUM` |
| `cancelled_at` | date | ✓ | Kündigungsdatum |
| `full_service_ends_at` | date | ✓ | ← `VERTRAG_FS_ENDE` |
| `maintenance_interval_months` | smallint | – | 12 / 6 / 4 / 3 … (D-037; deckt „Mehrfachwartung", D-042) |
| `next_due_at` | date | ✓ | **abgeleitet**, neu berechnet bei Wartungsabschluss (D-037) |
| `maintenance_price` | decimal(10,2) | ✓ | nur aktuell (D-036) |
| `travel_flat_rate` | decimal(10,2) | ✓ | Fahrtzonenpauschale, aktuell (D-036/D-020) |
| `payment_terms` | string | ✓ | ← `VERTRAG_ZAHLUNGSKONDITIONEN` |
| `billing_company_id` | FK → `companies` | ✓ | abw. Rechnungsempfänger (← `ABWEICHENDE_RECHNUNG`, D-004) |
| `warranty_manufacturer_until` | date | ✓ | ← `GARANTIE_HERSTELLER` |
| `warranty_customer_until` | date | ✓ | ← `GARANTIE_KUNDE` |
| `warranty_insurance` | boolean | – | ← `VERTAG_GARANTIEVERSICHERUNG` |
| `warranty_insurance_until` | date | ✓ | |
| `electronics_insurance` | boolean | – | ← `VERTRAG_ELEKTRONIKVERSICHERUNG` |
| `electronics_insurance_where` | string | ✓ | ← `VERTRAG_ELEKTRONIKVERS_WO` |
| `leasing` | boolean | – | ← `VERTRAG_LEASING` |
| `leasing_where` | string | ✓ | ← `VERTRAG_LEASING_WO` |
| `notes` | text | ✓ | ← `KEYWORD` / `NOTES2` |

**Verworfen** (D-036/D-037/D-042): `KOSTEN_*_VERTRAG` / `_EINMAL`, `PREISANPASSUNG*`,
`ERSTEWARTUNG`, `NAECHSTEWARTUNG`, `MONAT`, `MEHRFACHWARTUNG`. Adress-/Firmen-
Dubletten (`FIRMA`, `ORT`, `PLZ`, `DEBITORENNUMMER`, `VERANTWORTLICHER_SERVICE`)
→ leben auf `Company`.

---

## Maintenance

Eine einzelne Wartungsinstanz aus dem Zyklus eines `ServiceContract`.

| Feld | Typ | Null | Notiz |
| --- | --- | :-: | --- |
| `service_contract_id` | FK | – | |
| `number` | string | – | Nummernkreis |
| `status` | enum | – | State-Machine (s. u.) |
| `planned_due_at` | date | – | fachlich ≠ performed (SERVICE.md) |
| `performed_at` | datetime | ✓ | tatsächliche Durchführung — Basis `next_due_at` (D-037) |
| `finalized_at` | datetime | ✓ | ← `TICKET_DATUM_GESCHLOSSEN` |
| `assigned_technician_id` | FK → `users` | ✓ | ← `TICKET_TICKETUSERNAME` |
| `work_performed` | text | ✓ | ← `TICKET_DURCHGEFUEHRTEARBEITEN` |
| `notes` | text | ✓ | |

**Beziehungen:** `report()` `hasOne` `MaintenanceReport`, `measurementProtocol()`
`hasOne` `MeasurementProtocol`, `lineItems()` `morphMany`.

**State-Machine** (Werte offen — Vorschlag): `geplant → zugewiesen → in_durchfuehrung
→ kunde_bestaetigt → abgeschlossen → rechnung_freigegeben`. Übergänge server-seitig
erzwungen; kein Sprung `geplant → rechnung_freigegeben` (SERVICE.md, IDENTITY_RBAC
„Business-Workflow").

---

## MaintenanceReport

Der strukturierte Wartungsbericht (D-038). **Kein** `visual_check_1..N`.

| Feld | Typ | Null | Notiz |
| --- | --- | :-: | --- |
| `maintenance_id` | FK | – | 1:1 |
| `checklist_template_id` | FK → `checklist_templates` | – | version-gepinnt |
| `checklist_template_version` | integer | – | eingefroren |
| `outcome` | enum `keine_maengel` \| `maengel` \| `maengel_gefahr` \| `ausserbetriebnahme` | – | ← `TICKET_ABSCHLUSS_1..4` |
| `defects_note` | text | ✓ | ← `TICKET_ABSCHLUSS_MAENGEL` |
| `operating_status` | enum `in_betrieb` \| `eingeschraenkt` \| `ausser_betrieb` | – | SERVICE.md Betriebsstatus |
| — Kundenbestätigung (D-045) — | | | |
| `signature_image` | binär/Datei | ✓ | |
| `signer_name` | string | ✓ | |
| `signed_at` | datetime | ✓ | |
| `unconfirmed_reason` | string | ✓ | wenn ohne Unterschrift abgeschlossen |

**Ergebnisse je Prüfpunkt** — `maintenance_report_items`: `report_id`,
`template_item_id`, `result` (`ok` \| `nicht_ok` \| `na`), `note`. Speist sich aus
`TICKET_SICHTKONTROLLE_1..10`, `_FUNKTIONSKONTROLLE_1..13`, `_WARTUNGSARBEITEN_1..8`.

Fotos/Nachweise → `documents` (Dokumenten-Bereich), polymorph am Report.

---

## ChecklistTemplate

Versionierte Prüfkatalog-Vorlage (D-038).

- `checklist_templates`: `name`, `device_category` (optional — Templates je
  Systemklasse, s. offene Punkte), `version` (integer), `is_active`,
  `published_at`.
- `checklist_template_items`: `template_id`, `section` (enum `sichtkontrolle` ·
  `funktionskontrolle` · `wartungsarbeiten`), `position`, `label`,
  `input_type` (`bool` — vorerst nur ja/nein/na).
- Neue Version = neuer Datensatz; laufende Berichte bleiben an ihrer Version.

---

## MeasurementProtocol

STK / Konstanzprüfung nach **DIN EN 62353** — feste Struktur, nicht Template-
getrieben (D-044). 0..1 je `Maintenance` (bei Bedarf auch `ServiceCase`).

| Feld | Typ | Null | Notiz |
| --- | --- | :-: | --- |
| `maintenance_id` | FK | – | |
| `protection_class` | enum `sk1` \| `sk2` | – | ← `TICKET_MESSWERTE_SCHUTZKLASSE` |
| `test_method` | string | ✓ | ← `_ART` |
| `test_equipment` | string | ✓ | ← `_PRUEFMITTEL` |
| `sk1_iega` … `sk1_uln` | decimal | ✓ | 6 Werte ← `_SK1_*` |
| `sk2_iega` … `sk2_uln` | decimal | ✓ | 6 Werte ← `_SK2_*` |
| `evaluation` | enum `ok` \| `nicht_ok` | – | Grenzwert-Bewertung |
| `is_constancy_test` | boolean | – | Konstanzprüfung für KV ← `TICKET_ABSCHLUSS_KP` |

---

## ServiceCase

Störung / Serviceeinsatz — **nicht** aus dem Wartungszyklus (D-039).

| Feld | Typ | Null | Notiz |
| --- | --- | :-: | --- |
| `device_id` | FK → `devices` | – | betroffenes Gerät (← `TICKET_SYSTEM`) |
| `number` | string | – | Nummernkreis |
| `reported_by` | FK → `company_contacts` | – | **Pflicht**, Melder muss bestehender Kontakt sein (CORE.md) |
| `type` | enum | – | ← `GWSTYPE` — Werte **offen** |
| `status` | enum | – | inkl. `wartet_auf_kunde` (← `WARTEAURUECKMELDUNG`) — Werte **offen** |
| `assigned_technician_id` | FK → `users` | ✓ | |
| `escalated_at` | datetime | ✓ | ← `TICKET_TICKETESCALATIONSTIME1` |
| `technician_diagnosis` | text | ✓ | ← `TICKET_TECHNIKERDIAGNOSE` |
| `fault_cause` | text | ✓ | ← `TICKET_GWSFEHLERURSACHE` |
| `work_performed` | text | ✓ | |
| `goodwill` | boolean | – | Kulanz ← `KULANZ` |
| `loan_device_required` | boolean | – | ← `LEIHGERAET` |
| `finalized_at` | datetime | ✓ | |
| `notes` | text | ✓ | |

Eigener Bericht + eigene Signatur (analog MaintenanceReport, aber Servicebericht —
kein Prüfkatalog-Zwang). `lineItems()` `morphMany`. Rückruf-Nr./Mail →
`contact_channels`.

---

## line_items (Positionen)

Polymorph an `Maintenance` **oder** `ServiceCase` (D-043).

| Feld | Typ | Null | Notiz |
| --- | --- | :-: | --- |
| `lineable_type` / `lineable_id` | morph | – | |
| `position` | smallint | – | |
| `description` | string | – | |
| `quantity` | decimal(10,2) | – | |
| `unit` | string | ✓ | Stk / Std / Pauschale |
| `unit_price` | decimal(10,2) | ✓ | netto |

**Keine** Summen-/Steuerfelder hier. Bei Einsatzabschluss + Freigabe → Übergabe an
Billing, das die `Invoice` erstellt und einfriert (D-043). Legacy `TICKET_GESAMT_*`,
`TICKET_MWST`, `TICKET_DATUM_ABGERECHNET` → Billing/abgeleitet.

---

## Offene Punkte

| # | Punkt | Wohin |
| --- | --- | --- |
| 1 | `contract_type`- und `status`-Enum-Werte (ServiceContract, Maintenance, ServiceCase) | Rückfrage Nutzer |
| 2 | `DO_SVV_PRAXISSW*` (14 Praxis-IT-Felder aus D-001) → Device vs. Location aufteilen | Rückfrage Nutzer |
| 3 | Templates je `device_category` — welche Kategorien? | Rückfrage Nutzer |
| 4 | Betriebsstatus-Werte, State-Machine-Übergänge final | Rückfrage Nutzer |
| 5 | Übergabe-Mechanismus line_items → Invoice (Sammelrechnung?) | Bereich Billing |
| 6 | `Termine.xml` — Terminplanung für Maintenance/ServiceCase | Bereich Scheduling (nächster) |
| 7 | Ersatzteile/Lager (Teile in line_items) | Bereich Inventory |
| 8 | Qualifizierte e-Signatur | späterer Slice |
| 9 | Offline-Wartungsbericht | späterer ROADMAP-Slice (D-041) |

## Migration (Hinweise)

- Legacy-Ticket-Zeilen sind **Wartung ODER Störung** — Migration muss anhand
  `GWSTYPE` / Vertragsbezug trennen (→ `Maintenance` vs `ServiceCase`).
- Die denormalisierten Slot-Felder auf der Legacy-Adresse (`DO_SVV*`, D-001) sind
  **tot** — Device-Daten kommen aus `Servicevertraege-NEU.xml`, nicht von dort.
