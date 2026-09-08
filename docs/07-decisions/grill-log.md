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
