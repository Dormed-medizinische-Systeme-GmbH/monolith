# Grill-Log — Spec-First-Entscheidungen

Chronologisches Roh-Protokoll der `/grill-me`-Sessions. Jede Entscheidung bekommt
eine ID (`D-NNN`). Nach Abschluss eines Bereichs wird der Inhalt in die
`.docs/`-Struktur + `.ai/rules/` synthetisiert; dieses Log bleibt als Audit-Spur.

Status je Eintrag: `entschieden` · `offen (Rückfrage)` · `Entscheidungspunkt in ROADMAP`.

---

## Bereich: Domäne — Adressen-Zerlegung

> **Status: abgeschlossen & synthetisiert (2026-09-08).** Ergebnis:
> [`../04-domain/CORE.md`](../04-domain/CORE.md) (autoritative Spec) +
> [`../00-legacy/xml/Adressen-Zuordnung.md`](../00-legacy/xml/Adressen-Zuordnung.md)
> (356 Felder klassifiziert). Alle „Offene Fachfragen" zu Core aus `DOMAIN.md`
> beantwortet. Rest-Offen (in `CORE.md` gelistet): 2 Nutzer-Rückfragen (Hdin/USVE,
> Auto-Hauptstandort), Rest an Bereich Service/Integrationen/ROADMAP übergeben.

### D-001 — Legacy-Adressfelder `DO_SV*` / `DO_SVV*` (Servicevertrags-Slots)

**Status:** entschieden (Rand offen, s. u.)
**Datum:** 2026-09-08

Der Legacy-`Address` trägt bis zu ~6 „Servicevertrags-Slots" **denormalisiert**
(`DO_SVV_*` = Slot 1, `DO_SVV2_*` … `DO_SVV6_*`, plus `DO_SV_*` / `DO_SV2..5_*`
für Kosten) — Hersteller, Seriennummern, MAC, Wagen, Sonden, Fahrtzonen-Kosten,
Zahlung, Vertragsart/-status je Slot. Insgesamt **152 Felder**.

**Entscheidung:** Dieser gesamte Block → **verwerfen**. Laut Nutzer nicht mehr in
Verwendung; liegt nur noch da, weil der Löschprozess im Altsystem sehr
zeitintensiv ist. Nicht migrieren, nicht ins Zielmodell übernehmen.

**Domänen-Konsequenz (wichtig):** Das Muster bestätigt die Anforderung, dass im
Zielmodell **ServiceContract / Device** eine eigene, N-fach zu Company/Location
verknüpfte Entität ist (nicht Felder auf der Adresse). Die *fachlichen Inhalte*
(Sonden, Wagen, Drucker, Fahrtzone, Elektronikversicherung …) fließen in die
Service-/Device-Spec ein, nur eben nicht aus diesen toten Adressfeldern.

**Randfragen — geklärt (2026-09-08):**
1. `DO_SVV_PRAXISSW*` (14 Felder: Server-IP/Passwort/Gateway/Subnetz/Ports/
   Netzspeicher, Praxis-EDV-ASP, Speicher-/Arbeitslisten-AE-Titel+Port, Bemerkung,
   `DO_SVV_PRAXISSW` = Praxis-Software) → **bleiben.** Praxis-IT- /
   Remote-Access-Dokumentation. Zielmodell noch offen — vermutlich am **Device**
   oder an der **Location** (IT-/Anbindungs-Doku). → Entscheidungspunkt in der
   Service-/Device-Spec.
2. `SVV_VERTRAGS_INTERVALL`, `SVV2/3/4_VERTRAGS_INTERVALL` → **verwerfen** (Teil
   des toten Slot-Blocks).

**Zusammenfassung:** 142 Adressfelder verworfen, 14 behalten (`DO_SVV_PRAXISSW*`).

**Noch zu bestätigen:** `DO_SVV_PRAXIS_ASP` („Praxis Ansprechpartner", allgemeiner
Praxis-Kontakt neben `_HWASP`/`_ITASP`) — aktuell `verwerfen`; gehört semantisch
zum behaltenen IT-ASP-Cluster. Behalten oder weg?

---

### D-002 — Company ist der zentrale Ankerpunkt; Person hängt immer an ≥1 Company

**Status:** entschieden · **Datum:** 2026-09-08

- Die **Institution/Firma (Company)** ist das zentrale Objekt, um das sich alles
  dreht. An eine Company werden verknüpft: Ansprechpartner (Person via
  CompanyContact), Serviceverträge, Termine, Verkaufschancen.
- **Keine eigenständige Person.** Jede Person hängt über CompanyContact an
  mindestens einer Company. Auch Einzelärzte/Privatpraxen = eine (ggf.
  Ein-Personen-)Company.
- Person wird also **im Kontext einer Company** angelegt. Eine eigenständige
  `/people`-Ansicht ist reine Such-/Übersichtsansicht, kein Anlegen ohne Company.
  → betrifft den bestehenden CRM-Slice (dort ist Person aktuell frei anlegbar) →
  Anpassung in der Synthese.
- Querverknüpfungen zwischen den Sub-Objekten (z. B. **Termin ↔ Ticket** existiert
  heute) sind ein **späterer Bereich**, nicht Teil der Adressen-Zerlegung.
- **Offen:** privater „Melder" eines Servicefalls ohne Praxis-Bezug — wie
  abgebildet? → Bereich Service.

### D-003 — Company hat genau eine Adresse (Sitz)

**Status:** entschieden · **Datum:** 2026-09-08

Eine Adresse pro Company. Abweichende Liefer-/Rechnungsadresse wird **nicht** über
mehrere Company-Adressen gelöst, sondern über Location (physischer Ort) bzw. die
Rechnungsempfänger-Beziehung (D-004). Deckt sich mit dem bestehenden CRM-Slice
(`Company morphOne Address`).

### D-004 — Leistungsempfänger ≠ Rechnungsempfänger möglich

**Status:** entschieden (Detail offen) · **Datum:** 2026-09-08

Der Rechnungsempfänger kann von der servicierten/belieferten Company abweichen
(Treiber: Managementgesellschaft zahlt für mehrere unabhängige Praxen). Die
Company bekommt eine **„Rechnung an"-Beziehung** — von Anfang an im Modell.

- Voraussichtlich `companies.billing_company_id` → andere Company (nullable
  Self-Reference), da D-002/D-003 alles auf Company zentrieren.
- **Offen:** Ist der Rechnungsempfänger immer eine andere **Company**, oder kann
  es auch eine freistehende Rechnungsadresse ohne Company sein? (Annahme: immer Company.)
- Historisierung der Rechnungsadresse auf ausgestellten Rechnungen = Bereich Billing.

### D-005 — CompanyContact: genau eine Rolle, Vorschlagsliste + Freitext, nicht auswertungsrelevant

**Status:** entschieden · **Datum:** 2026-09-08

- **Genau eine Rolle** pro CompanyContact-Zeile. Mehrere Rollen ⇒ mehrere Zeilen.
- `role` = `string` (nullable). Die UI bietet eine **Vorschlagsliste** üblicher
  Werte (Datalist), erlaubt aber **Freitext**. Kein normalisierter Enum, keine FK —
  die Rolle ist für Auswertungen/Filter **nicht** relevant.
- Deckt sich mit dem bestehenden CRM-Slice (`role` nullable string, `is_primary` bool).
- **Offen:** konkrete Vorschlagswerte (Praxismanager, Einkauf, IT, Buchhaltung,
  Ärztliche Leitung, Technik …) → in der Synthese festzurren.

### D-006 — Keine Company-Hierarchie

**Status:** entschieden · **Datum:** 2026-09-08

Companies sind flach. Die **einzige** Company↔Company-Beziehung ist der abweichende
Rechnungsempfänger (D-004). Verbünde / Konzern / MVZ-Struktur werden erst
modelliert, wenn ein realer Fall es verlangt (ADR-010-Ausnahme greift nicht — hier
bewusst NICHT vorwegnehmen). → benannter Nicht-Scope.

### D-007 — Location = physischer Standort; Device/ServiceContract hängt an Location

**Status:** entschieden (Detail offen) · **Datum:** 2026-09-08

- Location = realer Ort (Praxisadresse, Nebenstelle, Zentrallager).
- **Jede Company hat ≥ 1 Location.** Einzelpraxis = 1 Location = Sitzadresse.
- **Device / ServiceContract referenzieren die Location**, an der das Gerät steht —
  nicht die Company.
- **Detail (D-003 × D-007):** Vorschlag — `Company.address` = Sitz (juristisch,
  Korrespondenz, Basis Rechnungsadresse); `Location.address` = physischer
  Gerätestandort. Bei Neuanlage einer Company wird automatisch eine Location
  „Hauptstandort" mit Kopie der Sitzadresse erzeugt (danach unabhängig editierbar).
  → **zu bestätigen.**
- **Slice-Konsequenz:** bestehender CRM-Slice hat `Company hasMany Location` +
  `Location morphOne Address` — passt. Auto-Hauptstandort fehlt noch.

### D-008 — Company ist (vorerst) nur Kunde; Lieferant = späterer Bereich

**Status:** entschieden · **Datum:** 2026-09-08

Zielmodell startet kundenzentriert. Kein `ist_lieferant`-Flag jetzt. Legacy-
Lieferantenadressen werden **nicht** als Company migriert (Migrations-Filter).
Lieferanten/Einkauf = **benannter Entscheidungspunkt in der ROADMAP**.

### D-009 — Company trägt Debitorennummer + KHK-Matchcode (Sage-KHK-Bridge bleibt)

**Status:** entschieden · **Datum:** 2026-09-08

- `AdrNumber` („Deb./Kred. Konto") → `Company.debitor_number`
- `ADRKHKMATCHCODE` → `Company.khk_matchcode`
- Die Sage-KHK-Buchhaltungsanbindung bleibt bestehen. Der Sync-**Mechanismus**
  selbst = Bereich Integrationen; die **Felder** leben ab jetzt auf Company.
- Feldnamen bewusst „Debitor…" (nicht „Deb./Kred.") — Kreditor-Pendant kommt mit D-008.

### D-010 — Kommunikationskanäle: eigene Tabelle `contact_channels`, `label` = fester Enum

**Status:** entschieden (Enum-Werte offen) · **Datum:** 2026-09-08

Ersetzt die ~30 nummerierten Legacy-Slots (Telefon×10, Fax×5, Mail×5, Web×5, IM×3).

- Tabelle `contact_channels`: `channel_type` (enum: `phone` · `mobile` · `fax` ·
  `email` · `web`), `label` (**fester Enum**, auswertbar), `value`, polymorpher
  Besitzer (Company **oder** Person), `is_primary` (je Typ).
- **Offen:** die `label`-Enum-Werte. Legacy-Kandidaten verdichtet:
  `geschaeftlich` · `praxis` · `zentrale` · `durchwahl` · `rechnungsversand` ·
  `privat` · `mobil_persoenlich` · `mobil_arzt` · `homepage` · `sonstige`.
- IM/Skype-Felder → `web`-Typ oder verwerfen (Entscheidung im Cluster-Durchlauf).

### D-011 — Marketing-/Fremdsystem-Blöcke komplett verwerfen

**Status:** entschieden · **Datum:** 2026-09-08

`EV*` (Evalanche, ~22 Felder), `FANPORTFOLIO` (fan!), `SunlightStatus`, `EBID*`
(EBIDINFO/NUMBER/STATUS), `EVLASTSYNC` → **verwerfen**. Marketing-Automation ist
nicht im Zielsystem. Eine spätere Anbindung würde eigenständig spezifiziert
(Bereich Integrationen) — **kein** ROADMAP-Platzhalter nötig.

### D-012 — Mitarbeiter- und Helpdesk-Online-Felder raus aus Adressen-Scope

**Status:** vertagt (eigene Bereiche) · **Datum:** 2026-09-08

