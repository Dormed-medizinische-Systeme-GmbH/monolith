# Legacy-Felder — SV (Servicevertraege-NEU)

Quelle: [`Servicevertraege-NEU.xml`](../xml/Servicevertraege-NEU.xml) · 83 Spalten · 83 Custom · 6 Pflicht

**Ist-Analyse (ADR-004).** `Entscheidung` wird in `/grill-me` gesetzt: `übernehmen` (in welches Zielmodell/-feld) · `verwerfen` · `offen`.

## Cluster nach Namenspräfix

| Präfix | Felder |
| --- | --- |
| `SYSTEM_*` | 21 |
| `(kein Präfix)` | 16 |
| `VERTRAG_*` | 8 |
| `KOSTEN_*` | 6 |
| `VERTRAGS_*` | 4 |
| `PRINTER_*` | 3 |
| `SONDE1_*` | 3 |
| `SONDE2_*` | 3 |
| `SONDE3_*` | 3 |
| `SONDE4_*` | 3 |
| `SONDE5_*` | 3 |
| `SONOGDT_*` | 3 |
| `WAGEN_*` | 3 |
| `GARANTIE_*` | 2 |
| `GW…` | 1 |
| `VERTAG_*` | 1 |

## Typverteilung

| Typ | Anzahl |
| --- | --- |
| STRING | 58 |
| DATETIME | 8 |
| DECIMAL | 6 |
| BOOLEAN | 6 |
| DATE | 2 |
| INT | 2 |
| TEXT | 1 |

## Felder

