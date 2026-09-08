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
Company 1──n Location 1──n Device ──0..1── ServiceContract
                              │ 1
                              │ n
                        DeviceComponent

Maintenance (= Anfahrt, 1 Company/Location)
   └─ 1..n MaintenanceDevice ──1── Device (Gerät MIT Vertrag)
             ├─ 1  MaintenanceReport ──> ChecklistTemplate (version-pinned)
             ├─ 0..1 MeasurementProtocol
             └─ n  line_items
   ⇒ 1 Rechnung je Maintenance (Fahrtzone 1× + Σ Geräte)

ServiceCase (unabhängig, 0..n Geräte, keine Vertragskosten)
   ├─ n  line_items
   └─ 0..1 MeasurementProtocol
```

**Wartung ≠ Servicefall** — vollständig getrennte Modelle, getrennte Workflows
(D-039). Der Ursprung bestimmt den zulässigen Prozess.
**`Maintenance` = die Anfahrt** und bündelt mehrere Geräte/Verträge einer Praxis
(D-059) — die Fahrtzone wird so **einmal je Anfahrt** berechnet.

---

## Device

Das medizintechnische System (Ultraschallgerät …). Steht an einer `Location`
(D-007). Kann **mit oder ohne** Servicevertrag existieren (D-064).

| Feld | Typ | Null | Notiz |
| --- | --- | :-: | --- |
| `service_contract_id` | FK → `service_contracts` | ✓ | nullable, unique (D-064). Gesetzt ⇒ Tarifstufe `contract` |
| `device_class` | enum `1` \| `2` | – | Geräteklasse, bestimmt Wartungspauschale (D-063). Namen offen |
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
| `next_due_at` | date | ✓ | **abgeleitet** aus letztem `maintenance_devices.performed_at` + Intervall (D-037/D-059) |
| `maintenance_price` | decimal(10,2) | – | Wartungspauschale, **auf diesen Vertrag fixiert** bei Vertragserstellung aus der Preisliste; spätere Preislistenänderung wirkt nicht (D-065) |
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

Der Vertrag hat **einen fixierten** `maintenance_price` (D-065) und wirkt zusätzlich
als **Gate** für die Tarifstufe: Device mit Vertrag ⇒ `contract`, sonst `standard`.

**Verworfen** (D-036/D-037/D-042/D-059/D-062): `KOSTEN_*` (alle Kosten-Varianten
außer der einen Wartungspauschale), `PREISANPASSUNG*`, `ERSTEWARTUNG`,
`NAECHSTEWARTUNG`, `MONAT`, `MEHRFACHWARTUNG`. Adress-/Firmen-Dubletten (`FIRMA`,
`ORT`, `PLZ`, `DEBITORENNUMMER`, `VERANTWORTLICHER_SERVICE`) → leben auf `Company`.

---

## Preise — `service_prices` + `travel_zones` (D-062/D-063/D-065)

### `service_prices` — nur für **neue** Verträge/Angebote (D-065)

Manuell gepflegte Liste. Wirkung **ausschließlich** beim (a) Erstellen neuer
Angebote (Opportunity-Positionen, D-052) und (b) Fixieren eines **neuen**
Vertrags. **Keine** Wirkung auf bestehende Verträge, **keine** Wirkung auf die Fahrtzone.

| Feld | Typ | Notiz |
| --- | --- | --- |
| `item` | enum `maintenance_flat` \| `hourly_rate` \| … | |
| `device_class` | enum `1` \| `2` (nullable) | nur bei `maintenance_flat` |
| `tier` | enum `contract` \| `standard` | |
| `amount` | decimal(10,2) | z. B. `hourly_rate/contract` = 25 €, `/standard` = 30 € |

- `maintenance_flat` → wird bei Vertragserstellung in `ServiceContract.maintenance_price`
  **kopiert/fixiert**. Ab dann trägt der Vertrag den Preis.
- `hourly_rate` → wird beim ServiceCase **zum Zeitpunkt** des Falls angewandt
  (Tarifstufe nach Vertragsstatus des Geräts), auf die Position gesnapshottet.
  **Nicht** am Vertrag fixiert.

### `travel_zones` — eigenständig

`name`, `flat_fee` (decimal, **ein** Wert je Zone — identisch für `contract` und
`standard`, D-063), `is_active`. **Nicht** Teil der Preisliste. Zonen-Definition
(PLZ-Bereiche vs. manuell je Company) → **offen**.

`Company.travel_zone_id` → `travel_zones` (nullable FK, **in CORE.md nachzutragen**).
Fahrtzonenpauschale wird bei der Abrechnung **einmal je Anfahrt** aus der Company
abgeleitet — **nicht** je Gerät, **nicht** vom Vertrag (D-059).

**Tarifstufe** je Position = `device.service_contract_id ? 'contract' : 'standard'`.

---

## Maintenance — der Einsatz / die Anfahrt (D-059)

**`Maintenance` ist der Vor-Ort-Einsatz**, gebunden an genau **eine** `Company` +
`Location`, und bündelt **1..n** Serviceverträge/Geräte, die in **einer Anfahrt**
gewartet werden. Zwei bewusst getrennte Anfahrten = zwei `Maintenance`.
**Keine Auto-Erkennung** — die Bündelung ist eine explizite Planungsentscheidung.

| Feld | Typ | Null | Notiz |
| --- | --- | :-: | --- |
| `company_id` | FK → `companies` | – | eine Praxis je Einsatz |
| `location_id` | FK → `locations` | – | ein Standort je Einsatz |
| `number` | string | – | Nummernkreis |
| `status` | enum | – | State-Machine (s. u.) |
| `scheduled_date` | date | ✓ | geplanter Anfahrtstag |
| `performed_at` | datetime | ✓ | tatsächliche Durchführung |
| `finalized_at` | datetime | ✓ | ← `TICKET_DATUM_GESCHLOSSEN` |
| `assigned_technician_id` | FK → `users` | ✓ | |
| `visit_aborted` | boolean | – | ganze Praxis kein Zugang (D-061) |
| `notes` | text | ✓ | |

**Beziehungen:** `devices()` `hasMany` `MaintenanceDevice`; `appointments()`
`morphMany` (n einzelne Termine je Gerät, D-046/D-059); `invoice()` — 1 Rechnung
je Einsatz.

### MaintenanceDevice

Ein Eintrag je gebündeltem Gerät im Einsatz.

| Feld | Typ | Null | Notiz |
| --- | --- | :-: | --- |
| `maintenance_id` | FK | – | |
| `device_id` | FK → `devices` | – | Gerät **muss** einen `service_contract_id` haben (D-064) |
| `planned_due_at` | date | – | Fälligkeit dieses Geräts (kann je Gerät abweichen) |
| `performed_at` | datetime | ✓ | Basis `next_due_at` **dieses** Vertrags (D-037) |
| `status` | enum `durchgefuehrt` \| `nicht_durchgefuehrt` | – | D-061 |
| `maintenance_fee_snapshot` | decimal(10,2) | ✓ | Snapshot von `contract.maintenance_price` zum Einsatzzeitpunkt (D-065) |
| `work_performed` | text | ✓ | ← `TICKET_DURCHGEFUEHRTEARBEITEN` |

**Beziehungen:** `report()` `hasOne` `MaintenanceReport`, `measurementProtocol()`
`hasOne` `MeasurementProtocol`, `lineItems()` `morphMany`.

### Abrechnung eines Maintenance-Einsatzes (D-059/D-061/D-065)

```
maintenance_device.maintenance_fee_snapshot := contract.maintenance_price   (bei Einsatz)