- **Identity/Employee:** `gwIsEmployee`, `gwIsContact`, `gwIsCompany`
  (Typ-Diskriminatoren), `EmpRecruitmentDate`, `EmpSeparationDate`, `EmpStatus`,
  `gwPersonnelNumber`, `GWISEXTERNALEMPLOYEE`, `gwCostCenter` → Bereich **Identity**.
- **Portal:** `GWHDOACCESSTYPE`, `GWSSERVICEPASSWORDSET`, `GWSSTATUS`, `GWSTYPE`,
  `HDBLOCKEDFORSUPPORT` → Bereich **Portal** (das neue Portal ersetzt „Helpdesk online").
- Keins dieser Felder wird ein Company-/Person-Feld. Migration entsprechend gefiltert.
- **Kollision:** `GWSSTATUS`/`GWSTYPE` existieren auch in `Tickets.xml` — dort
  eigene Bedeutung (Ticket-Status/-Typ). Nicht verwechseln.

### D-013 — DSGVO-Einwilligungen: historisiertes `consent`-Log

**Status:** entschieden (Detail offen) · **Datum:** 2026-09-08

- Tabelle `consents`: `person_id`, `channel` (enum: `fax` · `mail` · `post` ·
  `sms` · `telefon`), `status` (`erteilt` · `widerrufen`), `granted_at` /
  `revoked_at`, `source` (Formular / mündlich / Import / …).
- Historisierbar (Nachweis wann/wodurch erteilt bzw. widerrufen) — nicht als Flags.
- `AVV` (Auftragsverarbeitungsvertrag) ist **kein** Kanal-Consent → separates
  **Company**-Attribut (Vorschlag: `avv_signed_at` / `avv_status`).
- `DSGVO` (Flag „Datenschutzgrundverordnung") → vermutlich „DSGVO-Info erteilt";
  im Cluster-Durchlauf gegen das Consent-Log prüfen.
- Betrifft die Person-Spec → gehört in `.docs/04-domain/`.

### D-014 — Adress-Slots: Sitz → Company; Lieferung → Location; Privat → verworfen

**Status:** entschieden · **Datum:** 2026-09-08

- Slot 1 (Haupt: `Street1`/`Zip1`/`Town1`/`Suburb1`/`COUNTRY1`/`GWSTATE1`/
  `PoBox1`/`PoBoxZip1`/`POTOWN1`/`InHouseZip`) → **Address (Sitz)** an der Company.
- Slot 2 (Lieferung: `Street2`/`Zip2`/`Town2`/…/`COUNTRY2`/`GWSTATE2`/`PoBox2`/…)
  → **Location** (eine abweichende Lieferadresse ist eine Location).
- Slot 3 (Privat: `Street3`/`Zip3`/`Town3`/…/`COUNTRY3`/`GWSTATE3`) → **verworfen.**
- Bestätigt: **Person hat keine eigene Adresse** (nur über Company erreichbar, D-002).

### D-015 — Anrede minimal: `Person.gender` + `Person.title`, Briefanrede generiert

**Status:** entschieden · **Datum:** 2026-09-08

- `Person.gender` (enum) ← `GWGENDER`
- `Person.title` (string) ← `Title`
- Briefanrede wird bei Dokumenterstellung generiert, **nicht** gespeichert →
  Bereich Dokumente.
- Verworfen: `AddressTerm`, `AddressLetter`, `Anrede2/3/4`, `gwBranch` (Briefanrede F).
- `gwAdditionalInfo1` (Namenszusatz), `Birthday`, `gwBirthPlace`, `gwNationality`,
  `gwDenomination` → im Cluster-Durchlauf entscheiden (Tendenz: Namenszusatz →
  Person, Rest verwerfen).

### D-016 — `responsible_sales_id` / `responsible_service_id` = echter FK auf User

**Status:** entschieden · **Datum:** 2026-09-08

- `Company.responsible_sales_id`, `Company.responsible_service_id` → `users.id`
  (nullable FK). UI: Select mit Benutzername. Genutzt in Auswertungen/Filtern
  („Verantwortlicher = aktueller Benutzer / = L. Everding").
- **Rein informativ — keine Autorisierung** (Rechte kommen aus der Abteilung,
  `AUTHORIZATION.md`).
- FK zeigt vorerst auf `users`; ggf. Re-Point auf `Employee`, wenn das Modell steht (D-012).
- Legacy-Dubletten: `Mitarbeiter` (= Verantwortlicher Sales) → auf
  `responsible_sales_id` gemappt; `GWTRUSTEE` („Verantwortlicher (alt)") → verworfen.

### D-017 — Company-Klassifizierung: alles weg außer Fachrichtung

**Status:** entschieden · **Datum:** 2026-09-08

- `Company.medical_specialty` (Fachrichtung) ← `Fachrichtung` — **bleibt,
  nicht verhandelbar** (es sind Praxen). Kontrollierte Liste; Werte offen.
- **Verworfen:** `Category`, `ITDKLASSIFIZIERUNG` (Klassifizierung), `TurnOver`,
  `TurnOverGroup`, `Quelle1/2`, `ITDANZAHLMA`, `Interessean1/2`, `FirstContact(Date)`,
  `letzteAktion`, `LASTCONTACT*`, `Referenzkunde`, `Rebate`, `Payment`, `CurrencyNat`,
  `BudgetfKauf`, `Kaufdatum`, `wasgekauft`, `Anschaffungwan`, `Rckkauf`, `Gifts`,
  `LeisureActivities`, `KSgemeldetan`, `KSgewhrtan`, `durchwenwas1/2`,
  `Eingangsdatum1/2`, `was`, `explInt1`, `lostOrderan`.
- **Nicht** von D-017 erfasst (→ Bereich Service prüfen): `FAHRTZONENPAUSCHALE`,
  `VERSICHERUNG`/`VERSAKTIV`, `LEASING`, `EmpStatus` (Label „Servicevertrag").

### D-018 — Soft-Delete + Papierkorb + Auto-Prune, plattformweit

**Status:** entschieden (Details offen) · **Datum:** 2026-09-08

Es gibt **kein** „archiviert/inaktiv"-Zustand. Ein Datensatz ist **aktiv** oder
**gelöscht** (im Papierkorb).

- Jedes Domänenmodell nutzt Laravel `SoftDeletes`.
- Pro Modelltyp ein **Papierkorb** (Ansicht der `deleted_at`-Datensätze,
  Wiederherstellen möglich) — wie im Altsystem.
- **Auto-Leerung**: soft-gelöschte Datensätze werden nach **~30 Tagen**
  endgültig entfernt (Laravel `Prunable` + geplanter `model:prune`).
- Legacy `gwDeactivated` → bildet den Soft-Delete-Zustand ab, **keine** Spalte.
- **Plattform-Konzern** — gehört in `.ai/rules` (architecture) + Infra-Doc +
  eigenen ROADMAP-Slice (Voraussetzung für die Fach-Slices).
- **Offen:** genaue Modell-Liste (auch Pivot wie `company_contacts`?),
  Retention 30 T bestätigen, Zugriff auf Papierkorb (welche Rolle).

### D-019 — `Company.medical_specialty` = Lookup-Tabelle

**Status:** entschieden (2 Werte klären) · **Datum:** 2026-09-08

- Tabelle `medical_specialties` (`name`, `is_active`), `Company.medical_specialty_id`
  (nullable FK). Vom Nutzer editierbar, für Segmentierung/Filter genutzt.
- Seed-Werte (verbatim aus Auswahlliste Altsystem): Pulmologen · Institution ·
  Hdin · Orthopäden · Dermatologen · Veterinäre · Radiologen · Chirurgen ·
  Kardiologen · HNO · Sportmedizin · USVE · Bahnarzt · Rheumatologen · Hebammen ·
  Werksarzt · Unfallchirurgie · Sanitätshaus · Heilpraktiker · Phlebologie · Neurologen
- **Offen:** „Hdin" und „USVE" — Abkürzungen klären.

### D-020 — Fahrtzone ist ein Company-Attribut (Detail: Bereich Service)

**Status:** entschieden · **revidiert D-059/D-063** (`Company.travel_zone_id` → `travel_zones`; einmal je Anfahrt) · **Datum:** 2026-09-08

- Eine **Fahrtzone** gehört zur Company (Grundlage der Service-Anfahrtskosten).
  `Company.travel_zone` (bzw. `travel_zone_id`) — Feld lebt auf Company.
- Legacy `FAHRTZONENPAUSCHALE` (denormalisierter Betrag) → **verworfen**.
- Fahrtzonen-Modell selbst (Zonen, Preise) → Bereich Service/Billing.

### D-021 — Company-Name zweiteilig: `name` + `name_addition`

**Status:** entschieden · **Datum:** 2026-09-08

Wie Vor-/Nachname, aber für Firmen: `Company.name` (← `CompName`) +
`Company.name_addition` (← `CompName2`, „Adresszusatz", bleibt in der Form).
`CompName2` also **nicht** verwerfen.

### D-022 — `NOTES2` verwerfen; `CompanyContact.department` als eigenes Feld

**Status:** entschieden · **Datum:** 2026-09-08

- `NOTES2` (2. „Schlagworte") → verworfen; nur `Notes` → `Company.notes`.
- `Department` („Abteilung", in welcher Abteilung der Ansprechpartner sitzt) →
  eigenes `CompanyContact.department` (nullable String), **nicht** in `role`
  gefaltet (andere Achse als die Rolle aus D-005).
- `Birthday` + `gwBirthPlace` + `gwDenomination` + `gwNationality` +
  `BirthdayGreetings` + `ChristmasGreetings` → **verworfen** (keine betriebliche Relevanz).

### D-023 — Soft-Delete: Löschen + Papierkorb nur Management/Backoffice

**Status:** entschieden · **Datum:** 2026-09-08 · verfeinert D-018

- **Löschen** (Soft-Delete) eines Datensatzes ist eine **privilegierte Aktion** —
  nur Abteilung **Management / Backoffice**.
- **Papierkorb ansehen + Wiederherstellen** ist auf dieselbe Abteilung gescoped
  (eigene Berechtigung, nicht jeder eingeloggte User).
- Gilt für **alle** Domänenmodelle inkl. Pivots (`company_contacts` …).
- Retention **30 Tage** fix, danach `model:prune`.
- Verbindet D-018 mit dem RBAC-Bereich: die `delete`/`restore`/`forceDelete`
  Policy-Abilities werden nur der Management/Backoffice-Abteilung erteilt.

### D-024 — `Address`: Geocoding bleibt (wichtig)

**Status:** entschieden · **Datum:** 2026-09-08

- `Address`: `latitude` · `longitude` (nullable decimal), `geocode_status`
  (enum: `pending` · `ok` · `failed` · `manual`).
- Adress-Prüfung getrennt davon: `verified_at` · `verified_by` (← Legacy
  „Geprüft am/durch").
- **Wichtig** — Grundlage der **Fahrtzone** (D-020) / Techniker-Routing.
- Geocoding-**Mechanismus** (Provider, Trigger) → Bereich Integrationen; die
  Felder leben auf `Address`.
- `GEOCODESTATUS`, `GWEXTERNALADDRESSDATE`, `GWEXTERNALADDRESSNAME` → hierauf gemappt.

### D-025 — `Address`- und `Person`-Feldform final

**Status:** entschieden · **Datum:** 2026-09-08

- **`Address` (Sitz + Location):** `street` · `house_number` (separat, nullable) ·
  `postal_code` · `city` · `district` (Teilort, nullable) · `country_code`
  (default `DE` — aktuell nur DE-Kunden) · `state` (Staat/Region, nullable) ·
  `po_box` · `po_box_postal_code` · `po_box_city` (alle nullable) · +
  Geocoding/Prüfung aus D-024.
- **`Person`:** `first_name` · `last_name` · `name_suffix` (Namenszusatz, nullable,
  z. B. „Dr. med.") · `title` (nullable — kann mit `name_suffix` verschmelzen,
  vorerst beide) · `gender` (enum: `maennlich` · `weiblich` · `divers` ·
  `unbekannt`) · `locale` (default `de`).

### Bereich-Übergabe → Service (Adressen-Zerlegung)

- **„Melder"**: kein freistehender Melder. Ein Servicefall hat `reported_by`
  → **CompanyContact** (Pflicht, muss ein bestehender Kontakt der Company sein).
  Detail im Service-Bereich.
- **Device / Praxis-IT** (`DO_SVV_PRAXISSW*`, `SERVER_*`, `SONOGDT*`,
  `DO_SVV_PRAXIS_ASP` — 19 Felder) → Device-/ServiceContract-Spec.
- **Fahrtzone** (D-020): Company-Attribut, Zonen-/Preismodell im Service-Bereich.

---

## Bereich: Plattform — Identität / Employee / RBAC / Abteilungen

> **Status: Interim synthetisiert (2026-09-08).** Ergebnis:
> [`../03-security/IDENTITY_RBAC.md`](../03-security/IDENTITY_RBAC.md). SSO bleibt
> offener ROADMAP-Slice (8 Azure-Admin-Fragen unten). Audit-Ausbau offen.

Kontext: `.docs/03-security/AUTHORIZATION.md` + `SECURITY.md`, `CRM.md` „Mitarbeiter"
(`User → Employee → Department → Roles/Permissions`). Kein Permission-Package
installiert. Blockiert: D-012 (Mitarbeiter-Felder), D-016 (`responsible_*`),
D-023 (Papierkorb nur Management/Backoffice) und **jede Policy jedes Moduls**.

### D-026 — Employee = User (ein Modell)

**Status:** entschieden · **Datum:** 2026-09-08

Kein separates `Employee`-Modell. Mitarbeiter-Attribute leben auf `users`.
(CRM.md-Kette `User → Employee → …` wird zu `User → Rollen/Abteilung`.)

### D-027 — Mitarbeiter-Login: SSO-only über Microsoft Entra ID

**Status:** entschieden (Integration offen — großer Brocken) · **Datum:** 2026-09-08

- Dormed sitzt in Azure / Entra ID. Mitarbeiter melden sich **ausschließlich per
  SSO** an (OIDC). **Kein Login-Formular** im CRM-Bereich. Keine doppelte
  Nutzerpflege.
- `users` bekommt `entra_oid` (stabiler Entra-Objektschlüssel, unique) als
  Identitäts-Anker; kein Passwort für Mitarbeiter.
- **Offen (Azure-Admin nötig):** siehe Abschnitt „Entra-Integration — offene Punkte".
- **Portal/Shop-Kunden** sind **nicht** in Entra → eigener Auth-Pfad, eigener
  Bereich (Portal). `users` = Mitarbeiter; Kunden-Accounts separat.

### D-028 — Bypass: `users.is_admin`-Flag nur für IT/Bootstrap

**Status:** entschieden · **Datum:** 2026-09-08

- Ein hartes `is_admin` (bool) für 1–2 IT-/Notfall-Accounts. `Gate::before` gibt
  `is_admin` immer `true`.
- Bootstrap: die Entra-`oid`s der Bootstrap-Admins stehen in der Config
  (`config('identity.bootstrap_admin_oids')`); beim ersten SSO-Login wird
  `is_admin` gesetzt. Löst das Henne-Ei-Problem (erste Deploy, keine User).
- Management läuft **normal** über Rollen/Permissions, **kein** impliziter Bypass.

### RBAC — Empfehlung (zur Bestätigung)

**Handgerollt, gespeist aus Entra App Roles** — nicht `spatie/laravel-permission`:

- IT definiert in der Entra-App-Registrierung **App Roles** (`management` · `sales`
  · `service` · `accounting` · `it` · ggf. `readonly`). IT weist Mitarbeiter/
  Gruppen diesen Rollen zu. Entra liefert bei Login den `roles`-Claim.
- Das CRM ist **reiner Konsument**: beim Login `roles`-Claim → `users.roles`
  (JSON/Pivot). Kein Rollen-Management im CRM.
- **Permission-Katalog** im Code (`module.resource.action`-Strings, je Modul
  beigesteuert). **Rolle → Permissions**-Map in `config/authorization.php`
  (`management` → alle; `sales` → companies/contacts/opportunities/…; `service`
  → companies.view + service-cases/*; …).
- `PermissionService` / `Gate::before`: `$user->can($ability)` = eine der
  Rollen des Users gewährt `$ability` **oder** `$user->is_admin`.
- Kein Package, weil Entra die Zuweisungs-Autorität ist — `spatie` glänzt nur bei
  In-App-Rollenverwaltung.
- Individuelle Overrides (`user_permission_grants`) erst bei konkretem Bedarf.

### Entra-Integration — offene Punkte (brauchen Azure-Admin)

1. **App Roles vs. Gruppen vs. `department`-Claim** — kann IT App Roles anlegen &
   zuweisen (empfohlen)? Oder müssen wir bestehende Sicherheitsgruppen (GUIDs →
   Rolle in Config mappen) konsumieren?
2. **Rollen-/Abteilungsliste** operativ: Management · Vertrieb · Service (Innen-/
   Außendienst getrennt?) · Buchhaltung · IT — vollständig? Techniker eigene Rolle
   mit eingeschränktem Zugriff?
3. **Externe Mitarbeiter** (freie Techniker) — Entra-Accounts (B2B-Gast)?
   CRM-Zugang überhaupt, wenn ja eingeschränkt?
4. **Provisionierung** — reicht JIT (User beim ersten Login anlegen), oder
   nächtlicher Microsoft-Graph-Sync einer Gruppe „CRM-Users", damit
   `responsible_*`-Dropdowns vollständig sind, bevor jemand sich eingeloggt hat?
5. **`department`-Attribut** — wird es in Entra gepflegt / als optionaler Claim
   ausgeliefert? (Für Anzeige + evtl. Default-Filter „meine Abteilung".)
6. **Deaktivierung** — Austritt = in Entra deaktiviert. Graph-Sync darf CRM-User
   auto-deaktivieren + `responsible_*`-Datensätze zur Neuzuweisung markieren?
7. **Legacy-Mitarbeiterfelder** (`gwPersonnelNumber`, Eintritts-/Austrittsdatum,
   `gwCostCenter`, extern-Flag) — im CRM gebraucht oder nur in Entra/HR?
8. **App-Registrierung** — Single-Tenant? Wer besitzt sie (IT)? Redirect-URIs je
   Subdomain (`crm.` / `portal.` / `shop.`).

### D-029 — SSO vertagt; Interim = selbstverwaltete Identität + Rollen

**Status:** entschieden · **Datum:** 2026-09-08 · ersetzt D-027 für die Interim-Phase

- **Entra-SSO ist die Ziel-Lösung** (sauber), kommt aber **im Nachgang** —
  eigener ROADMAP-Slice, wird „oben draufgesetzt".
- **Interim:** selbstverwaltete `users` (E-Mail/Passwort, bestehende Breeze-Auth
  bleibt) + eine eigene Rollen-Tabelle.
- **SSO-additiv vorbereiten:** `users.entra_oid` (nullable, unique) schon jetzt
  anlegen; der SSO-Callback synchronisiert später nur `role_user` aus dem
  `roles`-Claim statt einer UI — Katalog + Gate-Logik bleiben unverändert.
- D-026 (Employee = User) und D-028 (`is_admin`-Flag) bleiben.

### D-030 — Interim-RBAC: `roles` + `role_user`, Katalog in Config (kein Package)

**Status:** entschieden (Rollenliste zu bestätigen) · **Datum:** 2026-09-08

- **Eine** Tabelle `roles` (`key` unique, `name`, `is_active`) + Pivot `role_user`
  (n:m). **Keine** separate `departments`-Tabelle — die Rolle *ist* die
  Abteilung/das Rechtebündel.
- Jeder User hat ≥ 1 Rolle. Eine Rolle kann als `is_primary` für Anzeige markiert sein.
- Permission-Katalog (`module.resource.action`) + `Rolle → Permissions`-Map in
  `config/authorization.php`. `PermissionService::can(User, ability)`.
- `Gate::before`: `is_admin` → true.
- **D-023-Verknüpfung:** Papierkorb/Löschen = Rolle `management` **oder** `backoffice`.
- `spatie/laravel-permission` **nicht** eingeführt (kein Mehrwert bei ~6 Rollen +
  Config-Katalog; Dependency-Freigabe vermieden).
- **Offen:** endgültige Rollen-Liste. Vorschlag: `management` · `backoffice` ·
  `sales` · `service` · `accounting` · `it` · `readonly`.

### D-031 — Rollen (Interim): 6

**Status:** entschieden · **Datum:** 2026-09-08

`management` · `backoffice` · `sales` · `service` · `accounting` · `it`.
Kein `readonly` (wer Zugang hat, hat ≥ 1 Fach-Rolle).
Service **nicht** in Innen-/Außendienst gesplittet — mögliche spätere Verfeinerung.

### D-032 — Kein Self-Service: keine Registrierung, kein Passwort-Reset

**Status:** entschieden · **Datum:** 2026-09-08

- Breeze-**Registrierung** + **Passwort-vergessen/-Reset** Routen **entfernen**.
- Nur **Login** bleibt. Passwort-Reset läuft über IT/Admin.
- User anlegen = Admin-Funktion (Name, E-Mail, Rollen) + Einladungs-Mail zum
  Passwort-Setzen (signierte URL).
- Maximal geschlossen bis SSO (D-029) kommt.

### D-033 — `users` minimal

**Status:** entschieden · **Datum:** 2026-09-08

Zusatzfelder: nur `first_name` · `last_name` · `is_active`. **Weg:** Personalnummer,
Kostenstelle, Ein-/Austrittsdatum, extern-Flag (HR-System, nicht CRM).

---

## Bereich: Domäne — Service (Device / ServiceContract / Maintenance / ServiceCase)

> **Status: synthetisiert (2026-09-08).** Ergebnis:
> [`../04-domain/SERVICE.md`](../04-domain/SERVICE.md) +
> `../00-legacy/xml/{Servicevertraege,Tickets}-Zuordnung.md`. Offen: Enum-Werte
> (contract_type/status/…), `DO_SVV_PRAXISSW*`-Aufteilung, Template-Kategorien,
> State-Machine-Übergänge → nächste Runde. Termine → Bereich Scheduling.

Legacy: `Servicevertraege-NEU.xml` (83 F.), `Tickets.xml` (112 F.), `Termine.xml`
(25 F., anteilig). Prinzipien: `.docs/05-modules/SERVICE.md`. Feeds Rest-Offen aus
`CORE.md` (Fahrtzone D-020, Melder, Device/Praxis-IT aus D-001).

### D-034 — Entitäten-Zerlegung Service

**Status:** entschieden · **Datum:** 2026-09-08

| Entität | Inhalt |
| --- | --- |
| `Device` | Das System: Hersteller, Seriennummer, Artikelnummer, Baujahr, Auslieferungsdatum, OS/SW, Optionen. **Netzwerk-/DICOM-Config als Felder direkt auf Device** (IP, MAC, Gateway, DHCP, Storage-/Worklist-Port+Title, System-Passwort) — kein eigenes `DeviceNetworkConfig`-Modell. |
| `DeviceComponent` | Sonden (1–5), Printer, Wagen, SonoGDT — `n` je Device, **typisiert** (`type`-Enum), je: Artikelnummer, Bezeichnung, Seriennummer. |
| `ServiceContract` | Die Vereinbarung (Art, Datum, Intervall, Status, Kündigung, Zahlung, Versicherungen). |
| `Maintenance` | Die einzelne Wartungsinstanz (`planned_due_at` / `performed_at` / `finalized_at`, SERVICE.md). |
| `ServiceCase` | Störung/Serviceeinsatz — **getrennt** von Maintenance. |

### D-035 — Device existiert nur über einen ServiceContract (1:1, Pflicht)

**Status:** ~~entschieden~~ **REVIDIERT durch D-064** — `service_contract_id` ist jetzt nullable · **Datum:** 2026-09-08

- Kein vertragsloses Device im System. `Device.service_contract_id` **required**,
  unique (1 Device ↔ 1 ServiceContract, DOMAIN.md / ADR-008).
- `ServiceContract.company_id` → Company; `Device.location_id` → Location
  (Gerätestandort, D-007). **Offen:** Company am Vertrag vs. abgeleitet über
  `device.location.company` — in Runde 2 klären.
- ADR-008 bleibt: ein ServiceContract ist **nicht** das vollständige Device-Objekt.

### D-036 — Vertragspreis: nur `current_price` (kein Historien-Modell)

**Status:** ~~entschieden~~ **REVIDIERT durch D-062/D-063** — keine Preise am Vertrag, Preisliste `service_prices` · **Datum:** 2026-09-08

- `ServiceContract`: `maintenance_price` (aktuell, wiederkehrend je Wartung) +
  `travel_flat_rate` (Fahrtzonenpauschale aktuell). Anpassung **überschreibt**.
- Legacy `_VERTRAG` (Ursprung) / `_KHK` (aktuell) / `_EINMAL` → nur „aktuell".
- Historie „wann/warum angepasst" → Audit-Log (Ausbau, s. IDENTITY_RBAC offene Punkte).
- **Offen:** `_EINMAL` = einmalige Einrichtungsgebühr? → ggf. `setup_fee`. Runde 2.
- `PREISANPASSUNG` / `PREISANPASSUNG2025` (Ankündigungs-Flags) → Runde 2.

### D-037 — Nächste Wartung = `performed_at` + Intervall (abgeleitet)

**Status:** entschieden · **Datum:** 2026-09-08

- `ServiceContract.maintenance_interval_months` (12 / 6 / 3 …).
- Fälligkeit der nächsten Wartung = `performed_at` der letzten Wartung + Intervall.
  **Abgeleitet**, kein Freitextfeld. Denormalisiertes `next_due_at` am Vertrag
  (für Planung/Query), neu berechnet bei Wartungsabschluss.
- Legacy `MONAT` (fixer Kalendermonat) → **verworfen** (D-037 wählt rollend).
- Legacy `ERSTEWARTUNG` (Label „letzte Wartung", fehlbenannt), `NAECHSTEWARTUNG`
  → aus Maintenance-Historie abgeleitet, nicht als Vertragsfelder übernommen.
- **Offen:** `MEHRFACHWARTUNG` — Intervall < 12 oder eigener Mechanismus? Runde 2.

### D-038 — Checklisten: versionierte Templates + Report-Instanz

**Status:** entschieden · **Datum:** 2026-09-08

- `checklist_templates`: Sektionen (Sichtkontrolle · Funktionskontrolle ·
  Wartungsarbeiten · …), Items, **Version**.
- `maintenance_report`: friert die genutzte Template-Version ein; je Item ein
  Ergebnis (`ok` · `nicht_ok` · `na` + Notiz).
- Neue/geänderte Prüfpunkte = **neue Template-Version**; bestehende Berichte bleiben
  unverändert (Nachweisintegrität, SERVICE.md „strukturiert und versionierbar").
- Legacy `TICKET_SICHTKONTROLLE_1..10`, `_FUNKTIONSKONTROLLE_1..13`,
  `_WARTUNGSARBEITEN_1..8`, `_ABSCHLUSS_*` → Template-Items bzw. Report-Ergebnisse.

### D-039 — Maintenance und ServiceCase sind vollständig getrennt

**Status:** entschieden · **Datum:** 2026-09-08

Kein gemeinsames „Visit/Einsatz"-Basismodell. `Maintenance` und `ServiceCase`
haben **je eigene** Terminplanung, eigenen Bericht, eigene Positionen, eigenen
Workflow. Bewusst mehr Duplikation für klarere Grenzen (SERVICE.md: „darf nicht
versehentlich als Wartung behandelt werden").

- `Maintenance` ← Wartungszyklus eines `ServiceContract` (D-037).
- `ServiceCase` ← Störungsmeldung; `reported_by` → `CompanyContact` (Pflicht, s. CORE.md).

### D-040 — Positionen am Einsatz → Rechnungsentwurf im Billing (Empfehlung, zu bestätigen)

**Status:** offen (Nutzer unsicher „1 oder 3") — **Empfehlung: Variante 1**

- `Maintenance` / `ServiceCase` tragen je `line_items` (Teile, Arbeitszeit,
  Anfahrt — Bezeichnung, Menge, Einzelpreis). Erfassung durch Techniker/Innendienst.
- Bei Abschluss + Freigabe → **`Invoice` im Billing-Bereich**, die die Positionen +
  Rechnungsempfänger + Adresse **zum Zeitpunkt einfriert** (ADR-006, Billing-Historie).
- Bis zur Rechnungsstellung sind die `line_items` am Einsatz editierbar; die
  `Invoice` ist danach unveränderlich.
- Begründung gegen Variante 3: Billing braucht ohnehin eine echte Invoice-Entität
  (Historisierung, e-Rechnung D-006, KHK/Sage-Sync). Doppelte Summenlogik am
  Einsatz wäre eine zweite Wahrheit.
- **→ Nutzer bestätigt Variante 1?**

### D-041 — Offline-Wartungsbericht: späterer ROADMAP-Slice

**Status:** entschieden · **Datum:** 2026-09-08

Zuerst online-only (server-gerenderte/API-Formulare). Offline-Erfassung
(PWA/lokaler Speicher/Sync/Konfliktbehandlung) = eigener benannter ROADMAP-Slice.
Legacy `ISOFFLINE` / `ISOFFLINE_BEARBEITUNG` → verworfen (kein Zielfeld).

### D-042 — Vertrags-Details Mehrfachwartung / Einrichtungsgebühr / Preisanpassungs-Flags: verworfen

**Status:** entschieden · **Datum:** 2026-09-08

Keins der drei wird in der Zielstruktur gebraucht:
- `MEHRFACHWARTUNG` → verworfen (Kadenz komplett über `maintenance_interval_months`, D-037).
- Einrichtungsgebühr / `KOSTEN_*_EINMAL` → verworfen (kein `setup_fee`).
- `PREISANPASSUNG` / `PREISANPASSUNG2025` (Ankündigungs-Flags) → verworfen
  (Preis = nur `current_price`, D-036; Anpassung überschreibt).

### D-043 — Abrechnungsgrenze: line_items am Einsatz, Rechnungslogik im Billing-Bereich

**Status:** entschieden (meine Entscheidung — Veto möglich) · **Datum:** 2026-09-08

- `Maintenance` und `ServiceCase` tragen je `line_items` (Teile, Arbeitszeit,
  Anfahrt: `description`, `quantity`, `unit_price`, `unit`). Erfassung am Einsatz.
- Bei Abschluss + Freigabe des Einsatzes → **Rechnungsentwurf im Billing-Bereich**.
  Die `Invoice` friert Positionen + Empfänger + Adresse ein (ADR-006).
- **Der genaue Übergabe-Mechanismus** (Invoice-Modell, Nummernkreis, Sammelrechnung
  ja/nein, e-Rechnung, KHK-Sync) wird im **Billing-Bereich** final entschieden.
- Keine eigene Summen-/Steuerlogik am Einsatz (zweite Wahrheit vermeiden).
- Legacy `TICKET_POS1..7_*`, `TICKET_LEISTUNG_POS1_*`, `TICKET_GESAMT_*`,
  `TICKET_MWST` → `line_items` bzw. abgeleitet/Billing.

### D-044 — STK-Messprotokoll: eigenes `measurement_protocol`, feste Struktur

**Status:** entschieden (meine Entscheidung — Veto möglich) · **Datum:** 2026-09-08

DIN EN 62353 ist regulatorisch standardisiert → **nicht** Template-getrieben.

- `measurement_protocol` 1:1 zu `Maintenance` (bzw. `ServiceCase` bei Bedarf).
- Felder: `protection_class` (SK1/SK2), `test_equipment` (Prüfmittel),
  `test_method` (Messverfahren), Messwerte je Schutzklasse: `iega` · `iepa` ·
  `iga` · `ipa` · `rsl` · `uln` (DECIMAL), `evaluation` (`ok` · `nicht_ok`),
  `is_constancy_test` (Konstanzprüfung für KV — `TICKET_ABSCHLUSS_KP`).
- Legacy `TICKET_MESSWERTE_*` (Art, Prüfmittel, Schutzklasse, SK1_*/SK2_*) → hierauf.

### D-045 — Kundenbestätigung: einfache Signatur als Workflow-Gate (jetzt)

**Status:** entschieden (meine Entscheidung — Veto möglich) · **Datum:** 2026-09-08

- Einfache Touch-Unterschrift beim Einsatzabschluss: `signature_image`,
  `signer_name`, `signed_at`, `confirmed_report_version` (welcher Berichtsstand).
- **Workflow-Gate**: ohne Bestätigung kein Status `abgeschlossen` → nicht
  rechnungsfähig. Ausnahme mit Vermerk möglich (niemand vor Ort) — `signed_at` null,
  `unconfirmed_reason` gesetzt.
- Qualifizierte elektronische Signatur → eigener späterer Slice, nur falls
  fachlich/legal erforderlich (SERVICE.md).
- Legacy `BESTAETIGUNG` → hierauf; `ATMOSPHERE` (Stimmung Kunde) → verworfen.

---

## Bereich: Domäne — Scheduling (Termine)

> **Status: synthetisiert (2026-09-08).** → [`../04-domain/SCHEDULING.md`](../04-domain/SCHEDULING.md).
> Offen: type/status-Enum-Werte, Serien-Umsetzungsdetail, Outlook-Sync (ROADMAP).

Legacy `Termine.xml` (25 F., fast Standard-CAS + 1 Dormed-Feld
`DORMEDLOGISTIKERFORDERLICH`). Prinzip (LEGACY_MAPPING): `Appointment` als
Scheduling-Objekt **getrennt** vom fachlichen Vorgang. Klärt Service-Restfrage
(Terminplanung Maintenance/ServiceCase).

### D-046 — Ein geteiltes `appointments`-Modell, polymorph, n je Vorgang

**Status:** entschieden · **Datum:** 2026-09-08

- Eine `appointments`-Tabelle. `schedulable_type` / `schedulable_id` **nullable** →
  `Maintenance` · `ServiceCase` · `Opportunity` · `null` (freier Termin:
  interne Besprechung, Kundenbesuch).
- Ein Vorgang kann **mehrere** Termine haben (Diagnose-Besuch + Reparatur-Besuch).
- Der **Workflow** bleibt pro Vorgang getrennt (D-039) — geteilt ist nur die
  Termin-Datenstruktur + ein gemeinsamer Kalender.
- Modul: `app/Modules/Scheduling/` (`depends_on: [Core, Service, Sales]`? — bzw.
  polymorph ohne harte Modul-Abhängigkeit; im Boundaries-Bereich klären).

### D-047 — Echte Terminserien (RRULE)

**Status:** entschieden (Umsetzungstiefe offen) · **Datum:** 2026-09-08

- `appointments.recurrence_rule` (RRULE/iCal), Serie + Ausnahmen/Einzel-Overrides
  + Einzel-Absagen. `PeriodStart`/`PeriodEnd` → Serien-Zeitraum.
- **Nur Kalender-Serien** (Team-Meeting etc.). Die **fachliche** Wiederholung
  (Wartungszyklus) bleibt D-037 — kein RRULE.
- **Offen:** Serien-Master + generierte Occurrences vs. Rule-Expansion beim Lesen
  → Detail im Slice.

### D-048 — Genau ein zugewiesener Techniker je Termin

**Status:** entschieden · **Datum:** 2026-09-08

- `appointments.assigned_technician_id` → `users` (nullable im Entwurf, sonst gesetzt).
- **Kein** Mehr-Personen-/Teilnehmerstatus-Konzept. Kunden-Kontakt ist **kein**
  Teilnehmer — der Vorgang (`schedulable`) kennt Company/Kontakt bereits.

### D-049 — Outlook/M365-Kalender-Sync: späterer ROADMAP-Slice

**Status:** entschieden · **Datum:** 2026-09-08

CRM-Kalender ist zunächst die einzige Wahrheit. Zwei-Wege-Sync via Microsoft
Graph = benannter späterer Slice (bündelt mit SSO/Entra, D-029).

### D-050 — Termine: Feldform & verworfen

**Status:** entschieden · **Datum:** 2026-09-08

- **Behalten:** `starts_at` · `ends_at` · `all_day` (← `DayAppointment`) ·
  `title` · `location_text` (← `GISDescription`) · `notes` (← `AddComment`/`Notes`) ·
  `type` (enum, freie Termine — Werte offen) · `status` (enum `geplant` ·
  `bestaetigt` · `durchgefuehrt` · `abgesagt` — Werte offen) · `is_online_meeting`
  (+ `meeting_url`) · `logistics_required` (bool ← `DORMEDLOGISTIKERFORDERLICH`) ·
  `reminder_minutes_before` (nullable — einfache Erinnerung; volles Reminder-System später).
- **Verworfen:** `CASAway`, `APP_MANDATORY`, `ISPARTOFEVENT`,
  `APP_ACCEPTABLEREGISTRATIONS`, `APP_GROUP`, `Category`, `CBStatus`, `Keyword`,
  `NOTES2` (Dublette), `Alarm`/`PERIODALARM*` (→ ersetzt durch `reminder_minutes_before`).
- Veranstaltungs-/Event-Management → **nicht** im Zielsystem.

---

## Bereich: Domäne — Sales (Verkaufschancen / Opportunity)

> **Status: synthetisiert (2026-09-08).** → [`../04-domain/SALES.md`](../04-domain/SALES.md).
> Offen: stage-/lead_source-Enum-Werte, `DORMEDABTEILUNG`, Item-Detailfelder.

Legacy `Verkaufschancen.xml` (34 F.). Standard-Opportunity.

### D-051 — Pipeline: eine Achse `stage` (inkl. Endzustände)

**Status:** entschieden (Werte offen) · **Datum:** 2026-09-08

- `Opportunity.stage` (enum): `lead` · `qualifiziert` · `angebot` · `verhandlung`
  · `gewonnen` · `verloren` (Werte final offen).
- Kein separates `status`. Bei `verloren` → `lost_reason` (Text/Enum).
- Legacy `DistributionPhase` + `Status` → beides in `stage`.

### D-052 — Die Opportunity IST das Angebot; strukturierte `opportunity_items`

**Status:** entschieden · **Datum:** 2026-09-08

- **Kein** separates Quote-Objekt. Die Verkaufschance hält die Angebotspositionen.
- `opportunity_items`: `product_id` (→ Produktkatalog/Inventory, später) / `description`,
  `quantity`, `unit_price`, `discount` (?), `unit_cost` (?) für Deckungsbeitrag.
- **Abgeleitet, nicht gespeichert:** `OppTotalAmount` (Summe), `RelativeAmount`
  (Summe × `probability`), `MARGINALRETURN` (Deckungsbeitrag),
  `MARGINALRETURNWEIGHTED`. Legacy-Betragsfelder → verworfen.
- Angebots-PDF → Bereich Dokumente.

### D-053 — Gewinnen: kein Automatismus; `DO_SV_VORGANSSART` verworfen

**Status:** entschieden · **Datum:** 2026-09-08

- `DO_SV_VORGANSSART` gehört zur toten `DO_SV*`-Familie (D-001) — versehentlich in
  den Verkaufschancen gelandet. → **verwerfen**.
- `stage = gewonnen` setzt nur den Status. Ein `ServiceContract` (+ Device) wird
  danach **manuell** angelegt, mit Rückverweis auf die Opportunity
  (`service_contracts.opportunity_id` nullable). Kein Auto-Erzeugen (ADR-010).
- Kann revidiert werden, wenn der reale Vertriebs→Service-Übergabeprozess klar ist.

### D-054 — Verantwortung: nur `owner_id`

**Status:** entschieden · **Datum:** 2026-09-08

- `Opportunity.owner_id` → `users` (nullable). Legacy `PersonInCharge` /
  `VENDORINFORMATION` (1) → hierauf; `VENDORINFORMATION2/3`, `AttorneyInFact`
  (Stellvertreter) → **verworfen**.
- **Kein** Territory-Modell. `VCAWKZ` (AWKZ/PLZ/Tour) → verworfen; Zuständigkeit
  über Abteilung (`AUTHORIZATION.md`).

### D-055 — Sales: übrige Felder

**Status:** entschieden (Enums/2 Flags offen) · **Datum:** 2026-09-08

- **Behalten:** `company_id` (← `AccountInformation`) · `number` (← `OPPORTUNITYNUMBER`) ·
  `probability` (%, ← `Probability`) · `customer_budget` (currency, nullable, ← `BUDGET`) ·
  `payment_terms` (← `ZAHLUNGKONDITIONEN`) · `lead_source` (enum/Lookup — Werte offen,
  ← `Source`) · `opened_at` (← `Start_dt`) · `expected_close_at` (← `end_dt`) ·
  `notes` (← `Keyword`/`Notes2`) · `competitor` + `competitor_note` (Strings,
  ← `Competitors`/`CompetitorNotes` bzw. DORMED-Dubletten) · `cooperation_type` +
  `cooperation_partner` (Strings, ← `KOOPERATION`/`KOOPERATIONSPARTNER`).
- **Verworfen:** `CurrencyNat` (nur EUR), `Alarm` (→ Appointment D-046),
  `LASTCONTACTINSALESPROCESS` (abgeleitet), Betragsfelder (D-052), `DORMEDABTEILUNG`
  **offen** (Produktbereich vs. Abteilung — Rückfrage), `ProductPositionsDisplay` (→ items).

---

## Bereich: Domäne — Billing (Rechnungen)

Greenfield (kein Legacy-XML). Hängt an: Service (D-043 line_items → Invoice),
Core (D-004 Rechnungsempfänger, D-009 Debitor/KHK), ADR-006 (strukturierte Daten
= Wahrheit, PDF = Repräsentation), DOMAIN.md (Rechnungen historisieren).

### D-056 — KHK/Sage wird abgelöst; Billing-Modul = vorerst Fakturierung + Debitoren

**Status:** Richtung entschieden (Scope-Detail offen) · **Datum:** 2026-09-08

- Die Sage-KHK soll **langfristig vollständig durch den Monolithen abgelöst**
  werden. Der KHK-Sync (D-009) ist eine **Übergangsbrücke**, kein Dauerzustand.
- **Vorerst** = `Billing`-Modul deckt: Rechnungserstellung + Nummernkreis +
  Historisierung + Versand + Debitoren/offene Posten. Zahlungseingang/Mahnwesen
  zunächst noch Sage, Status-Rücksync ins CRM.
- **Offene strategische Frage:** vollwertige Buchhaltung (Kontenrahmen, GuV/BWA,
  DATEV/ELSTER) im Monolithen — eigenes Modul `Accounting` getrennt von CRM, oder
  „ein unified System für Mitarbeiter". → **eigener Entscheidungspunkt** (nicht jetzt),
  ROADMAP-Direction „Sage-Ablösung".

### D-057 — Rechnungsquellen

**Status:** entschieden · **Datum:** 2026-09-08

- **Service-Einsatz** (Maintenance / ServiceCase, D-043): abgeschlossen + bestätigt
  → Rechnungsentwurf mit `line_items` + Vertragspreis (`maintenance_price`) + Anfahrt.
- **Gewonnene Opportunity** (Geräteverkauf, D-052): `opportunity_items` → Verkaufsrechnung.
- **Manuelle Rechnung** (freie Positionen) — **begrenzt**, permission-gated
  (`billing.invoices.create_manual`), Ausnahme nicht Norm.
- **Keine** separate wiederkehrende Vertragsgebühr-Rechnung — die Grundgebühr
  läuft über die Wartungsrechnung mit.

### D-058 — Invoice-Modell e-Rechnungs-fähig; Format-Export = früher ROADMAP-Slice

**Status:** entschieden · **Datum:** 2026-09-08

- Das `Invoice`-Datenmodell trägt alle Pflichtfelder für **ZUGFeRD / XRechnung**
  (Steuerkategorien, Einheiten-Codes, Zahlungsmittel, Leitweg-ID falls B2G,
  Liefer-/Leistungszeitraum, strukturierte Positionen).
- Der eigentliche Format-Export (ZUGFeRD-PDF/A-3, XRechnung-XML) ist ein eigener
  Slice — **früh** in der ROADMAP (DE-Pflicht B2B gestaffelt ab 2025).
- Empfang von Lieferanten-e-Rechnungen → später (mit Lieferanten-Bereich, D-008).

### OFFEN — die „Riesen-Problematik" bei den Abrechnungen

Der Nutzer hat aktuell ein großes Problem bei den Abrechnungen (Kontext:
Sammelrechnung / Rechnungsempfänger ≠ Leistungsempfänger / Managementgesellschaften).
**Muss vor dem Invoice-Modell verstanden werden** — offene Beschreibung durch den Nutzer.

### D-059 — Maintenance = Einsatz/Anfahrt, bündelt 1..n Serviceverträge; Fahrtzone von der Company

**Status:** Richtung entschieden (viele Sub-Fragen offen — Mehr-Turn-Thema) · **Datum:** 2026-09-08

**Problem (heute):** Ein Ticket je Wartung je Gerät. Werden 3 Geräte einer Praxis
in **einer Anfahrt** gewartet → 3 Einzeltickets → 3 Einzelrechnungen, und der
Innendienst muss **manuell** sicherstellen, dass die Fahrtzone nur **einmal**
berechnet wird. Geräte können in einer Anfahrt gemeinsam gewartet werden — oder
bewusst nicht (unterschiedliche Fälligkeit + Kundenwunsch → 2 getrennte Anfahrten).

**Lösung (Richtung):**
- **`Maintenance` = der Vor-Ort-Einsatz / die Anfahrt**, gebunden an genau **eine**
  `Company` + `Location`. Bündelt **1..n** `ServiceContract`s/Devices.
- Je gebündeltem Gerät ein eigener **`MaintenanceReport`** + **`MeasurementProtocol`**
  + eigene `line_items`; jedes Gerät steuert seinen `maintenance_price` (vom Vertrag) bei.
- **Fahrtzone NICHT mehr am Vertrag** (revidiert D-020/D-036): `ServiceContract`
  hat nur `maintenance_price`. Die Fahrtzone wird aus der **Company/Institution**
  abgeleitet (`Company.travel_zone_id` → `travel_zones`-Lookup: Zone → Pauschale).
- **Rechnung je `Maintenance`**: Fahrtzone **einmal** + Σ (je Gerät:
  `maintenance_price` + `line_items`).
- **Kein Auto-Erkennen** „gleiche Institution, gleicher Tag → keine 2. Fahrtzone".
  Die Bündelung ist eine **bewusste Planungsentscheidung**: Innendienst legt EINEN
  Einsatz an und fügt die Geräte hinzu. Zwei Anfahrten = zwei `Maintenance`.
- **Termine bleiben einzeln** (je Gerät, back-to-back), verknüpft zum selben
  `Maintenance` (`Appointment.schedulable` → `Maintenance`; n je Maintenance, D-046).

**Revidiert:** SERVICE.md `Maintenance` (war: 1 FK `service_contract_id`) → braucht
`company_id` + `location_id` + Pivot/Sub-Modell `maintenance_devices`
(`maintenance_id`, `service_contract_id`, → 1 Report + 1 Protokoll je Zeile).
D-020: Fahrtzone Company statt Vertrag.

**Offene Sub-Fragen (nächste Turns):**
1. Gilt dieselbe Bündelung für `ServiceCase` (mehrere Störungen einer Praxis in einer Anfahrt)?
2. `next_due_at` je Gerät: aus `Maintenance.performed_at` des jeweiligen `maintenance_devices`-Eintrags — bestätigt?
3. Darf ein Einsatz Geräte **verschiedener Verträge mit verschiedenen Intervallen** bündeln? (Ja — genau der Zweck; jedes Gerät eigene Fälligkeit.)
4. **Sammelrechnung** (eine Rechnung für mehrere Einsätze/Praxen an eine Managementgesellschaft, D-004) = **separate Ebene** über der Einsatz-Rechnung — eigener Turn.
5. Was, wenn Geräte in einem Einsatz unterschiedliche `billing_company_id` haben? (Sollte nicht — 1 Einsatz = 1 Company = 1 Rechnungsempfänger. Bestätigen.)
6. Teil-Wartung: ein geplantes Gerät wird vor Ort doch nicht gewartet (kein Zugang) — wie im Einsatz/Rechnung abbilden?
7. `travel_zones`-Lookup: Zonen-Definition (PLZ-Bereiche? manuell je Company?) + Pauschalen.

### D-060 — ServiceCase: 0..n Geräte, keine Vertragskosten, Vertrag nur für Sonderkonditionen

**Status:** entschieden · **Datum:** 2026-09-08

- Ein `ServiceCase` betrifft **0..n** Geräte (mehrere möglich, oder keins) →
  Pivot `service_case_devices` statt einem `device_id`-FK (revidiert SERVICE.md).
- **Keine** Kostenableitung aus dem Vertrag — alle Positionen sind ad-hoc
  (Teile + Arbeitszeit). Kein `maintenance_price`.
- Der Vertrag wirkt nur über **Sonderkonditionen**: hat das Gerät/die Company
  einen (Full-)Servicevertrag → reduzierter Satz auf Mehraufwand/Arbeitszeit.
  → `ServiceContract` braucht Konditionsfelder (Stundensatz / Rabatt) — Detail offen.
- Fahrtzone: aus der Company, **einmal je ServiceCase-Anfahrt** (analog D-059) —
  **zu bestätigen**.
- **Keine** Bündelung mehrerer Verträge wie bei `Maintenance` (D-059) — ServiceCase
  ist sein eigenes Ding.

### D-061 — Teil-/Nicht-Wartung: 50 % der Wartungspauschale

**Status:** entschieden · **Datum:** 2026-09-08

- Ein eingeplantes Gerät wird vor Ort **nicht** gewartet (kein Gerätezugang) →
  **50 % der Wartungspauschale** für dieses Gerät berechnet; weiterer Termin nötig
  (seltener Fall). `maintenance_devices.status` = `durchgefuehrt` |
  `nicht_durchgefuehrt`.
- Häufiger: **gesamter Praxiszugang** nicht gegeben → die ganze Wartung findet
  nicht statt. Dann: **auf ALLE eingeplanten Geräte 50 % der Wartungspauschale**
  **+ die volle Fahrtzone**.
- Rechnungsposition je `maintenance_device` = 100 % oder 50 % von
  `contract.maintenance_price`, je nach Status.

**Revidiert SERVICE.md** (D-059 + D-060 + D-061):
- `Maintenance`: `company_id` + `location_id` + `scheduled_date` / `performed_at` /
  `finalized_at` + `assigned_technician_id` + `status`. **Kein** `service_contract_id`.
- `maintenance_devices`: `maintenance_id`, `service_contract_id`, `status`
  (`durchgefuehrt` | `nicht_durchgefuehrt`) → `hasOne MaintenanceReport`,
  `hasOne MeasurementProtocol`, `hasMany line_items`. Abrechnung: 100 %/50 % von
  `contract.maintenance_price`.
- `ServiceContract`: **kein** `travel_flat_rate` mehr (D-059); + Konditionsfelder
  für ServiceCase-Sonderkonditionen (D-060, offen).
- `Company`: `travel_zone_id` → `travel_zones` (Zone, `flat_fee`).
- `ServiceCase`: `service_case_devices`-Pivot (0..n); Positionen ad-hoc; Fahrtzone
  aus Company.

### D-062 — Servicepreise kommen aus einer Preisliste, gestuft nach Vertragsstatus — nicht vom Vertrag

**Status:** Richtung entschieden (Preislisten-Struktur offen) · **Datum:** 2026-09-08 · **revidiert D-036**

- **Keine Preisfelder** am `ServiceContract` (kein `maintenance_price`, kein
  `travel_flat_rate`, kein `service_hourly_rate`).
- Es gibt eine zentrale **Preisliste**. Preise (Stundensatz, Wartungspauschale,
  ggf. Fahrtzone) werden dort geführt, **gestuft nach Vertragsstatus**:
  - aktuell: Stundensatz **25 €** Vertragskunde / **30 €** Nicht-Vertragskunde.
- Der `ServiceContract` wirkt nur als **Gate**: hat die Company/das Gerät einen
  (Full-)Servicevertrag → Vertrags-Tarif, sonst Standard-Tarif.
- `SERVICE.md`-Umbau (D-059/D-060/D-061) **pausiert**, bis die Preislisten-Struktur
  steht (Billing/Inventory-Bereich).

**Offen (Preisliste):**
1. Wartungspauschale — ein Pauschalbetrag, oder je Gerätekategorie/-typ verschieden?
2. Nur 2 Stufen (Vertrag/Nicht-Vertrag), oder auch kundenindividuell verhandelte Preise?
3. Fahrtzonenpauschale — auch gestuft nach Vertrag, oder flat je Zone?
4. Preisliste zeitversioniert (Preis ab Datum X) — für Preisanpassungen?
5. Eine Preisliste, oder mehrere (Service / Produkte / Ersatzteile getrennt)?

### D-063 — Eine Service-Preisliste; Wartungspauschale je Geräteklasse × Tarifstufe; Snapshot bei Eintragung

**Status:** entschieden · **Datum:** 2026-09-08

- **Eine** Preisliste für **alle Serviceleistungen** (`service_prices`).
  Ersatzteile **nicht** darin — die laufen **per Kostenvoranschlag** (Freitext-
  Position mit selbst eingetragenem Preis).
- **Wartungspauschale**: je **Geräteklasse** (genau **2** Klassen) × **Tarifstufe**
  → 4 Werte.
- **Stundensatz** (Arbeitszeit): 2 Werte — `contract` **25 €** / `standard` **30 €**.
- **Tarifstufe** = hat das **angefasste Gerät** einen Servicevertrag → `contract`,
  sonst `standard`. **Nicht** company-, nicht kundenindividuell.
- **Fahrtzonenpauschale**: `travel_zones.flat_fee` — **ein** Wert je Zone,
  **identisch** für Vertrags- und Nicht-Vertragskunden, unabhängig vom Vertrag.
- **Snapshot**: im Moment der Eintragung wird der Preis auf die Position/den
  Charge kopiert; spätere Preislistenänderungen ändern bestehende Einträge/
  Rechnungen **nicht**. → keine zeitversionierten Preislisten-Zeilen nötig.
  Offen: exakter Moment (bei Anlage der Position vs. bei Wartungsabschluss).

Vorschlag Struktur:
- `service_prices`: `item` (`maintenance_flat` · `hourly_rate` · …),
  `device_class` (nullable — nur bei `maintenance_flat`), `tier`
  (`contract` · `standard`), `amount`.
- `travel_zones`: `name`, `flat_fee`, `is_active`.
- `Device.device_class` (enum `1` | `2` — Namen offen).

### D-064 — Device kann ohne Servicevertrag existieren — revidiert D-035

**Status:** entschieden · **Datum:** 2026-09-08

- `Device.service_contract_id` **nullable**. Ein Gerät ohne (Full-)Servicevertrag
  ist zulässig (Standard-Tarif; ServiceCase möglich, keine Wartungs-Zyklen).
- `Maintenance`-Bündelung (D-059) betrifft nur Geräte **mit** Vertrag.
- ADR-008 bleibt: der Vertrag ist nicht das Device-Objekt.
- **Offen:** wie kommt ein vertragsloses Device ins System (Verkauf ohne Vertrag /
  Erfassung bei erstem ServiceCase)? → Runde Device-Erfassung.

### D-065 — Wartungspauschale bleibt am Vertrag (fixiert); Preisliste nur für neue Verträge/Angebote — revidiert D-062/D-063

**Status:** entschieden · **Datum:** 2026-09-08

- `ServiceContract.maintenance_price` **existiert** — der Vertrag ist auf **einen
  Preis fixiert**, festgelegt bei Vertragserstellung (nach angenommenem Angebot /
  Auftragseingang) aus der Preisliste zu diesem Zeitpunkt. Spätere
  Preislistenänderungen wirken **nicht** auf bestehende Verträge.
- **Preisliste** (`service_prices`, Wartungspauschalen je Geräteklasse × Tarif):
  ausschließlich für (a) das Erstellen **neuer Angebote** (Opportunity-Positionen,
  D-052) und (b) das **Fixieren eines neuen Vertrags** auf einen Preis.
  **Manuell** gepflegt. Keine Wirkung auf bestehende Verträge.
- Bei einem `Maintenance`-Einsatz: **Snapshot** von `contract.maintenance_price`
  → `maintenance_device.maintenance_fee_snapshot`.
- **Fahrtzone ist komplett getrennt** — `travel_zones.flat_fee` je Zone, **nicht**
  Teil der Preisliste, Preisliste hat **keine** Wirkung darauf (D-063 bleibt hier gültig).
- Stundensatz (ServiceCase-Arbeitszeit): bleibt preislisten-getrieben zum
  Zeitpunkt des Falls, Tarifstufe nach Vertragsstatus des Geräts (D-063,
  **nicht** am Vertrag fixiert — nur die Wartungspauschale wird fixiert).

Flow:
```
service_prices (manuell) → Angebot (Opportunity) → angenommen/Auftrag
   → ServiceContract.maintenance_price := Preislisten-Wert (fixiert)
   → Maintenance → maintenance_device.maintenance_fee_snapshot := contract.maintenance_price
   → Rechnung
```

### D-066 — Keine übergreifende Sammelrechnung; 1 Rechnung je Einsatz; Rechnungsempfänger auf `Company`

**Status:** entschieden · **Datum:** 2026-09-08 · präzisiert D-004/D-059

- **Es gibt keine** praxis- oder tagesübergreifende Sammelrechnung, keinen
  Rechnungslauf, keine Bündelungs-Engine.
- Die größte „Sammelrechnung" = **eine `Maintenance`** (mehrere Geräte, ein Tag,
  eine Praxis, D-059) → **eine Rechnung**.
- Diese Rechnung kann an eine **abweichende Rechnungsanschrift** (Praxisgesellschaft)
  gehen: `Company.billing_company_id` → andere `Company` (Praxisgesellschaft ist
  selbst eine `Company`, D-002). **Verschoben** von `ServiceContract` (D-004) auf
  `Company` — so ist je Einsatz genau **ein** Rechnungsempfänger garantiert
  (1 Einsatz = 1 Praxis = 1 Empfänger).
- Rechnungsempfänger einer `ServiceCase`-Rechnung analog aus `Company.billing_company_id`.
- Die Managementgesellschaft bekommt also **viele Einzelrechnungen** (je Praxis-
  Einsatz), nicht eine gebündelte.
- **Konsequenz:** Billing-Modell wird deutlich einfacher — `Invoice` hat **eine**
  Quelle (ein `Maintenance` **oder** eine `ServiceCase` **oder** eine gewonnene
  Opportunity **oder** manuell, D-057), snapshottet Empfänger + Positionen, fertig.

**Revidiert:** `SERVICE.md` `ServiceContract.billing_company_id` → entfällt;
`CORE.md` `Company.billing_company_id` ergänzen.

### D-067 — Rechnungsnummernkreis: jährlich, `RE-{Jahr}-{lfd.}` / `GS-{Jahr}-{lfd.}`

**Status:** entschieden · **Datum:** 2026-09-12

- Nummernkreis pro Kalenderjahr, Reset zum 1.1. Format `RE-2026-000123`.
- Gutschriften (D-069) haben einen **eigenen** Nummernkreis/Präfix: `GS-2026-000045`.
- Erfüllt §14 UStG (fortlaufend, lückenlos, eindeutig) — lückenlos **je Jahr und
  Beleg-Typ** (Rechnung/Gutschrift getrennt gezählt).

### D-068 — Sage/KHK komplett abgelöst; Monolith übernimmt Fakturierung UND Zahlungsverfolgung vollständig

**Status:** entschieden · **Datum:** 2026-09-12 · **revidiert D-056, D-009**

- **Keine Übergangsbrücke mehr.** D-056s „Sage bleibt für Zahlungseingang/Mahnwesen,
  Status-Rücksync ins CRM" ist **hinfällig** — Sage/KHK hat **keine Relevanz mehr** in
  diesem Programm.
- Der Monolith macht **von Anfang an** Rechnungsstellung, Zahlungsabgleich (D-071) und
  Mahnwesen (D-072) vollständig selbst — kein Sync, keine Doppelerfassung.
- **Vollwertige Buchhaltung** (Kontenrahmen, GuV/BWA) bleibt trotzdem **extern** beim
  Steuerberater-Büro — der Monolith exportiert nur periodisch DATEV-fähige
  Buchungssätze (D-075). Das ist die Antwort auf D-056s offene strategische Frage.
- **Konsequenz für D-009** (`Company.khk_matchcode`/`debitor_number`) → D-073.

### D-069 — Storno/Gutschrift: Vollstorno erzeugt automatisch eine Gutschrift

**Status:** entschieden · **Datum:** 2026-09-12

- Eine bereits **gestellte** (eingefrorene) Rechnung wird nie nachträglich verändert.
  Storno = **Vollstorno**: erzeugt automatisch einen Gutschrift-Beleg (Negativ-Beleg,
  identische Positionen, negierte Beträge) mit Rückverweis `credited_invoice_id` auf
  die Original-Rechnung. Original-Status → `storniert`.
- **Kein** reines Statusflag ohne Beleg (nicht GoBD-konform für bereits versendete
  Rechnungen).
- **Keine Teil-Gutschriften** jetzt (nur einzelne Positionen gutschreiben) — nur
  Vollstorno. Teil-Gutschriften wären ein späterer Ausbau (ADR-010).
- `invoices.type` = `rechnung` | `gutschrift` (eine Tabelle, kein separates Modell).

### D-070 — Leistungsdatum: Einzeldatum, automatisch aus der Quelle

**Status:** entschieden · **Datum:** 2026-09-12

- `Invoice.service_date` (Einzeldatum, kein Zeitraum) — automatisch übernommen aus
  `Maintenance.performed_at` bzw. `ServiceCase.finalized_at`. Kein manuelles Feld,
  kein Zeitraum-Paar.
- Passt zum Modell „1 Anfahrt = 1 Tag = 1 Rechnung" (D-059/D-066).

### D-071 — Zahlungsabgleich: automatisierter Kontoauszug-Import, Matching per Rechnungsnummer, n:m Zahlung↔Rechnung

**Status:** entschieden (Import-Format offen) · **Datum:** 2026-09-12

- **Import**: Bank-Kontoauszug-Import, Format **noch offen** (CAMT.053 wahrscheinlich,
  ggf. MT940) — Datenmodell bewusst **format-unabhängig** gehalten (ein
  `bank_statement_imports`-Log mit `format`-Feld, der eigentliche Parser ist
  austauschbar).
- **Matching**: automatisch per Rechnungsnummer im Verwendungszweck; kein Treffer →
  Warteschlange „nicht zugeordnet" zur manuellen Zuordnung durch die Buchhaltung.
- **`payments` ↔ `invoices` als n:m** (Pivot `payment_invoice` mit `amount` je
  Zuordnung) — bildet sowohl Sammelüberweisungen (eine Zahlung deckt mehrere
  Rechnungen, z. B. Managementgesellschaft, D-004/D-066) als auch Teilzahlungen
  (mehrere Zahlungen auf eine Rechnung) ab.
- `Invoice.payment_status` (abgeleitet aus den zugeordneten `payments`): `offen` ·
  `teilbezahlt` · `bezahlt` · `ueberfaellig` (Fälligkeit überschritten, unbezahlt).

### D-072 — Mahnwesen: 3 Stufen, manuell ausgelöst, keine automatische Gebühr/Zins-Berechnung

**Status:** entschieden · **Datum:** 2026-09-12

- Stufen: **Zahlungserinnerung → 1. Mahnung → 2. Mahnung**. Danach manueller Übergang
  zu Inkasso **außerhalb** des Systems (kein Inkasso-Workflow im Monolithen).
- Jede Stufe wird **manuell** ausgelöst (kein automatischer Cron-Versand) —
  serverseitig erzwungene Reihenfolge (kein Sprung `keine → mahnung_2`,
  Business-Workflow-Guard analog `IDENTITY_RBAC.md`).
- **Keine** automatische Mahngebühr- oder Verzugszins-Berechnung jetzt (§288 BGB) —
  späterer Ausbau bei Bedarf (ADR-010).
- `Invoice.dunning_level` (enum `keine` \| `zahlungserinnerung` \| `mahnung_1` \|
  `mahnung_2`), `last_dunning_sent_at`.

### D-073 — KHK-Felder revidiert: `khk_matchcode` verworfen, `debitor_number` wird interne Kundennummer

**Status:** entschieden · **Datum:** 2026-09-12 · **revidiert D-009**

- `Company.khk_matchcode` → **verworfen** (keine Fremdsystem-Verknüpfung mehr nötig,
  D-068).
- `Company.debitor_number` → **bleibt**, aber ohne Sage/KHK-Bezug: eine **eigene,
  interne Kundennummer** (Referenz auf Rechnungen/Kommunikation). Format/Vergabe
  (manuell vs. automatisch fortlaufend) → Detail bei Umsetzung, kein Sync-Mechanismus
  mehr nötig.

### D-074 — e-Rechnung: Leitweg-ID optional, USt-Kategorien mehrwertig, Zahlungsmittel Überweisung + Lastschrift

**Status:** entschieden · **Datum:** 2026-09-12 · konkretisiert D-058

- **Leitweg-ID** (B2G-Routing-ID): optionales, nullable Feld (auf `Invoice`, ggf.
  gespeist aus `Company`) für die seltenen öffentlichen Auftraggeber (Bahnarzt,
  Werksarzt, `medical_specialties`-Werte aus D-019).
- **USt-Kategorien**: nicht nur 19 % Regelsteuersatz — zusätzlich seltene Sonderfälle
  (Auslandskunden/Export, Reverse Charge). `invoice_items.tax_category` (enum
  `standard_19` \| `reverse_charge` \| `export_tax_free` \| `other_tax_free`),
  `tax_rate`, `tax_amount` je Position (nicht am Invoice-Kopf, da innerhalb einer
  Rechnung theoretisch gemischt).
- **Zahlungsmittel**: `ueberweisung` \| `lastschrift`. Bei Lastschrift zusätzlich
  `sepa_mandate_reference` (+ Mandatsdatum) — Feld auf `Company` oder `Invoice`,
  Detail bei Umsetzung.

### D-075 — Buchhaltung bleibt extern; Monolith exportiert periodisch DATEV-fähige Buchungssätze

**Status:** entschieden · **Datum:** 2026-09-12 · beantwortet D-056s offene strategische Frage

- **Kein** eigenes `Accounting`-Modul (Kontenrahmen, GuV/BWA, ELSTER) im Monolithen.
  Das bleibt beim Steuerberater-Büro / dessen Tool.
- Der Monolith liefert **periodisch einen DATEV-fähigen Buchungssatz-Export**
  (Rechnungen + Zahlungen) — dafür braucht jede `invoice_items`-Position perspektivisch
  ein **Erlöskonto** (Buchungskonto-Zuordnung, Detail/Kontenrahmen bei Umsetzung).
- Kein Rückfluss (keine Daten kommen vom Steuerberater-Tool zurück in den Monolithen).

### D-076 — ServiceCase-Fahrtzone bestätigt: analog Maintenance, einmal je Anfahrt

**Status:** entschieden · **Datum:** 2026-09-12 · bestätigt D-060s offenen Punkt

Wie `Maintenance` (D-059): **eine** Fahrtzonenpauschale je `ServiceCase`-Anfahrt, aus
`Company.travel_zone_id` abgeleitet — unabhängig davon, wie viele Geräte im Case
betroffen sind (D-060). Kein Bündeln mehrerer Verträge wie bei `Maintenance` bleibt
unverändert (ServiceCase ist weiterhin sein eigenes Ding).

### D-077 — Vertragsloses Device: entsteht über Verkauf ODER Direkterfassung im Service

**Status:** entschieden · **Datum:** 2026-09-12 · löst D-064s offenen Punkt

Ein `Device` ohne `service_contract_id` (D-064) entsteht auf **zwei** zulässigen Wegen:

1. **Verkauf** (gewonnene Opportunity, D-053): Gerät entsteht aus dem Sales-Flow, auch
   ohne begleitenden Servicevertrag.
2. **Direkterfassung im Service**: Techniker/Innendienst legt das Gerät „on the fly"
   beim ersten `ServiceCase` an (häufigster Fall: Kunde meldet ein Altgerät, das noch
   nicht im System ist).

Beide Wege sind gleichberechtigt — kein Zwang, jedes Gerät müsse aus einem
Verkaufsvorgang stammen.

---

## Bereich: Domäne — Billing — Status

> **Status: abgeschlossen & synthetisiert (2026-09-12).** Ergebnis:
> [`../04-domain/BILLING.md`](../04-domain/BILLING.md). Entscheidungen D-056 – D-077.
> Offene Rest-Punkte (Bank-Import-Format, Kontenrahmen/DATEV-Detail,
> Mahngebühren/-zinsen später) in `BILLING.md` gelistet.

---

## Bereich: Rand-Klärungen Core/Service (Runde 2026-09-12)

Schließt verbleibende „Rückfrage Nutzer"-Punkte in bereits synthetisierten Bereichen,
bevor neue Bereiche (Inventory/Documents/Communication) aufgemacht werden — macht
`CORE.md`/`SERVICE.md` stichfest für die erste Umsetzungsrunde (ADR-020).

### D-078 — Auto-„Hauptstandort"-Location bei Company-Neuanlage: bestätigt

**Status:** entschieden · **Datum:** 2026-09-12 · bestätigt `CORE.md` D-007

Bei jeder Company-Neuanlage wird automatisch eine `Location` „Hauptstandort" mit einer
Kopie der Sitzadresse erzeugt, danach unabhängig editierbar/löschbar.

### D-079 — ServiceContract.contract_type: genau 2 Werte

**Status:** entschieden · **Datum:** 2026-09-12

`full_service` (inkl. Reparaturen/Ersatzteile) und `wartung` (nur planmäßige Wartung,
Störungen werden extra abgerechnet — Sonderkonditionen auf Mehraufwand, D-060). Keine
weiteren Vertragsstufen.

### D-080 — Geräteklassen: zwei unabhängige Achsen, Wartungspauschale nur nach Bauform

**Status:** entschieden · **Datum:** 2026-09-12 · **revidiert D-063** (Geräteklassen-Teil)

- Geräte werden real über eine **Matrix aus zwei Achsen** klassifiziert: **Bauform**
  (`portabel` \| `standgeraet`) × **Bildgebung** (`schwarzweiss` \| `farbdoppler`) — 4
  Kombinationen, aktuell im Altsystem als flache Liste geführt (z. B. „portables
  Farbdopplersystem").
- **Preisrelevant ist ausschließlich die Bauform**: `standgeraet` ist teurer als
  `portabel`. Die Bildgebung (`imaging_type`) hat **keine** Preiswirkung — rein
  katalog-/anzeigerelevant.
- D-063s „genau 2 Klassen × Tarifstufe → 4 Werte" bleibt für die **Preisliste**
  korrekt (2 `form_factor`-Werte × 2 Tarifstufen) — klargestellt: die preisrelevante
  Achse heißt `form_factor`, nicht `device_class`. `Device` bekommt zusätzlich
  `imaging_type` als reines Katalogfeld ohne Preisbezug.

### D-081 — ServiceContract.status: 5 reale Werte

**Status:** entschieden · **Datum:** 2026-09-12 · **revidiert** den SERVICE.md-Vorschlag

Reale Werte aus dem operativen Geschäft (nicht die ursprünglich vorgeschlagenen
`entwurf`/`ausgelaufen`):

- `offen` — Entwurf/Verhandlung, noch nicht unterschrieben.
- `aktiv`
- `gekuendigt`
- `verschrottet` — Gerät wurde außer Betrieb genommen/entsorgt.
- `kein_interesse` — Kunde lehnt nach Auslaufen/Kündigung einen Neuabschluss explizit ab.

Kein Bezug zur Opportunity-Pipeline — alle fünf sind echte `ServiceContract`-Zustände.

### D-082 — Maintenance.status: ohne separaten `zugewiesen`-Schritt, Techniker automatisch per PLZ-Gebiet

**Status:** entschieden · **Datum:** 2026-09-12 · **revidiert D-048**

- State-Machine: `geplant → in_durchfuehrung → kunde_bestaetigt → abgeschlossen →
  rechnung_freigegeben`. **Kein** separater `zugewiesen`-Zustand mehr — der
  verantwortliche Techniker ist bei Erstellung sofort bekannt, abgeleitet aus dem PLZ-
  Gebiet des Erfüllungsortes (`service_territories`, D-084), nicht aus einem
  manuellen Zuweisungsschritt.
- `assigned_technician_id` bleibt als Feld (D-048) — wird bei Erstellung automatisch
  aus dem PLZ-Gebiet vorbelegt, kann aber jederzeit übergeben/geändert werden
  („kann theoretisch übergeben werden").
- Gilt analog für `ServiceCase.assigned_technician_id`.

### D-083 — ServiceCase.status: 5 Werte, inkl. echtem `zugewiesen`-Schritt

**Status:** entschieden · **Datum:** 2026-09-12

`neu → zugewiesen → in_bearbeitung → wartet_auf_kunde → abgeschlossen` (+ `storniert`
als Endzustand von jedem Nicht-Abschluss-Zustand aus). Anders als bei `Maintenance`
(D-082) bleibt `zugewiesen` hier ein **eigener** Zustand — die automatische
PLZ-Vorbelegung (D-084) setzt zwar sofort einen Vorschlag, der Übergang `neu →
zugewiesen` ist aber ein einsehbarer Schritt (Triage/Bestätigung durch Innendienst),
bevor die Bearbeitung beginnt.

### D-084 — Drei unabhängige PLZ-Gebietstabellen: Fahrtzonen, Service-Gebiete, Vertriebs-Gebiete

**Status:** entschieden · **Datum:** 2026-09-12 · **revidiert D-054** (Sales-Territory)

Es gibt **drei fachlich getrennte** PLZ-Gebietsmodelle, jedes mit **echten
Von-Bis-PLZ-Bereichen** (nicht Präfix-basiert), unabhängig voneinander geschnitten:

1. **`travel_zones`** (Service/Billing, D-020/D-059/D-063): PLZ-Bereich → Fahrtzone
   (`flat_fee`). Bereits bestehend.
2. **`service_territories`** (neu, Service): PLZ-Bereich → `default_technician_id`.
   Bestimmt den bei Erstellung automatisch vorbelegten Techniker für `Maintenance`
   und `ServiceCase` (D-082/D-083).
3. **`sales_territories`** (neu, Sales — **revidiert D-054**): PLZ-Bereich →
   `default_sales_rep_id`. Schlägt `Company.responsible_sales_id` automatisch vor
   (D-016), **manuell überschreibbar**. D-054s „kein Territory-Modell, Zuständigkeit
   nur über Abteilung" gilt damit nicht mehr uneingeschränkt — es gibt doch ein
   PLZ-Gebietsmodell für Vertrieb, nur eben als **Vorschlag**, keine harte
   Autorisierungsgrenze (RBAC bleibt wie in D-016: rein informativ).

**Struktur je Tabelle:** `postal_code_from`, `postal_code_to`, das jeweilige
Zuordnungsfeld, `is_active`. Alle drei unabhängig pflegbar — **keine** gemeinsame
Basis-Zonen-Tabelle mit mehreren Attributen, da die Gebietsgrenzen fachlich
unterschiedlich geschnitten sein können.

### D-085 — Checklisten-Templates: ein universeller Katalog, kein `device_category`

**Status:** entschieden · **Datum:** 2026-09-12 · **revidiert** den SERVICE.md-Vorschlag

Dormed wartet überwiegend Ultraschall-/Sonographiesysteme — **ein einziger,
universeller Prüfkatalog** für alle Geräte. `checklist_templates.device_category`
entfällt ersatzlos (war „optional — Templates je Systemklasse" in `SERVICE.md`).

### D-086 — Opportunity.lead_source: 6 Werte

**Status:** entschieden · **Datum:** 2026-09-12

`messe` · `empfehlung` · `website_anfrage` · `kaltakquise` ·
`bestandskunde_cross_upsell` · `sonstige`.

### D-087 — `DORMEDABTEILUNG` verworfen

**Status:** entschieden · **Datum:** 2026-09-12

Deprecated im Altsystem, keine Übernahme — kein `product_area`-Feld, keine
Dublette zur Abteilungs-/Rollenstruktur (D-031).

### D-088 — Appointment.type (freie Termine): 3 Werte

**Status:** entschieden · **Datum:** 2026-09-12

`kundenbesuch` · `interne_besprechung` · `sonstiges`.

### D-089 — Opportunity.stage/probability: Phasenliste steht noch aus

**Status:** offen (Rückfrage) · **Datum:** 2026-09-12

Legacy `DistributionPhase` (Feld „Phase") ist im XML-Export nur als Spalten-Definition
vorhanden, **nicht** die eigentliche Phasen-/Prozent-Konfigurationsliste (lag im
Altsystem in einer separaten, nicht exportierten Konfigurationstabelle). Nutzer liefert
die reale Liste (Phase → %) nach — bis dahin bleiben die 6 vorläufigen `stage`-Werte
aus D-051 (`lead`·`qualifiziert`·`angebot`·`verhandlung`·`gewonnen`·`verloren`) sowie
`probability` als freies Feld (0–100 %, nicht fest an `stage` gekoppelt) bestehen.

### D-090 — Appointment.status: 3 Werte

**Status:** entschieden · **Datum:** 2026-09-12 · revidiert den SCHEDULING.md-Vorschlag

`vorlaeufig` · `fixiert` · `storniert` — ersetzt den ursprünglichen Vorschlag
(`geplant`/`bestaetigt`/`durchgefuehrt`/`abgesagt`). Kein eigener
„durchgeführt"-Zustand am Termin — die tatsächliche Durchführung wird vom
fachlichen Vorgang (`Maintenance.performed_at`, `ServiceCase`, …) getragen, nicht
vom Kalender-Termin selbst.

### D-091 — MaintenanceReport.operating_status: bestätigt

**Status:** entschieden · **Datum:** 2026-09-12

`in_betrieb` · `eingeschraenkt` · `ausser_betrieb` bestätigt, unverändert.

### D-092 — `DO_SVV_PRAXISSW*` (14 Praxis-IT-Felder) → Location, nicht Device

**Status:** entschieden · **Datum:** 2026-09-12 · löst CORE.md/SERVICE.md offenen Punkt

Das Praxis-Netzwerk (Server-IP, Passwort, Gateway, Subnetz, Ports,
Praxis-EDV-ASP, Speicher-/Arbeitslisten-AE-Titel+Port) ist standort-, nicht
gerätebezogen — mehrere Geräte am selben Standort teilen sich dasselbe
Praxis-Netz. Die 14 Felder werden `Location`-Felder, nicht `Device`-Felder.

---

## Bereich: Datenbank-Standards (Runde 2026-09-12)

Nutzer möchte höchstmögliche Normalisierung + einen eigenen Doku-Bereich dafür.
Ergebnis: [`../04-database/DATABASE.md`](../04-database/DATABASE.md).

### D-093 — Normalisierungs-Ausnahmen bereinigt: `next_due_at` und `users.name` gestrichen, Invoice-Summen/Snapshots bleiben

**Status:** entschieden · **Datum:** 2026-09-12 · **revidiert D-037**

- `ServiceContract.next_due_at` (D-037, war ein Planungs-Cache) → **gestrichen**,
  wird live aus `MAX(maintenance_devices.performed_at) + maintenance_interval_months`
  berechnet. Kein Cache-Feld — bei Performance-Bedarf später eine Materialized
  View, keine denormalisierte Spalte.
- `users.name` (IDENTITY_RBAC.md, nur Breeze-Kompatibilität) → **gestrichen**,
  wird `getNameAttribute()`-Accessor. Grund entfällt mit ADR-023 (Fortify statt
  Breeze).
- `Invoice.net_total`/`tax_total`/`gross_total` und `Invoice.recipient_*`
  (BILLING.md) sowie `MaintenanceDevice.maintenance_fee_snapshot`/
  `ServiceContract.maintenance_price` (D-065) → **bleiben** unverändert. Das sind
  **keine** echten Normalisierungsverletzungen, sondern rechtlich/fachlich
  geforderte Zustands-Snapshots zu einem Zeitpunkt (GoBD-Unveränderlichkeit bzw.
  Preis-zum-Vertragszeitpunkt) — eine andere Kategorie als redundant gespeicherte,
  jederzeit aktuelle Ableitungen.

### D-094 — Enum-Speicherung: VARCHAR + DB-CHECK-Constraint

**Status:** entschieden · **Datum:** 2026-09-12

Nicht Postgres-native `ENUM`-Typen (zu unflexibel bei den noch reifenden
Wertelisten), nicht nur PHP-seitige Validierung ohne DB-Constraint (zu schwach
angesichts ADR-007 „Postgres als zusätzliche Integrity Boundary"). Stattdessen:
`VARCHAR`-Spalte + expliziter `CHECK`-Constraint mit der Werteliste, **zusätzlich**
zum Laravel-PHP-Enum-Cast. Wertelisten-Änderung = eigene Migration
(`DROP CONSTRAINT` + `ADD CONSTRAINT`) — bewusster Reibungspunkt, da jede solche
Änderung ohnehin eine eigene `D-NNN`-Entscheidung ist.

### D-095 — Primärschlüssel: Auto-Increment bigint, kein UUID

**Status:** entschieden · **Datum:** 2026-09-12

Laravel-Standard `id()` (bigint, auto-increment) für alle Tabellen. Kein UUID —
fachliche Nummernkreise mit externer Sichtbarkeit existieren bereits separat
(`number`-Felder, z. B. D-067), der DB-PK ist rein intern. Kein Multi-Master-/
Offline-Sync-Bedarf, der UUIDs erfordern würde (Offline-Wartungsbericht ist
D-041 bewusst ein späterer Slice).

### D-096 — Geldbeträge einheitlich `decimal(12,2)`

**Status:** entschieden · **Datum:** 2026-09-12 · vereinheitlicht uneinheitliche Angaben in SERVICE.md/BILLING.md

Alle Geld-Spalten über alle Bereiche hinweg `decimal(12,2)` (statt der bisher
uneinheitlichen Mischung `decimal(10,2)`/`decimal(12,2)` in den einzelnen
`04-domain/*.md`-Tabellen). Prozentsätze (Stundensatz-Faktoren, Rabatt) bleiben
`decimal(5,2)`.

### D-097 — ServiceCase.type: vorläufige Werteliste, nicht abschließend

**Status:** entschieden (Liste erweiterbar) · **Datum:** 2026-09-12

`allgemeiner_service` · `telefonischer_support` · `geraeteausfall` ·
`netzwerkproblem`. Vom Nutzer ausdrücklich als Startpunkt markiert ("erstmal
diese") — **nicht abschließend**, kann bei Bedarf um weitere Werte ergänzt
werden (jede Ergänzung = eigene D-NNN-Entscheidung + CHECK-Constraint-Migration,
D-094).
