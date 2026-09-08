# Grill-Log — Spec-First-Entscheidungen

Chronologisches Roh-Protokoll der `/grill-me`-Sessions. Jede Entscheidung bekommt
eine ID (`D-NNN`). Nach Abschluss eines Bereichs wird der Inhalt in die
`docs/`-Struktur + `.ai/rules/` synthetisiert; dieses Log bleibt als Audit-Spur.

Status je Eintrag: `entschieden` · `offen (Rückfrage)` · `Entscheidungspunkt in ROADMAP`.

---

## Bereich: Domäne — Adressen-Zerlegung

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
