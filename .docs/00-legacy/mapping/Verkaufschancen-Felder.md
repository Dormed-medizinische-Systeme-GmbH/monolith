# Legacy-Felder — GWOPPORTUNITY (Verkaufschancen)

Quelle: [`Verkaufschancen.xml`](../xml/Verkaufschancen.xml) · 34 Spalten · 9 Custom · 11 Pflicht

**Ist-Analyse (ADR-004).** `Entscheidung` wird in `/grill-me` gesetzt: `übernehmen` (in welches Zielmodell/-feld) · `verwerfen` · `offen`.

## Cluster nach Namenspräfix

| Präfix | Felder |
| --- | --- |
| `(kein Präfix)` | 30 |
| `DO_*` | 3 |
| `DORMED…` | 1 |

## Typverteilung

| Typ | Anzahl |
| --- | --- |
| STRING | 24 |
| DATETIME | 4 |
| DECIMAL | 4 |
| CURRENCY | 1 |
| INT | 1 |

## Felder

| Feld | Typ | Label (de) | Custom | Pflicht | Entscheidung |
| --- | --- | --- | :-: | :-: | --- |
| `AccountInformation` | STRING(200) | Kunde |  |  | offen |
| `Alarm` | DATETIME | Alarm |  |  | offen |
| `AttorneyInFact` | STRING(40) | Stellvertreter |  | ✓ | offen |
| `BUDGET` | CURRENCY | Budget | ✓ | ✓ | offen |
| `CompetitorNotes` | STRING(1024) | Notiz zu Mitbewerb |  |  | offen |
| `Competitors` | STRING(255) | Mitbewerber |  |  | offen |
| `CurrencyNat` | STRING(3) | Währung |  |  | offen |
| `DistributionPhase` | STRING(80) | Phase |  | ✓ | offen |
| `DO_SV_VORGANSSART` | STRING(50) | Vorgangsart | ✓ | ✓ | offen |
| `DO_VC_MITBEWERBER` | STRING(50) | DORMED Mitbewerber | ✓ |  | offen |
| `DO_VC_MITBEWERBER_NOTIZ` | STRING(255) | DORMED Notiz zu Mitbewerber | ✓ |  | offen |
| `DORMEDABTEILUNG` | STRING(50) | Bereich | ✓ | ✓ | offen |
| `end_dt` | DATETIME | Ende |  | ✓ | offen |
| `Keyword` | STRING(100) | Stichwort |  | ✓ | offen |
| `KOOPERATION` | STRING(50) | Kooperations-Art | ✓ |  | offen |
| `KOOPERATIONSPARTNER` | STRING(50) | Kooperations-Partner | ✓ |  | offen |
| `LASTCONTACTINSALESPROCESS` | DATETIME | Letzte Aktion am |  |  | offen |
| `MARGINALRETURN` | DECIMAL | Deckungsbeitrag |  |  | offen |
| `MARGINALRETURNWEIGHTED` | DECIMAL | Deckungsbeitrag gewichtet |  |  | offen |
| `Notes2` | STRING(-1) | Schlagworte |  |  | offen |
| `OPPORTUNITYNUMBER` | STRING(30) | Nummer |  |  | offen |
| `OppTotalAmount` | DECIMAL | Gesamt |  |  | offen |
| `PersonInCharge` | STRING(40) | Verantwortlicher |  | ✓ | offen |
| `Probability` | INT | Wahrscheinlichkeit |  | ✓ | offen |
| `ProductPositionsDisplay` | STRING(512) | Produktpositionen |  |  | offen |
| `RelativeAmount` | DECIMAL | Gesamt gewichtet |  |  | offen |
| `Source` | STRING(100) | Quelle des Leads |  | ✓ | offen |
| `Start_dt` | DATETIME | Beginn |  | ✓ | offen |
| `Status` | STRING(20) | DORMED Status |  |  | offen |
| `VCAWKZ` | STRING(50) | AWKZ / PLZ / Tour | ✓ |  | offen |
| `VENDORINFORMATION` | STRING(200) | Verkäufer |  |  | offen |
| `VENDORINFORMATION2` | STRING(200) | Verkäufer 2 |  |  | offen |
| `VENDORINFORMATION3` | STRING(200) | Verkäufer 3 |  |  | offen |
| `ZAHLUNGKONDITIONEN` | STRING(64) | Zahlungskonditionen | ✓ |  | offen |
