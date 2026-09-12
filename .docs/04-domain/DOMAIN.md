# Domain Model — Discovery-Zielbild

## Grundprinzip

Das neue Modell ist fachlich strukturiert.

Es ersetzt nicht einfach die Legacy-Tabellen.

Dieses Dokument ist die **Discovery-Übersicht**. Fertig ausspezifizierte Bereiche
haben ein eigenes autoritatives Dokument (Feldform, Enums, Regeln):

| Bereich | Spec | Status |
| --- | --- | --- |
| Core: Company / Person / Adresse | [`CORE.md`](CORE.md) | **spezifiziert** (D-001–025, D-078, D-092) |
| Service: Device / Vertrag / Wartung / Servicefall | [`SERVICE.md`](SERVICE.md) | **spezifiziert** (D-034–045, D-059–065, D-079–085, D-091, D-097) |
| Scheduling: Termine | [`SCHEDULING.md`](SCHEDULING.md) | **spezifiziert** (D-046–050, D-088, D-090) |
| Sales: Verkaufschancen | [`SALES.md`](SALES.md) | **spezifiziert** (D-051–055, D-086–087; `stage`/`probability`-Phasenliste D-089 vertagt) |
| Billing: Rechnungen / Zahlungen / Mahnwesen | [`BILLING.md`](BILLING.md) | **spezifiziert** (D-056 – D-077; Bank-Import-Format & DATEV-Kontenrahmen offen) |
| Inventory: Katalog / Lager / Bestand / Belege | [`INVENTORY.md`](INVENTORY.md) | **spezifiziert** (D-099 – D-121; Fremdgeräte D-107, Bestellwesen D-120 offen) |
| Dokumente, Portal, Shop | dieses Dokument | Discovery |

## Core

**→ Vollständige Spec: [`CORE.md`](CORE.md).** Kurzfassung:

- **Company** ist der zentrale Ankerpunkt. Aktuell nur Kunden, flach (keine
  Hierarchie außer abweichendem Rechnungsempfänger).
- **Person** existiert nie eigenständig — immer über `CompanyContact` an ≥ 1 Company.
  Keine eigene Adresse.
- **CompanyContact**: genau eine `role` (Vorschlagsliste + Freitext), `department`,
  `is_primary`.
- **Address**: polymorph, genau eine je `Company` (Sitz) und je `Location`.
  Geocoding ist fester Bestandteil.
- **Location**: physischer Standort, ≥ 1 je Company; Device/ServiceContract hängen
  an der Location.
