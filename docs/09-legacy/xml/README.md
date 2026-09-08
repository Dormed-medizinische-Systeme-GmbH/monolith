# Legacy-Schema-Exporte (CAS genesisWorld)

Rohe `gwconnect`-Objektdefinitionen aus dem aktuell produktiven CRM
(CAS genesisWorld). **Ist-Analyse, kein Zielschema** (ADR-004): ein Feld wird nur
übernommen, wenn seine fachliche Bedeutung im neuen Modell gebraucht wird.

## Dateien

| Datei | Objekt | Tablesign | Spalten | Neues Zielkonzept (grob) |
| --- | --- | --- | --- | --- |
| [`Adressen.xml`](Adressen.xml) | `Address` | `ADR` | 356 | Company / Person / CompanyContact / Address / Location |
| [`Servicevertraege-NEU.xml`](Servicevertraege-NEU.xml) | `SV` | `SV` | 83 | ServiceContract / Device / DeviceConfiguration / Maintenance |
| [`Tickets.xml`](Tickets.xml) | `TICKETS` | `TICK` | 112 | ServiceCase / Maintenance / Serviceposition / Diagnose … |
| [`Termine.xml`](Termine.xml) | `APPOINTMENT` | `APP` | 25 | Appointment (Scheduling, getrennt von Fachvorgang) |
| [`Verkaufschancen.xml`](Verkaufschancen.xml) | `GWOPPORTUNITY` | `GWOP` | 34 | SalesOpportunity |

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
(`docs/02-development/TESTING_AND_SEEDING.md`).
