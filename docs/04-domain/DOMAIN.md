# Domain Model — Discovery-Zielbild

## Grundprinzip

Das neue Modell ist fachlich strukturiert.

Es ersetzt nicht einfach die Legacy-Tabellen.

Dieses Dokument ist die **Discovery-Übersicht**. Fertig ausspezifizierte Bereiche
haben ein eigenes autoritatives Dokument (Feldform, Enums, Regeln):

| Bereich | Spec | Status |
| --- | --- | --- |
| Core: Company / Person / Adresse | [`CORE.md`](CORE.md) | **spezifiziert** (D-001 – D-025) |
| Service: Device / Vertrag / Wartung / Servicefall | [`SERVICE.md`](SERVICE.md) | **spezifiziert** (D-034 – D-045; einige Enum-Werte offen) |
| Scheduling: Termine | [`SCHEDULING.md`](SCHEDULING.md) | **spezifiziert** (D-046 – D-050) |
| Sales, Dokumente, Billing, Portal, Inventory | dieses Dokument | Discovery |

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

Ob der Rechnungsempfänger immer dieselbe Company ist, ist als Fachfrage noch offen.

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

### Service / Sales / Billing — noch offen

- Kann ein Rechnungsempfänger auch eine freistehende Adresse ohne Company sein?
  (Annahme aktuell: immer Company.)
- Fahrtzonen-Modell (Zonen, Preise) — Bereich Service.
- „Melder" eines Servicefalls — Bereich Service (`ServiceCase.reported_by` → CompanyContact).
