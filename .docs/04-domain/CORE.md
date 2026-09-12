# Domäne — Core: Company / Person / Adresse

Autoritative deklarative Spec für den Kern des CRM. Entstanden aus der
Adressen-Zerlegung (`/grill-me`, Entscheidungen **D-001 – D-025** im
[`../07-decisions/grill-log.md`](../07-decisions/grill-log.md); Feld-für-Feld
Herkunft in [`../09-legacy/xml/Adressen-Zuordnung.md`](../09-legacy/xml/Adressen-Zuordnung.md)).

Modul: `packages/core/src/Modules/Crm/` (Namespace `Dormed\Core\Modules\Crm\`, ADR-013). Diese Spec legt **Felder,
Typen, Beziehungen, Regeln** fest — nicht den Code-Stil.

## Grundsatz

**Die Company (Institution/Praxis) ist der zentrale Ankerpunkt.** Alles hängt an
ihr: Ansprechpartner, Serviceverträge, Termine, Verkaufschancen. Es gibt **keine
eigenständige Person** — jede Person ist über `CompanyContact` mindestens einer
Company zugeordnet (D-002).

Das Legacy-`Address`-Universalobjekt (356 Spalten) wird zerlegt in: `Company`,
`Person`, `CompanyContact`, `Address`, `Location`, `ContactChannel`, `Consent`.
243 Legacy-Felder werden **nicht** übernommen.

---

## Company

Die juristische / organisatorische Kunden-Einheit. **Aktuell ausschließlich
Kunden** (Lieferanten/Kreditoren = späterer Bereich, D-008). Companies sind flach
— **keine Hierarchie** außer der Rechnungsempfänger-Beziehung (D-006).

| Feld | Typ | Null | Notiz |
| --- | --- | :-: | --- |
| `name` | string | – | Firmenname (← `CompName`) |
| `name_addition` | string | ✓ | Adresszusatz, „zweiter Namensteil" (← `CompName2`, D-021) |
| `medical_specialty_id` | FK → `medical_specialties` | ✓ | Fachrichtung (D-019) |
| `notes` | text | ✓ | Schlagworte (← `Notes`) |
| `debitor_number` | string | ✓ | Debitorennummer Sage/KHK (← `AdrNumber`, D-009) |
| `khk_matchcode` | string | ✓ | KHK-Matchcode (← `ADRKHKMATCHCODE`, D-009) |
| `avv_status` | enum `none` \| `signed` | – | Auftragsverarbeitungsvertrag (← `AVV`, D-013) |
| `avv_signed_at` | date | ✓ | — |
| `responsible_sales_id` | FK → `users` | ✓ | Verantwortlich Vertrieb, **informativ**, keine AuthZ (D-016) |
| `responsible_service_id` | FK → `users` | ✓ | Verantwortlich Service, dito (D-016) |
| `billing_company_id` | FK → `companies` | ✓ | Abweichende Rechnungsanschrift → andere `Company` (z. B. Praxisgesellschaft). Self-Reference. **Alle** Rechnungen dieser Praxis gehen dorthin (D-004/D-066). Keine praxisübergreifende Sammelrechnung. |
| `travel_zone_id` | FK → `travel_zones` | ✓ | Fahrtzone der Institution — Grundlage der Service-Anfahrtspauschale (D-020/D-059/D-063). `travel_zones` (`name`, `flat_fee`) definiert im Service-Bereich. Fahrtzone wird **einmal je Anfahrt** berechnet, nicht je Gerät/Vertrag. |
| — Bankverbindung — | | | |
| `iban` | string(34) | ✓ | ← `gwIBAN` |
| `bic` | string(11) | ✓ | ← `gwBIC` |
| `bank_account_holder` | string | ✓ | ← `BankAccountHolder` |
| `bank_name` | string | ✓ | ← `FinancialInstitute` |
| — Recht / Steuer — | | | |
| `legal_form` | string | ✓ | Rechtsform / rechtl. Informationen (← `GWCOMPANYLEGALFORM`) |
| `tax_number` | string | ✓ | Steuernummer (← `TAXNUMBER`) |
| `vat_id` | string | ✓ | USt-IdNr. (← `TurnoverTaxId`) |
| `wid_number` | string | ✓ | Wirtschafts-Identifikationsnummer (← `CASWIDNR`) |
| `trade_register_number` | string | ✓ | Registernummer (← `GWTRADEREGISTER`) |
| `register_court` | string | ✓ | Registergericht/-standort (← `GWDISTRICTCOURT`) |

**Beziehungen**
- `address()` — `morphOne` `Address` (Sitz, D-003/D-014)
- `locations()` — `hasMany` `Location` (mindestens 1, D-007)
- `contacts()` — `hasMany` `CompanyContact`
- `people()` — `belongsToMany` `Person` über `company_contacts`
- `contactChannels()` — `morphMany` `ContactChannel`
- `billingCompany()` — `belongsTo` `Company` (nullable)
- `medicalSpecialty()` — `belongsTo` `MedicalSpecialty`
- `responsibleSales()`, `responsibleService()` — `belongsTo` `User`

**Regeln**
- Bei Neuanlage wird automatisch eine `Location` „Hauptstandort" mit einer Kopie
  der Sitzadresse erzeugt; danach unabhängig editierbar (D-007). **Zu bestätigen.**
- Rechnungsadresse einer *ausgestellten* Rechnung wird in Billing historisiert —
  nicht hier (D-004).

---

## Person

Eine reale Person. **Nie eigenständig** — Anlage immer im Kontext einer Company
(D-002). Person hat **keine eigene Adresse** (D-014).

| Feld | Typ | Null | Notiz |
| --- | --- | :-: | --- |
| `first_name` | string | – | ← `ChristianName` |
| `last_name` | string | – | ← `Name` |
| `name_suffix` | string | ✓ | Namenszusatz, z. B. „Dr. med." (← `gwAdditionalInfo1`, D-025) |
| `title` | string | ✓ | Titel (← `Title`) — kann später mit `name_suffix` verschmelzen |
| `gender` | enum `maennlich` \| `weiblich` \| `divers` \| `unbekannt` | – | ← `GWGENDER` |
| `locale` | string(5) | – | default `de` (aktuell nur DE-Kunden, D-025) |

**Beziehungen**
- `contacts()` — `hasMany` `CompanyContact`
- `companies()` — `belongsToMany` `Company` über `company_contacts`
- `contactChannels()` — `morphMany` `ContactChannel`
- `consents()` — `hasMany` `Consent`

Briefanrede wird bei Dokumenterstellung **generiert**, nicht gespeichert (D-015).

---

## CompanyContact

Die explizite Beziehung Person ↔ Company. Eine Person kann Kontakt mehrerer
Companies sein.

| Feld | Typ | Null | Notiz |
| --- | --- | :-: | --- |
| `company_id` | FK → `companies` | – | cascade delete |
| `person_id` | FK → `people` | – | cascade delete |
| `role` | string | ✓ | **genau eine** Rolle (D-005). UI: Vorschlagsliste + Freitext. Nicht auswertungsrelevant. |
| `department` | string | ✓ | Abteilung des Ansprechpartners (← `Department`, D-022) |
| `is_primary` | boolean | – | default `false` |

Unique(`company_id`, `person_id`).

**`role`-Vorschlagswerte** (Datalist, Freitext erlaubt): Praxismanager*in ·
Einkauf · IT · Buchhaltung · Ärztliche Leitung · Technik · Empfang · Sonstige.

---

## Address

Polymorph (`morphOne`) an `Company` (Sitz) und `Location`. Genau **eine** Adresse
pro Besitzer.

| Feld | Typ | Null | Notiz |
| --- | --- | :-: | --- |
| `addressable_type` / `addressable_id` | morph | – | unique zusammen |
| `street` | string | – | Straße (← `Street1` / `Street2`) |
| `house_number` | string(32) | ✓ | separat (D-025) |
| `postal_code` | string(20) | – | |
| `city` | string | – | |
| `district` | string | ✓ | Teilort (← `Suburb1/2`) |
| `state` | string | ✓ | Staat/Region (← `GWSTATE1/2`) |
| `country_code` | string(2) | – | default `DE` |
| `po_box` | string | ✓ | Postfach (← `PoBox1/2`) |
| `po_box_postal_code` | string | ✓ | ← `PoBoxZip1/2` |
| `po_box_city` | string | ✓ | ← `POTOWN1/2` |
| `latitude` / `longitude` | decimal(10,7) | ✓ | Geocoding (D-024) — **wichtig**, Grundlage Fahrtzone |
| `geocode_status` | enum `pending` \| `ok` \| `failed` \| `manual` | – | default `pending` |
| `verified_at` | datetime | ✓ | Adress-Prüfung (← `GWEXTERNALADDRESSDATE`) |
| `verified_by` | string | ✓ | ← `GWEXTERNALADDRESSNAME` |

Geocoding-Mechanismus (Provider, Trigger) → Bereich Integrationen.

---

## Location

Realer Betriebs-/Servicestandort einer Company. **Jede Company hat ≥ 1 Location**
(Einzelpraxis: 1 Location = Sitzadresse). **Device / ServiceContract referenzieren
die Location** (D-007), nicht die Company.

| Feld | Typ | Null | Notiz |
| --- | --- | :-: | --- |
| `company_id` | FK → `companies` | – | cascade delete |
| `name` | string | – | z. B. „Hauptstandort", „Praxis Nord" |
| `notes` | text | ✓ | |
| `is_primary` | boolean | – | genau eine je Company = Hauptstandort |

**Beziehungen**: `company()` `belongsTo`, `address()` `morphOne` `Address`.

Legacy-Slot 2 (`Street2` … „Lieferung") wird als (weitere) Location migriert (D-014).

---

## ContactChannel

Ersetzt die ~30 nummerierten Legacy-Kommunikationsslots (D-010). Polymorph an
`Company` **oder** `Person`.

| Feld | Typ | Null | Notiz |
| --- | --- | :-: | --- |
| `channelable_type` / `channelable_id` | morph | – | |
| `channel_type` | enum `phone` \| `mobile` \| `fax` \| `email` \| `web` | – | |
| `label` | enum | – | fester Enum, auswertbar (s. u.) |
| `value` | string | – | Nummer / Adresse / URL |
| `is_primary` | boolean | – | je (`channelable`, `channel_type`) höchstens einer |

**`label`-Enum**: `geschaeftlich` · `praxis` · `zentrale` · `durchwahl` ·
`rechnungsversand` · `privat` · `mobil_persoenlich` · `mobil_arzt` · `homepage` ·
`sonstige`.

---

## Consent

Historisierte DSGVO-Einwilligung je Person und Kanal (D-013).

| Feld | Typ | Null | Notiz |
| --- | --- | :-: | --- |
| `person_id` | FK → `people` | – | cascade delete |
| `channel` | enum `fax` \| `mail` \| `post` \| `sms` \| `telefon` | – | |
| `status` | enum `erteilt` \| `widerrufen` | – | |
| `granted_at` | datetime | ✓ | |
| `revoked_at` | datetime | ✓ | |
| `source` | enum `formular` \| `muendlich` \| `telefonisch` \| `import` \| `sonstige` | – | |

Nie hart löschen (Nachweispflicht) — nur neuer Datensatz bei Statuswechsel.

---

## MedicalSpecialty (Lookup)

Vom Nutzer pflegbare Liste. `Company.medical_specialty_id` → hierauf.

| Feld | Typ | Notiz |
| --- | --- | --- |
| `name` | string | unique |
| `is_active` | boolean | default `true` |

**Seed** (verbatim aus dem Altsystem, D-019): Pulmologen · Institution · Hdin ·
Orthopäden · Dermatologen · Veterinäre · Radiologen · Chirurgen · Kardiologen ·
HNO · Sportmedizin · USVE · Bahnarzt · Rheumatologen · Hebammen · Werksarzt ·
Unfallchirurgie · Sanitätshaus · Heilpraktiker · Phlebologie · Neurologen.
→ „Hdin" und „USVE" sind unklare Abkürzungen (offen).

---

## Querschnitt

- **Soft-Delete + Papierkorb** (D-018, D-023): jedes Modell hier inkl. Pivots
  nutzt `SoftDeletes`. Löschen / Papierkorb ansehen / Wiederherstellen = nur
  Abteilung **Management/Backoffice** (eigene Berechtigung). Endgültige Löschung
  automatisch nach **30 Tagen**. Details / Modell-Regel → `.ai/rules` + Infra-Doc
  + eigener ROADMAP-Slice (Voraussetzung).
- **Audit** (`created_by` / `updated_by`): `App\Support\TracksBlame` (bereits vorhanden).
- **`responsible_*_id`** zeigen vorerst auf `users`; Re-Point auf `Employee`
  sobald das Identity-Modell steht (D-012, D-016).

## Offene Punkte (in diesem Bereich bewusst offen gelassen)

| # | Punkt | Wohin |
| --- | --- | --- |
| 1 | „Hdin" / „USVE" Fachrichtungs-Abkürzungen | Rückfrage Nutzer |
| 2 | Auto-„Hauptstandort"-Location bei Company-Anlage bestätigen | Rückfrage Nutzer |
| 3 | `title` vs `name_suffix` endgültig zusammenlegen? | Person, später |
| 4 | Fahrtzonen-Modell (Zonen, Preise, Geocoding-Zuordnung) | Bereich Service |
| 5 | Melder = `ServiceCase.reported_by` → CompanyContact (Pflicht) | Bereich Service |
| 6 | Device / Praxis-IT (19 Legacy-Felder, `DO_SVV_PRAXISSW*` …) | Bereich Service/Device |
| 7 | Geocoding-Provider & Trigger | Bereich Integrationen |
| 8 | Lieferanten/Kreditoren | Späterer Bereich (ROADMAP) |
| 9 | Company-Verbünde/Konzern | Nicht-Scope bis realer Fall (D-006) |

## Slice-Anpassungen (bestehender CRM-Code)

Der bestehende `packages/core/src/Modules/Crm/`-Slice (Company/Person/Location/CompanyContact/
Address) ist **strukturell kompatibel**, muss aber angepasst werden:
- Person nicht mehr frei anlegbar → nur nested unter Company (D-002).
- Company: neue Felder (Bank, Recht/Steuer, `name_addition`, `medical_specialty_id`,
  `debitor_number`, `khk_matchcode`, `avv_*`, `responsible_*_id`, `billing_company_id`,
  `travel_zone`).
- Address: `house_number` bereits vorhanden; `district`/`state`/`po_box*`/Geocoding/
  `verified_*` ergänzen.
- Neu: `ContactChannel`, `Consent`, `MedicalSpecialty`.
- `SoftDeletes` überall ergänzen.
→ Umsetzung erst mit dem CRM-Slice der ROADMAP, nicht jetzt.