- Zusätzlich: `ContactChannel` (Kommunikationskanäle), `Consent` (DSGVO,
  historisiert), `MedicalSpecialty` (Lookup „Fachrichtung").

## Service

### Device

Ein physisches Gerät.

Ein Gerät soll langfristig ein First-Class-Objekt der Warenwirtschaft sein.

> **Eingelöst (D-099/D-121).** `Device` **ist** das seriennummerngeführte Exemplar
> der Warenwirtschaft — ein Datensatz mit Lebenszyklus vom Wareneingang bis zur
> Verschrottung, kein separates `InventoryItem` daneben. Das Model lebt deshalb in
> `Modules\Inventory\`. Autoritativ: [`INVENTORY.md`](INVENTORY.md).

### ServiceContract

Servicevereinbarung für genau ein Gerät.

Im aktuellen Zielbild:

```text
Device 1 <-> 1 ServiceContract
```

Diese Regel soll nicht ohne fachliche Begründung zu einer Many-to-Many-Struktur erweitert werden.

### Maintenance

Konkrete Wartungsinstanz bzw. Wartungsvorgang.

Sie ist von der wiederkehrenden Servicevereinbarung zu unterscheiden.

### ServiceCase

Störung/Serviceeinsatz, der nicht automatisch eine Wartung ist.

## Dokumente

Strukturierte Fachdaten sind die Source of Truth.

PDFs sind möglichst Repräsentationen davon.

> **Notiz (2026-09-12, Detail folgt bei Grill-Runde Documents):** Als Template-/
> Report-Designer-Frontend für die Dokumenterstellung sind
> [AnkaReports](https://github.com/ankareport/ankareport) und
> [NextReports](https://github.com/nextreport/engine) Kandidaten. Templates werden
> später mit Daten aus der Datenbank befüllt (Invoice, Angebot,
> Wartungsbericht, …) — Bindung/Datenfluss noch nicht spezifiziert.

Beispiel:

```text
Invoice data
    |
    +--> PDF representation
    +--> e-invoice representation
```

## Billing

Rechnungen müssen historische Daten erhalten.

Eine aktuelle Company-Adresse darf nicht nachträglich die historische Rechnungsadresse einer bereits ausgestellten Rechnung verändern.

## Location und Billing

Keine starre Matrix erzwingen.

Mögliche Realität:

```text
Company A
├── Location A
├── Location B
├── Location C
└── central billing address
```

Ein Service kann an Location B stattfinden, während die Rechnung an eine zentrale Adresse geht.

Rechnungsempfänger ist **immer** eine `Company` (`billing_company_id`, D-066) —
keine freistehende Rechnungsadresse ohne Company. Details: `BILLING.md`.

## Offene Fachfragen

### Core — geklärt (siehe `CORE.md` / `../07-decisions/grill-log.md`)

- ✅ Managementgesellschaft erhält Rechnungen für mehrere Praxen → **ja** (D-004).
- ✅ Managementgesellschaft ist eine eigene Company; die Praxen zeigen mit
  `billing_company_id` auf sie (D-004).
- ✅ Leistungsempfänger ≠ Rechnungsempfänger möglich → **ja**, eigene Beziehung (D-004).
- ✅ `CompanyContact`-Rollen → **eine** freie `role` (String) mit Vorschlagsliste,
  nicht auswertungsrelevant (D-005).
- ✅ Adresse Company vs. Location → Company hat **eine** Sitzadresse; jede
  Location hat ihre eigene Adresse (Gerätestandort). Lieferadresse = eine
  Location (D-003 / D-007 / D-014).

### Billing — geklärt (siehe `BILLING.md` / `grill-log.md` D-056–D-077)

- ✅ Rechnungsempfänger ist immer eine `Company`, nie eine freistehende Adresse (D-066).
- ✅ Sage/KHK vollständig abgelöst, keine Sync-Brücke (D-068).
- ✅ Zahlungsabgleich, Mahnwesen, Storno/Gutschrift, e-Rechnungs-Detailfelder (D-069–D-074).

### Service / Sales — geklärt (siehe `SERVICE.md`/`SALES.md`/`grill-log.md` D-078–D-098)

- ✅ Fahrtzonen-/Territorien-Modell: drei unabhängige PLZ-Bereichstabellen
  (`travel_zones`, `service_territories`, `sales_territories`), revidiert D-054 (D-084).
- ✅ „Melder" eines Servicefalls → `ServiceCase.reported_by` → CompanyContact
  (`CORE.md`/`SERVICE.md`).
- ✅ Device-Klassen: `form_factor` bestimmt Preis, `imaging_type` ist reine
  Katalogeigenschaft (D-080, revidiert D-063).
- ✅ Enum-Wertelisten ServiceContract/Maintenance/ServiceCase/Appointment
  (D-079, D-081–083, D-088, D-090, D-097).

### Inventory — geklärt (siehe `INVENTORY.md` / `grill-log.md` D-099–D-121)

- ✅ Dreistufiger Warenstamm `ArticleGroup → Article → Exemplar`; `article_number`
  gehört zum **Artikel**, nicht zum Exemplar (D-099).
- ✅ **Tragende Mechanik:** das Technikerlager **ist** die Positionsauswahl —
  Lagerabgang und Rechnungsposition sind ein einziger Vorgang (D-109).
- ✅ Benutzerdefinierter Feldkatalog je Artikelgruppe mit Typvorgabe, `mandatory`
  und optionalem Regex als CHECK-Constraint (D-100/D-111/D-119).
- ✅ Bestand als Bewegungs-Ledger statt Bestandsspalte (D-102, Folge aus D-093).
- ✅ Leistungskatalog `OfferingGroup → Offering`, getrennt vom Artikelstamm (D-117).
- ✅ Belegprinzip durchgezogen: Wareneingang, Umbuchung, Reservierung + Rückgabe,
  Zählauftrag sind eigene Belege (D-106/D-113/D-114).

### Noch zu grillen — vorgemerkte Bereiche

- **Communication — Kontaktformular + Automatisierung (wichtig, D-122).**
  Eingehende Formularanfragen sollen automatisch mit bestehenden Datensätzen
  verknüpft oder direkt zu einem Kundenstamm werden. **Kernanforderung: es darf
  keine verwaisten Anfragen geben.** Enthält einen ungelösten Konflikt mit D-002
  (Person existiert nie ohne Company — ein Formular liefert aber zuerst eine
  Person), dazu Spam-Abwehr vor der Auto-Anlage und die DSGVO-Einwilligung aus
  dem Formular. Stärkstes Argument, Communication vor Documents zu grillen.
- **Communication — Aktivitäten-Timeline** (Anrufe/Mails/Notizen), `SALES.md` #7.
- **Documents** — inkl. der vertagten Template-Designer-Idee (siehe oben).

### Vertagt

- **Sales:** `stage`/`probability`-Phasenliste (reale % aus Legacy-`DistributionPhase`)
  — vertagt bis Nutzer die Werteliste liefert (D-089).
- **Inventory:** Fremdgeräte ohne Artikelstamm — ist `Device.article_id` `NOT NULL`?
  **Eigener Detaildurchgang nötig**, Nutzer braucht Vorlauf; migrationsrelevant
  und **ohne** Schema-Abgleich zu beantworten, da D-108 nicht verfügbar (D-107).
- **Inventory:** Bestellwesen ja/nein — Agent legt in der nächsten Runde eine
  begründete Empfehlung vor (D-120).
- **Inventory:** Sage/KHK-Artikelstamm-Export **nicht beschaffbar** (D-108).
  `INVENTORY.md` ist damit die einzige Domänen-Spec **ohne** Legacy-Ist-Referenz —
  die breit angelegte `Article`-Feldliste (D-115) muss aus der Nutzung heraus
  gekürzt werden, nicht aus dem Abgleich.
