# Legacy-Schema-Exporte (CAS genesisWorld)

Rohe `gwconnect`-Objektdefinitionen aus dem aktuell produktiven CRM
(CAS genesisWorld). **Ist-Analyse, kein Zielschema** (ADR-004): ein Feld wird nur
übernommen, wenn seine fachliche Bedeutung im neuen Modell gebraucht wird.

## Dateien

| Roh-XML | Feld-Inventar | Objekt | Spalten (Custom) | Neues Zielkonzept (grob) |
| --- | --- | --- | --- | --- |
| [`Adressen.xml`](Adressen.xml) | [`Adressen-Felder.md`](Adressen-Felder.md) | `Address` (ADR) | 356 (176) | Company / Person / CompanyContact / Address / Location |
| [`Servicevertraege-NEU.xml`](Servicevertraege-NEU.xml) | [`Servicevertraege-NEU-Felder.md`](Servicevertraege-NEU-Felder.md) | `SV` (SV) | 83 (83) | ServiceContract / Device / DeviceConfiguration / Maintenance |
| [`Tickets.xml`](Tickets.xml) | [`Tickets-Felder.md`](Tickets-Felder.md) | `TICKETS` (TICK) | 112 (112) | ServiceCase / Maintenance / Serviceposition / Diagnose … |
| [`Termine.xml`](Termine.xml) | [`Termine-Felder.md`](Termine-Felder.md) | `APPOINTMENT` (APP) | 25 (1) | Appointment (Scheduling, getrennt von Fachvorgang) |
| [`Verkaufschancen.xml`](Verkaufschancen.xml) | [`Verkaufschancen-Felder.md`](Verkaufschancen-Felder.md) | `GWOPPORTUNITY` (GWOP) | 34 (9) | SalesOpportunity |

Die `*-Felder.md` sind die **lesbare Vorverdauung** (Feld, Typ, Label, Custom-Flag,
Cluster nach Namenspräfix). Die Spalte `Entscheidung` steht auf `offen` und wird
in `/grill-me` gefüllt (`übernehmen` → Zielmodell/-feld · `verwerfen` · `offen`).

Details der Zerlegung: [`../../04-domain/LEGACY_MAPPING.md`](../../04-domain/LEGACY_MAPPING.md).

## Format — wie lesen

```
gwconnect
└── system
    └── object name="…" tablesign="…"      ← die Entität
        ├── terms / <term>                 ← lokalisierte Namen (de)
        ├── image …                        ← Icons als base64, IGNORIEREN
        ├── table name="…" tablecolumncount="…"
        │   └── column name="…" adotype="…" size="…" scale="…" precision="…" sysfield="…"
        │       └──  … eine pro DB-Spalte
        └── title …                        ← lokalisiertes Label je Spalte
```

- **Signal**: `<column>` (Spaltenname + Typ) und `<title>` (Label).
- **Rauschen**: `<image binary="…">` (base64-Bitmaps), Surface-/Layout-Blöcke.
- `adotype` ist der ADO/OLEDB-Typcode: `200`=varchar, `202`=nvarchar, `3`=int,
  `5`=double, `11`=bool, `135`=datetime, `129`/`130`=char/nchar, `205`=blob.
- Custom-/Kundenfelder sind i. d. R. am Präfix erkennbar (`ADR_…`, `DORMED…`,
  modul­spezifische Kürzel); die genaue Trennung wird in `/grill-me` gezogen.

## Nutzung

Diese Dateien sind **Input für die Domänen-Spec** (`/grill-me`), nicht für Migrationscode.
Ein späterer Live-Datenimport ist ein eigener, separat zu spezifizierender Mechanismus
(`.docs/02-development/TESTING_AND_SEEDING.md`).