Rechnung = Fahrtzone (Company.travel_zone.flat_fee, Snapshot) × 1
         + Σ  je maintenance_device (fee = maintenance_fee_snapshot):
              status = durchgefuehrt       → 100 % fee + line_items
              status = nicht_durchgefuehrt →  50 % fee
```

`visit_aborted = true` ⇒ **alle** `maintenance_devices` gelten als
`nicht_durchgefuehrt` (50 %) **+ volle Fahrtzone** (D-061).

### State-Machine (Werte offen — Vorschlag)

`geplant → zugewiesen → in_durchfuehrung → kunde_bestaetigt → abgeschlossen →
rechnung_freigegeben`. Übergänge server-seitig erzwungen; kein Sprung
`geplant → rechnung_freigegeben` (IDENTITY_RBAC „Business-Workflow").

---

## MaintenanceReport

Der strukturierte Wartungsbericht (D-038). **Kein** `visual_check_1..N`.

| Feld | Typ | Null | Notiz |
| --- | --- | :-: | --- |
| `maintenance_device_id` | FK → `maintenance_devices` | – | 1:1 — **ein Bericht je Gerät** (D-059) |
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
getrieben (D-044). 0..1 je `maintenance_device` (bei Bedarf auch `ServiceCase`).

| Feld | Typ | Null | Notiz |
| --- | --- | :-: | --- |
| `maintenance_device_id` | FK → `maintenance_devices` | – | |
| `protection_class` | enum `sk1` \| `sk2` | – | ← `TICKET_MESSWERTE_SCHUTZKLASSE` |
| `test_method` | string | ✓ | ← `_ART` |
| `test_equipment` | string | ✓ | ← `_PRUEFMITTEL` |
| `sk1_iega` … `sk1_uln` | decimal | ✓ | 6 Werte ← `_SK1_*` |
| `sk2_iega` … `sk2_uln` | decimal | ✓ | 6 Werte ← `_SK2_*` |
| `evaluation` | enum `ok` \| `nicht_ok` | – | Grenzwert-Bewertung |
| `is_constancy_test` | boolean | – | Konstanzprüfung für KV ← `TICKET_ABSCHLUSS_KP` |

---

## ServiceCase

Störung / Serviceeinsatz — **nicht** aus dem Wartungszyklus (D-039). Betrifft
**0..n Geräte** (D-060) über Pivot `service_case_devices` (`service_case_id`,
`device_id`); Geräte können mit **oder ohne** Vertrag sein.

**Keine Kostenableitung aus dem Vertrag** (D-060). Alle Positionen ad-hoc
(Teile per Kostenvoranschlag, Arbeitszeit `hourly_rate` nach Tarifstufe des
jeweiligen Geräts). Fahrtzone: aus `Company`, einmal je Anfahrt (**zu bestätigen**).
Kein Bündeln von Verträgen wie bei `Maintenance`.

| Feld | Typ | Null | Notiz |
| --- | --- | :-: | --- |
| `company_id` | FK → `companies` | – | |
| `location_id` | FK → `locations` | ✓ | |
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
