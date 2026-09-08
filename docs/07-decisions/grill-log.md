# Grill-Log — Spec-First-Entscheidungen

Chronologisches Roh-Protokoll der `/grill-me`-Sessions. Jede Entscheidung bekommt
eine ID (`D-NNN`). Nach Abschluss eines Bereichs wird der Inhalt in die
`docs/`-Struktur + `.ai/rules/` synthetisiert; dieses Log bleibt als Audit-Spur.

Status je Eintrag: `entschieden` · `offen (Rückfrage)` · `Entscheidungspunkt in ROADMAP`.

---

## Bereich: Domäne — Adressen-Zerlegung

> **Status: abgeschlossen & synthetisiert (2026-09-08).** Ergebnis:
> [`../04-domain/CORE.md`](../04-domain/CORE.md) (autoritative Spec) +
> [`../09-legacy/xml/Adressen-Zuordnung.md`](../09-legacy/xml/Adressen-Zuordnung.md)
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
- Betrifft die Person-Spec → gehört in `docs/04-domain/`.

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

**Status:** entschieden (Modell offen) · **Datum:** 2026-09-08

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

Kontext: `docs/03-security/AUTHORIZATION.md` + `SECURITY.md`, `CRM.md` „Mitarbeiter"
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