| Feld | Typ | Label (de) | Custom | Pflicht | Entscheidung |
| --- | --- | --- | :-: | :-: | --- |
| `ABWEICHENDE_RECHNUNG` | STRING(6) | Abweichende Rechnung | ✓ |  | offen |
| `DEBITORENNUMMER` | STRING(50) | DebitorenNummer | ✓ |  | offen |
| `ERFUELLUNGSORT_UEBERTRAG` | STRING(64) | ERFUELLUNGSORT_ÜBERTRAG | ✓ |  | offen |
| `ERSTEWARTUNG` | DATETIME | letzte Wartung | ✓ |  | offen |
| `FIRMA` | STRING(100) | Firma | ✓ |  | offen |
| `FIRMA2` | STRING(100) | Firma2 | ✓ |  | offen |
| `GARANTIE_HERSTELLER` | DATETIME | Herstellergarantie | ✓ |  | offen |
| `GARANTIE_KUNDE` | DATETIME | Kundengarantie bis (SV) | ✓ |  | offen |
| `GWAUTONUM` | STRING(200) | Nummer | ✓ |  | offen |
| `KEYWORD` | STRING(128) | Stichwort | ✓ |  | offen |
| `KOSTEN_FAHRTZONE_EINMAL` | DECIMAL | Fahrtzeugpauschale (einmal) | ✓ |  | offen |
| `KOSTEN_FAHRTZONE_KHK` | DECIMAL | Fahrzeugpauschale (Aktuell) | ✓ |  | offen |
| `KOSTEN_FAHRTZONE_VERTRAG` | DECIMAL | Fahrzeugpauschale (Ursprung) | ✓ |  | offen |
| `KOSTEN_WARTUNG_EINMAL` | DECIMAL | Wartungkosten (einmal) | ✓ |  | offen |
| `KOSTEN_WARTUNG_KHK` | DECIMAL | Wartungskosten (Aktuell) | ✓ |  | offen |
| `KOSTEN_WARTUNG_VERTRAG` | DECIMAL | Wartungskosten (Ursprung) | ✓ |  | offen |
| `MEHRFACHWARTUNG` | BOOLEAN | Mehrfachwartung | ✓ |  | offen |
| `MONAT` | STRING(32) | MONAT | ✓ |  | offen |
| `NAECHSTEWARTUNG` | DATETIME | nächste Wartung | ✓ |  | offen |
| `NOTES2` | TEXT | Schlagworte | ✓ |  | offen |
| `ORT` | STRING(50) | Ort | ✓ |  | offen |
| `PLZ` | STRING(5) | PLZ | ✓ |  | offen |
| `PREISANPASSUNG` | BOOLEAN | Preisanpassung gemäß Wartungsbericht? | ✓ |  | offen |
| `PREISANPASSUNG2025` | DATETIME | Preisanpassung gem. WB angekündigt | ✓ |  | offen |
| `PRINTER_ARTIKELNUMMER` | STRING(32) | Printer Artikelnummer | ✓ |  | offen |
| `PRINTER_BEZEICHNUNG` | STRING(32) | Printer Bezeichnung | ✓ |  | offen |
| `PRINTER_SERIENNUMMER` | STRING(32) | Printer Seriennummer | ✓ |  | offen |
| `SONDE1_ARTIKELNUMMER` | STRING(32) | Sonde 1 Artikelnummer | ✓ |  | offen |
| `SONDE1_BEZEICHNUNG` | STRING(64) | Sonde 1 Bezeichnung | ✓ |  | offen |
| `SONDE1_SERIENNUMMER` | STRING(32) | Sonde 1 Seriennummer | ✓ |  | offen |
| `SONDE2_ARTIKELNUMMER` | STRING(32) | Sonde 2 Artikelnummer | ✓ |  | offen |
| `SONDE2_BEZEICHNUNG` | STRING(64) | Sonde 2 Bezeichnung | ✓ |  | offen |
| `SONDE2_SERIENNUMMER` | STRING(32) | Sonde 2 Seriennummer | ✓ |  | offen |
| `SONDE3_ARTIKELNUMMER` | STRING(32) | Sonde 3 Artikelnummer | ✓ |  | offen |
| `SONDE3_BEZEICHNUNG` | STRING(64) | Sonde 3 Bezeichnung | ✓ |  | offen |
| `SONDE3_SERIENNUMMER` | STRING(32) | Sonde 3 Seriennummer | ✓ |  | offen |
| `SONDE4_ARTIKELNUMMER` | STRING(32) | Sonde 4 Artikelnummer | ✓ |  | offen |
| `SONDE4_BEZEICHNUNG` | STRING(64) | Sonde 4 Bezeichnung | ✓ |  | offen |
| `SONDE4_SERIENNUMMER` | STRING(32) | Sonde 4 Seriennummer | ✓ |  | offen |
| `SONDE5_ARTIKELNUMMER` | STRING(32) | Sonde 5 Artikelnummer | ✓ |  | offen |
| `SONDE5_BEZEICHNUNG` | STRING(64) | Sonde 5 Bezeichnung | ✓ |  | offen |
| `SONDE5_SERIENNUMMER` | STRING(32) | Sonde 5 Seriennummer | ✓ |  | offen |
| `SONOGDT_ARTIKELNUMMER` | STRING(50) | SONOGDT Artikelnummer (KHK) | ✓ |  | offen |
| `SONOGDT_BEZEICHNUNG` | STRING(50) | SONOGDT Bezeichnung | ✓ |  | offen |
| `SONOGDT_LIZENZ` | STRING(32) | SONOGDT Lizenz | ✓ |  | offen |
| `SYSTEM_ARTIKELNUMMER` | STRING(32) | System Artikelnummer | ✓ | ✓ | offen |
| `SYSTEM_AUSLIEFERUNGSDATUM` | DATE | Auslieferungsdatum | ✓ |  | offen |
| `SYSTEM_BAUJAHR` | STRING(8) | Baujahr | ✓ |  | offen |
| `SYSTEM_BENUTZER` | STRING(32) | System Benutzer | ✓ |  | offen |
| `SYSTEM_BEZEICHNUNG` | STRING(64) | System Kategorie (Wartung) | ✓ | ✓ | offen |
| `SYSTEM_DHCP` | BOOLEAN | DHCP | ✓ |  | offen |
| `SYSTEM_GATEWAY` | STRING(32) | System Gateway | ✓ |  | offen |
| `SYSTEM_HERSTELLER` | STRING(64) | System Hersteller | ✓ | ✓ | offen |
| `SYSTEM_IPADRESSE` | STRING(30) | System IP Adresse | ✓ |  | offen |
| `SYSTEM_MACADRESSE` | STRING(17) | System MAC Adresse | ✓ |  | offen |
| `SYSTEM_OPTIONEN` | STRING(255) | System Optionen | ✓ |  | offen |
| `SYSTEM_OS` | STRING(16) | System OS | ✓ |  | offen |
| `SYSTEM_PASSWORD` | STRING(32) | System Passwort | ✓ |  | offen |
| `SYSTEM_SERIENNUMMER` | STRING(32) | System Seriennummer | ✓ | ✓ | offen |
| `SYSTEM_STORAGE_PORT` | INT | Storage (Port) | ✓ |  | offen |
| `SYSTEM_STORAGE_PORT_NEW` | STRING(10) | Storage (Port)_ | ✓ |  | offen |
| `SYSTEM_STORAGE_TITLE` | STRING(16) | Storage (Title) | ✓ |  | offen |
| `SYSTEM_SW` | STRING(16) | System Software | ✓ |  | offen |
| `SYSTEM_WORKLIST_PORT_NEW` | STRING(10) | Worklist (Port)_ | ✓ |  | offen |
| `SYSTEM_WORKLISTE_PORT` | INT | Worklist (Port) | ✓ |  | offen |
| `SYSTEM_WORKLISTE_TITLE` | STRING(16) | Worklist (Title) | ✓ |  | offen |
| `VERANTWORTLICHER_SERVICE` | STRING(64) | Verantwortlicher | ✓ | ✓ | offen |
| `VERTAG_GARANTIEVERSICHERUNG` | BOOLEAN | Garantieversicherung | ✓ |  | offen |
| `VERTRAG_DATUM_KUENDIGUNG` | DATETIME | Kündigungsdatum | ✓ |  | offen |
| `VERTRAG_ELEKTORNIKVERSICHERUNG` | BOOLEAN | Elektronikversicherung | ✓ |  | offen |
| `VERTRAG_ELEKTRONIKVERS_WO` | STRING(32) | Versicherung Wo? | ✓ |  | offen |
| `VERTRAG_FS_ENDE` | DATETIME | Full-Service Ende | ✓ |  | offen |
| `VERTRAG_GARANTIEVERS_BIS` | DATE | Garantieversicherung bis | ✓ |  | offen |
| `VERTRAG_LEASING` | BOOLEAN | Leasingvertrag | ✓ |  | offen |
| `VERTRAG_LEASING_WO` | STRING(32) | Leasing Wo? | ✓ |  | offen |
| `VERTRAG_ZAHLUNGSKONDITIONEN` | STRING(32) | Zahlungskonditionen | ✓ |  | offen |
| `VERTRAGS_ART` | STRING(32) | Vertragsart | ✓ |  | offen |
| `VERTRAGS_DATUM` | DATETIME | Vertragsdatum | ✓ |  | offen |
| `VERTRAGS_INTERVALL` | STRING(16) | Intervall | ✓ |  | offen |
| `VERTRAGS_STATUS` | STRING(16) | Vertragsstatus | ✓ | ✓ | offen |
| `WAGEN_ARTIKELNUMMER` | STRING(32) | Wagen Artikelnummer | ✓ |  | offen |
| `WAGEN_BEZEICHNUNG` | STRING(32) | Wagen Bezeichnung | ✓ |  | offen |
| `WAGEN_SERIENNUMMER` | STRING(32) | Wagen Seriennummer | ✓ |  | offen |
