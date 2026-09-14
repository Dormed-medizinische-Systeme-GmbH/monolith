# Legacy-Felder — APPOINTMENT (Termine)

Quelle: [`Termine.xml`](Termine.xml) · 25 Spalten · 1 Custom · 3 Pflicht

**Ist-Analyse (ADR-004).** `Entscheidung` wird in `/grill-me` gesetzt: `übernehmen` (in welches Zielmodell/-feld) · `verwerfen` · `offen`.

## Cluster nach Namenspräfix

| Präfix | Felder |
| --- | --- |
| `(kein Präfix)` | 18 |
| `APP_*` | 3 |
| `GW…` | 2 |
| `CAS…` | 1 |
| `DORMED…` | 1 |

## Typverteilung

| Typ | Anzahl |
| --- | --- |
| STRING | 10 |
| BOOLEAN | 7 |
| DATETIME | 5 |
| INT | 2 |
| DECIMAL | 1 |

## Felder

| Feld | Typ | Label (de) | Custom | Pflicht | Entscheidung |
| --- | --- | --- | :-: | :-: | --- |
| `AddComment` | STRING(40) | Kommentar |  |  | offen |
| `Alarm` | DATETIME | Alarm |  |  | offen |
| `APP_ACCEPTABLEREGISTRATIONS` | INT | Max. Teilnehmerzahl |  |  | offen |
| `APP_GROUP` | STRING(250) | Termingruppe |  |  | offen |
| `APP_MANDATORY` | BOOLEAN | Pflichttermin |  |  | offen |
| `CASAway` | BOOLEAN | Außer Haus |  |  | offen |
| `Category` | STRING(255) | Kategorie |  |  | offen |
| `CBStatus` | STRING(20) | Aktivitätsstatus |  |  | offen |
| `DayAppointment` | BOOLEAN | Tagesaktivität |  |  | offen |
| `DORMEDLOGISTIKERFORDERLICH` | BOOLEAN | Logistik erforderlich | ✓ |  | offen |
| `Duration` | DECIMAL | Dauer |  |  | offen |
| `End_dt` | DATETIME | Ende |  |  | offen |
| `GISDescription` | STRING(255) | Ort |  |  | offen |
| `GWSSTATUS` | STRING(80) | Status |  | ✓ | offen |
| `GWSTYPE` | STRING(80) | Typ |  | ✓ | offen |
| `ISONLINEMEETING` | BOOLEAN | Online-Besprechung |  |  | offen |
| `ISPARTOFEVENT` | BOOLEAN | Veranstaltungstermin |  |  | offen |
| `Keyword` | STRING(100) | Stichwort |  | ✓ | offen |
| `Notes` | STRING(255) | Schlagworte |  |  | offen |
| `NOTES2` | STRING(-1) | Schlagworte |  |  | offen |
| `PERIODALARMDAYS` | INT | Alarmierung in Tagen |  |  | offen |
| `PERIODALARMSET` | BOOLEAN | Alarmierung |  |  | offen |
| `PeriodEnd` | DATETIME | Periodenenddatum |  |  | offen |
| `PeriodStart` | DATETIME | Periodenstartdatum |  |  | offen |
| `start_dt` | DATETIME | Beginn |  |  | offen |
