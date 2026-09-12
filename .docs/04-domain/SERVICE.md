# Domäne — Service: Device / ServiceContract / Maintenance / ServiceCase

Autoritative deklarative Spec. Entscheidungen **D-034 – D-045**
([`../07-decisions/grill-log.md`](../07-decisions/grill-log.md)). Feld-Herkunft:
[`../00-legacy/Servicevertraege/Servicevertraege-Zuordnung.md`](../00-legacy/Servicevertraege/Servicevertraege-Zuordnung.md),
[`../00-legacy/Tickets/Tickets-Zuordnung.md`](../00-legacy/Tickets/Tickets-Zuordnung.md).
Prinzipien: [`../05-modules/SERVICE.md`](../05-modules/SERVICE.md).

Modul: `packages/core/src/Modules/Service/` (Namespace `Dormed\Core\Modules\Service\`, `depends_on: [Core, Inventory]`, ADR-013 — Inventory neu durch D-109/D-121).

> **`Device` lebt ab D-121 in `Modules\Inventory\`**, nicht mehr hier
> (`DeviceComponent` ist mit D-131 ganz entfallen). Grund ist die Zyklusauflösung: `line_items` braucht einen Artikelbezug
> (Service → Inventory), und der Wareneingang erzeugt Geräte (Inventory → Service).
> Fachlich bleibt Service vollständig: `ServiceContract`, `Maintenance`,
> `MaintenanceReport`, `MeasurementProtocol`, `ServiceCase`, `line_items`,
> `service_prices`, `travel_zones`, `service_territories`. Die Device-Spec unten
> bleibt als Feldreferenz bestehen, autoritativ ist dafür [`INVENTORY.md`](INVENTORY.md).

## Grundsatz

Der Legacy-Servicevertrag (83 F.) und das Legacy-Ticket (112 F.) vermischen
Vertrag + Gerät + Komponenten + Netzwerk-Config + Wartungszyklus + Wartungs-
Checkliste + Messprotokoll + Rechnungspositionen. Zerlegung:

```
Company 1──n Location 1──n Device ──0..1── ServiceContract
                              │ n
                              └── Device (Komponenten, parent_device_id — D-131)

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

Das medizintechnische System (Ultraschallgerät …). Kann **mit oder ohne**
Servicevertrag existieren (D-064).

> **Revidiert durch die Inventory-Runde (D-099/D-105/D-121) — autoritativ ist ab
> jetzt [`INVENTORY.md`](INVENTORY.md).** `Device` ist das seriennummerngeführte
> **Exemplar der Warenwirtschaft** mit Lebenszyklus vom Wareneingang bis zur
> Verschrottung; „steht beim Kunden und wird gewartet" ist nur eine Phase davon.
> Konsequenzen: das Model zieht nach `Modules\Inventory\` (Zyklusauflösung,
> D-121), Katalogfelder wandern auf den `Article`, und `location_id` wird
> nullable, weil ein Gerät im Lager keinen Kundenstandort hat.

| Feld | Typ | Null | Notiz |
| --- | --- | :-: | --- |
| `article_id` | FK → `articles` | ? | **neu (D-099)** — Katalogbezug. Nullability offen, hängt an den Fremdgeräten (D-107) |
| `service_contract_id` | FK → `service_contracts` | ✓ | nullable, unique (D-064). Gesetzt ⇒ Tarifstufe `contract` |
| `form_factor` | enum `portabel` \| `standgeraet` | – | Bauform — bestimmt die Wartungspauschale (D-063/D-080): `standgeraet` teurer als `portabel` |
| `imaging_type` | enum `schwarzweiss` \| `farbdoppler` | – | Bildgebung — **rein katalog-/anzeigerelevant**, keine Preiswirkung (D-080). Kombination ergibt die Katalog-Klasse, z. B. „portables Farbdopplersystem" |
| `location_id` | FK → `locations` | ✓ | **wird nullable (D-099)** — Kundenstandort. Gesetzt ⇔ Gerät beim Kunden |
| `warehouse_id` | FK → `warehouses` | ✓ | **neu (D-099)** — Lager. Gesetzt ⇔ Gerät im Lager. **DB-CHECK: genau eines von beiden gesetzt** |
| ~~`manufacturer`~~ | – | – | **entfällt → `Article`** (Modelleigenschaft, D-099) |
| ~~`model_name`~~ | – | – | **entfällt → `Article`** (Modelleigenschaft, D-099) |
| ~~`article_number`~~ | – | – | **entfällt → `Article`** — „Artikelnummer ist Teil des Artikels, nicht des Items" (D-099) |
| `serial_number` | string | – | ← `SYSTEM_SERIENNUMMER` — die Seriennummer existiert genau **einmal** im System |
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
vollständig **`Location`** (Praxis-Netzwerk ist standort-, nicht gerätebezogen,
D-092) — nicht Device. Felder: Server-IP/-Passwort/-Gateway/-Subnetz, Storage-/
Worklist-Port+Titel (Praxis-weit), Praxis-EDV-ASP, Netzspeicher, Bemerkung,
Praxis-Software-Name.

---

## ~~DeviceComponent~~ — entfällt (D-131)

> **Ersatzlos aufgelöst.** Zubehör/Baugruppen (Sonde, Drucker, Wagen, SonoGDT) sind
> **keine Zeilen am Gerät mehr**, sondern **selbst Exemplare** (`Device`) mit
> `parent_device_id`. Eine Sonde liegt im Lager, wird verkauft, angebaut, abgebaut,
> ersetzt, eingeschickt — das ist ein eigener Lebenszyklus, kein Attribut.
>
> Anbau und Ausbau sind gewöhnliche Umbuchungen. Autoritativ:
> [`INVENTORY.md`](INVENTORY.md) — dort steht auch, wohin jedes einzelne
> D-034-Feld gewandert ist (`type` → `ArticleGroup`, `license` →
> benutzerdefiniertes Feld mit `scope = item`, …).

---

## ServiceContract

Die Servicevereinbarung für genau ein Device.

| Feld | Typ | Null | Notiz |
| --- | --- | :-: | --- |
| `company_id` | FK → `companies` | – | Vertragspartner |
| `number` | string | – | Nummernkreis (← `GWAUTONUM`) |
| `contract_type` | enum `full_service` \| `wartung` | – | Full-Service = inkl. Reparaturen/Ersatzteile; Wartung = nur planmäßige Wartung, Störungen extra (D-079) |
| `status` | enum `offen` \| `aktiv` \| `gekuendigt` \| `verschrottet` \| `kein_interesse` | – | `offen` = Entwurf/Verhandlung, noch nicht unterschrieben; `verschrottet` = Gerät außer Betrieb genommen; `kein_interesse` = Kunde lehnt nach Auslaufen/Kündigung explizit einen Neuabschluss ab (D-081) |
| `signed_on` | date | ✓ | ← `VERTRAGS_DATUM` |
| `cancelled_at` | date | ✓ | Kündigungsdatum |
| `full_service_ends_at` | date | ✓ | ← `VERTRAG_FS_ENDE` |
| `maintenance_interval_months` | smallint | – | 12 / 6 / 4 / 3 … (D-037; deckt „Mehrfachwartung", D-042) |
| — | – | – | `next_due_at` ist **kein** gespeichertes Feld — live berechnet aus `MAX(maintenance_devices.performed_at) + maintenance_interval_months` (D-093, revidiert D-037) |
| `maintenance_price` | decimal(12,2) | – | Wartungspauschale, **auf diesen Vertrag fixiert** bei Vertragserstellung aus der Preisliste; spätere Preislistenänderung wirkt nicht (D-065) |
| `payment_terms` | string | ✓ | ← `VERTRAG_ZAHLUNGSKONDITIONEN` |
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
| `form_factor` | enum `portabel` \| `standgeraet` (nullable) | nur bei `maintenance_flat` — **einzige** preisrelevante Geräte-Achse (D-080). `imaging_type` (schwarzweiß/Farbdoppler) hat **keine** Preiswirkung, ist rein katalog-/anzeigerelevant |
| `tier` | enum `contract` \| `standard` | |
| `amount` | decimal(12,2) | z. B. `hourly_rate/contract` = 25 €, `/standard` = 30 €; `maintenance_flat`: `standgeraet` teurer als `portabel` |

- `maintenance_flat` → wird bei Vertragserstellung in `ServiceContract.maintenance_price`
  **kopiert/fixiert**. Ab dann trägt der Vertrag den Preis.
- `hourly_rate` → wird beim ServiceCase **zum Zeitpunkt** des Falls angewandt
  (Tarifstufe nach Vertragsstatus des Geräts), auf die Position gesnapshottet.
  **Nicht** am Vertrag fixiert.

### `travel_zones` — eigenständig, PLZ-basiert (D-084)

`name`, `postal_code_from`, `postal_code_to` (echte PLZ-Von-Bis-Bereiche, D-084),
`flat_fee` (decimal, **ein** Wert je Zone — identisch für `contract` und `standard`,
D-063), `is_active`. **Nicht** Teil der Preisliste.

`Company.travel_zone_id` → `travel_zones` (nullable FK, **in CORE.md nachzutragen**) —
**automatisch aus der PLZ der Company-Adresse abgeleitet, manuell überschreibbar**
(D-084). Fahrtzonenpauschale wird bei der Abrechnung **einmal je Anfahrt** aus der
Company abgeleitet — **nicht** je Gerät, **nicht** vom Vertrag (D-059).

**Tarifstufe** je Position = `device.service_contract_id ? 'contract' : 'standard'`.

### `service_territories` — unabhängige PLZ-Tabelle für die Techniker-Vorbelegung (D-084)

`postal_code_from`, `postal_code_to`, `default_technician_id` (FK → `users`),
`is_active`. **Eigene** PLZ-Bereiche, unabhängig von `travel_zones` (fachlich
unterschiedlich geschnitten). Bestimmt `Maintenance.assigned_technician_id` /
`ServiceCase.assigned_technician_id` bei Erstellung (D-082/D-083) — Vorschlag, jederzeit
manuell änderbar.

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
| `status` | enum `geplant` \| `in_durchfuehrung` \| `kunde_bestaetigt` \| `abgeschlossen` \| `rechnung_freigegeben` | – | State-Machine (s. u.), D-082 |
| `scheduled_date` | date | ✓ | geplanter Anfahrtstag |
| `performed_at` | datetime | ✓ | tatsächliche Durchführung |
| `finalized_at` | datetime | ✓ | ← `TICKET_DATUM_GESCHLOSSEN` |
| `assigned_technician_id` | FK → `users` | ✓ | bei Erstellung **automatisch** aus `service_territories` (PLZ des Erfüllungsortes) vorbelegt, jederzeit übergebbar (D-082/D-084) |
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
| `maintenance_fee_snapshot` | decimal(12,2) | ✓ | Snapshot von `contract.maintenance_price` zum Einsatzzeitpunkt (D-065) |
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

### State-Machine (D-082)

`geplant → in_durchfuehrung → kunde_bestaetigt → abgeschlossen →
rechnung_freigegeben`. **Kein** separater `zugewiesen`-Zustand — der Techniker ist ab
Erstellung bekannt (automatische PLZ-Vorbelegung, s. o.), nicht Teil der
Status-Übergänge. Übergänge server-seitig erzwungen; kein Sprung
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
| `operating_status` | enum `in_betrieb` \| `eingeschraenkt` \| `ausser_betrieb` | – | bestätigt (D-091) |
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

- `checklist_templates`: `name`, `version` (integer), `is_active`, `published_at`.
  **Ein universeller Katalog für alle Geräte** — kein `device_category`-Feld (D-085).
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
jeweiligen Geräts). Fahrtzone: aus `Company`, einmal je Anfahrt (bestätigt, D-076).
Kein Bündeln von Verträgen wie bei `Maintenance`.

| Feld | Typ | Null | Notiz |
| --- | --- | :-: | --- |
| `company_id` | FK → `companies` | – | |
| `location_id` | FK → `locations` | ✓ | |
| `number` | string | – | Nummernkreis |
| `reported_by` | FK → `company_contacts` | – | **Pflicht**, Melder muss bestehender Kontakt sein (CORE.md) |
| `type` | enum `allgemeiner_service` \| `telefonischer_support` \| `geraeteausfall` \| `netzwerkproblem` | – | ← `GWSTYPE` (D-097, Liste erweiterbar) |
| `status` | enum `neu` \| `zugewiesen` \| `in_bearbeitung` \| `wartet_auf_kunde` \| `abgeschlossen` \| `storniert` | – | ← `GWSSTATUS`/`WARTEAURUECKMELDUNG` (D-083). `storniert` als Endzustand von jedem Nicht-Abschluss-Zustand aus erreichbar |
| `assigned_technician_id` | FK → `users` | ✓ | bei Erstellung automatisch aus `service_territories` vorbelegt (D-082/D-084), Übergang `neu → zugewiesen` bleibt ein eigener Schritt (D-083) |
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

> **Erweitert durch die Inventory-Runde (D-109/D-116/D-118).** Positionen sind
> nicht mehr reiner Freitext: sie können aus dem Artikelkatalog, aus dem
> Leistungskatalog oder frei erfasst werden. Die tragende Mechanik dahinter —
> **das Technikerlager ist die Positionsauswahl** — steht in
> [`INVENTORY.md`](INVENTORY.md) und ist für diese Tabelle verbindlich.

| Feld | Typ | Null | Notiz |
| --- | --- | :-: | --- |
| `lineable_type` / `lineable_id` | morph | – | |
| `position` | smallint | – | |
| `article_id` | FK → `articles` | ✓ | **neu (D-109)** — gesetzt bei Positionen aus dem Artikel-Tab |
| `offering_id` | FK → `offerings` | ✓ | **neu (D-117)** — gesetzt bei Positionen aus dem Leistungs-Tab |
| `stock_movement_id` | FK → `stock_movements` | ✓ | **neu (D-109)** — gesetzt, wenn die Position aus einer Lagerentnahme entstanden ist |
| `description` | string | – | bei Katalogpositionen vorbelegt, bei `diverse` frei (D-118) |
| `quantity` | decimal(10,2) | – | bei seriennummernpflichtigen Exemplaren immer 1 |
| `unit` | string | ✓ | Stk / Std / Pauschale — aus `Article.unit` / `Offering.unit` |
| `unit_price` | decimal(12,2) | ✓ | netto, **gesnapshottet** aus dem Katalog (D-104) |
| `is_chargeable` | boolean | – | **neu (D-129)**, default `true`. `false` ⇒ verbaut, aber nicht berechnet |
| `non_charge_reason` | enum `garantie` \| `kulanz` \| `vertrag` | ✓ | **neu (D-129)**, Pflicht wenn `is_chargeable = false` |

Alle drei neuen FKs sind nullable: eine „Sonstiges"-Position (`diverse`, D-118) hat
weder `article_id` noch `offering_id`, und eine Katalogposition ohne Bestandsbezug
(Leistung) hat keine `stock_movement_id`.

**Nicht berechnete Positionen (D-129):** Garantie-, Kulanz- und
Full-Service-Leistungen erzeugen eine **normale Position mit echtem Lagerabgang**,
tragen aber `is_chargeable = false` + Grund. Sie werden **in die Rechnung
übernommen und dort als „nicht berechnet" ausgewiesen** — der Kunde sieht, was er
erhalten hat und was es ihn nicht gekostet hat. Details: [`INVENTORY.md`](INVENTORY.md).

**Keine** Summen-/Steuerfelder hier. Bei Einsatzabschluss + Freigabe → Übergabe an
Billing, das die `Invoice` erstellt und einfriert (D-043). Legacy `TICKET_GESAMT_*`,
`TICKET_MWST`, `TICKET_DATUM_ABGERECHNET` → Billing/abgeleitet.

---

## Offene Punkte

| # | Punkt | Wohin |
| --- | --- | --- |
| 1 | ~~ServiceContract `contract_type`/`status`-Enum-Werte~~ | ✅ gelöst (D-079/D-081) |
| 1a | ~~`Maintenance`- und `ServiceCase`-`status`-Enum-Werte~~ | ✅ gelöst (D-082/D-083) |
| 1b | ~~`travel_zones`-Zonen-Definition~~ | ✅ gelöst — PLZ-Von-Bis, automatisch (D-084) |
| 2 | ~~`DO_SVV_PRAXISSW*` Device vs. Location~~ | ✅ gelöst — Location (D-092) |
| 3 | ~~Templates je `device_category`~~ | ✅ gelöst — ein universeller Katalog (D-085) |
| 4 | ~~`MaintenanceReport.operating_status`-Werte~~ | ✅ bestätigt (D-091) |
| 5 | ~~Übergabe-Mechanismus line_items → Invoice~~ | ✅ gelöst, siehe `BILLING.md` (D-057/D-066) |
| 6 | `Termine.xml` — Terminplanung für Maintenance/ServiceCase | ✅ gelöst, siehe `SCHEDULING.md` |
| 7 | ~~Ersatzteile/Lager (Teile in line_items)~~ | ✅ gelöst — [`INVENTORY.md`](INVENTORY.md), tragende Mechanik D-109 |
| 8 | Qualifizierte e-Signatur | späterer Slice |
| 9 | Offline-Wartungsbericht | späterer ROADMAP-Slice (D-041) |
| 10 | `sales_territories`/`service_territories`: konkrete PLZ-Bereiche + Zuordnungen befüllen | Datenerfassung bei Umsetzung |

## Migration (Hinweise)

- Legacy-Ticket-Zeilen sind **Wartung ODER Störung** — Migration muss anhand
  `GWSTYPE` / Vertragsbezug trennen (→ `Maintenance` vs `ServiceCase`).
- Die denormalisierten Slot-Felder auf der Legacy-Adresse (`DO_SVV*`, D-001) sind
  **tot** — Device-Daten kommen aus `Servicevertraege-NEU.xml`, nicht von dort.
