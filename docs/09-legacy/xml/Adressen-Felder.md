# Legacy-Felder — Address (Adressen)

Quelle: [`Adressen.xml`](Adressen.xml) · 356 Spalten · 176 Custom · 0 Pflicht

**Ist-Analyse (ADR-004).** `Entscheidung` wird in `/grill-me` gesetzt: `übernehmen` (in welches Zielmodell/-feld) · `verwerfen` · `offen`.

> **D-001**: die denormalisierten Servicevertrags-Slots auf der Adresse sind **tot**
> und verworfen — nur die Löschung im Altsystem steht noch aus. **142 Felder**
> verworfen (`DO_SV*` / `DO_SVV*` / `SVV*_VERTRAGS_INTERVALL`). **Ausnahme**: die
> 14 `DO_SVV_PRAXISSW*`-Felder (Praxis-IT / Remote-Access-Doku) **bleiben** →
> Ziel wahrsch. Device/Location. Effektiv relevant in Adressen: **214 Felder**.
> Siehe [`../../07-decisions/grill-log.md`](../../07-decisions/grill-log.md).

## Cluster nach Namenspräfix

| Präfix | Felder |
| --- | --- |
| `(kein Präfix)` | 177 |
| `DO_*` | 152 |
| `GW…` | 15 |
| `SERVER_*` | 3 |
| `CAS…` | 2 |
| `SONOGDT_*` | 2 |
| `ADR…` | 1 |
| `SVV2_*` | 1 |
| `SVV3_*` | 1 |
| `SVV4_*` | 1 |
| `SVV_*` | 1 |

## Typverteilung

| Typ | Anzahl |
| --- | --- |
| STRING | 286 |
| BOOLEAN | 21 |
| CURRENCY | 21 |
| DATETIME | 11 |
| INT | 8 |
| DATE | 5 |
| DECIMAL | 4 |

## Felder

| Feld | Typ | Label (de) | Custom | Pflicht | Entscheidung |
| --- | --- | --- | :-: | :-: | --- |
| `AddressLetter` | STRING(60) | Briefanrede |  |  | offen |
| `AddressTerm` | STRING(30) | Anrede |  |  | offen |
| `ADRKHKMATCHCODE` | STRING(100) | Deb./Kred. Matchcode | ✓ |  | offen |
| `AdrNumber` | STRING(30) | Deb./Kred. Konto |  |  | offen |
| `Anrede2` | STRING(60) | Anrede 2 |  |  | offen |
| `Anrede3` | STRING(60) | Anrede 3 |  |  | offen |
| `Anrede4` | STRING(60) | Anrede 4 |  |  | offen |
| `Anschaffungwan` | STRING(30) | wann gekauft |  |  | offen |
| `AVV` | BOOLEAN | Auftragsverarbeitungsvertrag | ✓ |  | offen |
| `BankAccountHolder` | STRING(30) | Kontoinhaber |  |  | offen |
| `BankAccountNr` | STRING(20) | Kontonummer |  |  | offen |
| `BankZipNr` | STRING(20) | Bankleitzahl |  |  | offen |
| `Birthday` | DATETIME | Geburtstag |  |  | offen |
| `BirthdayGreetings` | BOOLEAN | Geburtstagskarte |  |  | offen |
| `BMeABl` | DATETIME | _BMe/ABl |  |  | offen |
| `BudgetfKauf` | INT | Budget f. Kauf |  |  | offen |
| `CASFunction` | STRING(100) | Funktion Ansprechpartner |  |  | offen |
| `CASWIDNR` | STRING(25) | Wirtschafts-Identifikationsnummer |  |  | offen |
| `Category` | STRING(255) | Kategorie |  |  | offen |
| `ChristianName` | STRING(30) | Vorname |  |  | offen |
| `ChristmasGreetings` | BOOLEAN | Weihnachtskarte |  |  | offen |
| `CompName` | STRING(255) | Firma (Anrede) |  |  | offen |
| `CompName2` | STRING(60) | Name |  |  | offen |
| `COUNTRY1` | STRING(80) | Land |  |  | offen |
| `COUNTRY2` | STRING(80) | Lieferung (Land) |  |  | offen |
| `COUNTRY3` | STRING(80) | Land (Privat) |  |  | offen |
| `CurrencyNat` | STRING(3) | Währung |  |  | offen |
| `Department` | STRING(30) | Abteilung |  |  | offen |
| `DO_SV2_KOSTEN` | CURRENCY | SVV2 Kosten (Vertrag) | ✓ |  | verwerfen (D-001 · tot) |
| `DO_SV2_KOSTEN_KHK` | CURRENCY | SVV2 Kosten (KHK) | ✓ |  | verwerfen (D-001 · tot) |
| `DO_SV3_KOSTEN` | CURRENCY | SVV3 Kosten (Vertrag) | ✓ |  | verwerfen (D-001 · tot) |
| `DO_SV3_KOSTEN_KHK` | CURRENCY | SVV3 Kosten (KHK) | ✓ |  | verwerfen (D-001 · tot) |
| `DO_SV4_KOSTEN` | CURRENCY | SVV4 Kosten (Vertrag) | ✓ |  | verwerfen (D-001 · tot) |
| `DO_SV4_KOSTEN_KHK` | CURRENCY | SVV4 Kosten (KHK) | ✓ |  | verwerfen (D-001 · tot) |
| `DO_SV5_KOSTEN` | CURRENCY | SVV5 Kosten (Vertrag) | ✓ |  | verwerfen (D-001 · tot) |
| `DO_SV5_KOSTEN_KHK` | CURRENCY | SVV5 Kosten (KHK) | ✓ |  | verwerfen (D-001 · tot) |
| `DO_SV_KOSTEN` | CURRENCY | SVV Kosten (Vertrag) | ✓ |  | verwerfen (D-001 · tot) |
| `DO_SV_KOSTEN_KHK` | CURRENCY | SVV Kosten (KHK) | ✓ |  | verwerfen (D-001 · tot) |
| `DO_SVV2_HERSTELLER` | STRING(50) | SVV2 Hersteller | ✓ |  | verwerfen (D-001 · tot) |
| `DO_SVV2_KOSTEN_FAHRTZONE` | CURRENCY | SVV2 Kosten Fahrtzone (KHK) | ✓ |  | verwerfen (D-001 · tot) |
| `DO_SVV2_KOSTEN_FAHRTZONE_VERT` | CURRENCY | SVV2 Kosten Fahrtzone (Vertrag) | ✓ |  | verwerfen (D-001 · tot) |
| `DO_SVV2_MAC` | STRING(50) | SVV2 MAC-Adresse | ✓ |  | verwerfen (D-001 · tot) |
| `DO_SVV2_NAECHSTEWARTUNG` | DATE | SVV2 nächste Wartung | ✓ |  | verwerfen (D-001 · tot) |
| `DO_SVV2_PRINER_BEZ` | STRING(50) | SVV2 Printer Bezeichnung | ✓ |  | verwerfen (D-001 · tot) |
| `DO_SVV2_PRINTER_SERNR` | STRING(50) | SVV2 Printer Seriennummer | ✓ |  | verwerfen (D-001 · tot) |
| `DO_SVV2_SONDE4_SERNR` | STRING(50) | SVV2 Sonde 4 Seriennummer | ✓ |  | verwerfen (D-001 · tot) |
| `DO_SVV2_SYSTEM` | STRING(50) | SVV2 System Artikelnummer | ✓ |  | verwerfen (D-001 · tot) |
| `DO_SVV2_SYSTEM_BEZ` | STRING(50) | SVV2 System Bezeichnung | ✓ |  | verwerfen (D-001 · tot) |
| `DO_SVV2_SYSTEM_SERNR` | STRING(50) | SVV2 System Seriennummer | ✓ |  | verwerfen (D-001 · tot) |
| `DO_SVV2_VERTRAG_ART` | STRING(50) | SVV2 Vertrag-Art | ✓ |  | verwerfen (D-001 · tot) |
| `DO_SVV2_WAGEN` | STRING(50) | SVV2 Wagen Artikel | ✓ |  | verwerfen (D-001 · tot) |
| `DO_SVV2_WAGEN_BEZ` | STRING(50) | SVV2 Wagen Bezeichnung | ✓ |  | verwerfen (D-001 · tot) |
| `DO_SVV2_WAGEN_SERNR` | STRING(50) | SVV2 Wagen Seriennummer | ✓ |  | verwerfen (D-001 · tot) |
| `DO_SVV2_ZAHLUNG` | STRING(50) | SVV2 Zahlungskonditionen | ✓ |  | verwerfen (D-001 · tot) |
| `DO_SVV3_APPSW` | STRING(50) | SVV3 Applikation (SW) | ✓ |  | verwerfen (D-001 · tot) |
| `DO_SVV3_BASE` | STRING(50) | SVV3 Betriebssystem (OS) | ✓ |  | verwerfen (D-001 · tot) |
| `DO_SVV3_ELEKTRONIKVERSICHERUNG` | STRING(50) | SVV3 Elektronikversicherung | ✓ |  | verwerfen (D-001 · tot) |
| `DO_SVV3_ERFUELLUNGSORT` | STRING(50) | SVV3 Erfüllungsort (falls abw.) | ✓ |  | verwerfen (D-001 · tot) |
| `DO_SVV3_HERSTELLER` | STRING(50) | SVV3 Hersteller | ✓ |  | verwerfen (D-001 · tot) |
| `DO_SVV3_KOSTEN_FAHRTZONE` | CURRENCY | SVV3 Kosten Fahrtzone (KHK) | ✓ |  | verwerfen (D-001 · tot) |
| `DO_SVV3_KOSTEN_FAHRTZONE_VERT` | CURRENCY | SVV3 Kosten Fahrtzone (Vertrag) | ✓ |  | verwerfen (D-001 · tot) |
| `DO_SVV3_MAC` | STRING(50) | SVV3 MAC Adresse | ✓ |  | verwerfen (D-001 · tot) |
| `DO_SVV3_PRINER_BEZ` | STRING(50) | SVV3 Printer Bezeichnung | ✓ |  | verwerfen (D-001 · tot) |
| `DO_SVV3_PRINTER` | STRING(50) | SVV3 Printer Artikelnummer | ✓ |  | verwerfen (D-001 · tot) |
| `DO_SVV3_PRINTER_SERNR` | STRING(50) | SVV3 Printer Seriennummer | ✓ |  | verwerfen (D-001 · tot) |
| `DO_SVV3_SONDE1` | STRING(50) | SVV3 Sonde 1 Artikelnummer | ✓ |  | verwerfen (D-001 · tot) |
| `DO_SVV3_SONDE1_BEZ` | STRING(50) | SVV3 Sonde 1 Bezeichnung | ✓ |  | verwerfen (D-001 · tot) |
| `DO_SVV3_SONDE2` | STRING(50) | SVV3 Sonde 2 Artikelnummer | ✓ |  | verwerfen (D-001 · tot) |
| `DO_SVV3_SONDE2_BEZ` | STRING(50) | SVV3 Sonde 2 Bezeichnung | ✓ |  | verwerfen (D-001 · tot) |
| `DO_SVV3_SONDE2_SERNR` | STRING(50) | SVV3 Sonde 2 Seriennummer | ✓ |  | verwerfen (D-001 · tot) |
| `DO_SVV3_SONDE3` | STRING(50) | SVV3 Sonde 3 Artikelnummer | ✓ |  | verwerfen (D-001 · tot) |
| `DO_SVV3_SONDE3_BEZ` | STRING(50) | SVV3 Sonde 3 Bezeichnung | ✓ |  | verwerfen (D-001 · tot) |
| `DO_SVV3_SONDE4` | STRING(50) | SVV3 Sonde 4 Artikelnummer | ✓ |  | verwerfen (D-001 · tot) |
| `DO_SVV3_SONDE4_BEZ` | STRING(50) | SVV3 Sonde 4 Bezeichnung | ✓ |  | verwerfen (D-001 · tot) |
| `DO_SVV3_SYSTEM` | STRING(50) | SVV3 System Artikelnummer | ✓ |  | verwerfen (D-001 · tot) |
| `DO_SVV3_SYSTEM_BEZ` | STRING(50) | SVV3 System Bezeichnung | ✓ |  | verwerfen (D-001 · tot) |
| `DO_SVV3_SYSTEM_OPTIONEN` | STRING(255) | SVV3 System Optionen | ✓ |  | verwerfen (D-001 · tot) |
| `DO_SVV3_SYSTEM_SERNR` | STRING(50) | SVV3 System Seriennummer | ✓ |  | verwerfen (D-001 · tot) |
| `DO_SVV3_VERTRAG_ART` | STRING(50) | SVV3 Vertrag-Art | ✓ |  | verwerfen (D-001 · tot) |
| `DO_SVV3_VETRAG_STATUS` | STRING(50) | SVV3 Vertrag-Status | ✓ |  | verwerfen (D-001 · tot) |
| `DO_SVV3_VOM` | DATE | SVV3 Vertrags-Datum | ✓ |  | verwerfen (D-001 · tot) |
| `DO_SVV3_WAGEN` | STRING(50) | SVV3 Wagen Artikel | ✓ |  | verwerfen (D-001 · tot) |
| `DO_SVV3_WAGEN_BEZ` | STRING(50) | SVV3 Wagen Bezeichnung | ✓ |  | verwerfen (D-001 · tot) |
| `DO_SVV3_WAGEN_SERNR` | STRING(50) | SVV3 Wagen Seriennummer | ✓ |  | verwerfen (D-001 · tot) |
| `DO_SVV3_ZAHLUNG` | STRING(50) | SVV3 Zahlungskonditionen | ✓ |  | verwerfen (D-001 · tot) |
| `DO_SVV4_APPSW` | STRING(50) | SVV4 Applikation (SW) | ✓ |  | verwerfen (D-001 · tot) |
| `DO_SVV4_BASE` | STRING(50) | SVV4 Betriebssystem (OS) | ✓ |  | verwerfen (D-001 · tot) |
| `DO_SVV4_BAUJAHR` | STRING(50) | SVV4 Baujahr (KHK) | ✓ |  | verwerfen (D-001 · tot) |
| `DO_SVV4_ELEKTRONIKVERSICHERUNG` | STRING(50) | SVV4 Elektronikversicherung | ✓ |  | verwerfen (D-001 · tot) |
| `DO_SVV4_ERFUELLUNGSORT` | STRING(50) | SVV4 Erfüllungsort (falls abw.) | ✓ |  | verwerfen (D-001 · tot) |
| `DO_SVV4_HERSTELLER` | STRING(50) | SVV4 Hersteller | ✓ |  | verwerfen (D-001 · tot) |
| `DO_SVV4_KOSTEN_FAHRTZONE` | CURRENCY | SVV4 Kosten Fahrtzone (KHK) | ✓ |  | verwerfen (D-001 · tot) |
| `DO_SVV4_KOSTEN_FAHRTZONE_VERT` | CURRENCY | SVV4 Kosten Fahrtzone (Vertrag) | ✓ |  | verwerfen (D-001 · tot) |
| `DO_SVV4_MAC` | STRING(50) | SVV4 MAC Adresse | ✓ |  | verwerfen (D-001 · tot) |
| `DO_SVV4_PRINTER` | STRING(50) | SVV4 Printer Artikelnummer | ✓ |  | verwerfen (D-001 · tot) |
| `DO_SVV4_PRINTER_BEZ` | STRING(50) | SVV4 Printer Bezeichnung | ✓ |  | verwerfen (D-001 · tot) |
| `DO_SVV4_PRINTER_SERNR` | STRING(50) | SVV4 Printer Seriennummer | ✓ |  | verwerfen (D-001 · tot) |
| `DO_SVV4_SONDE1` | STRING(50) | SVV4 Sonde 1 Artikelnummer | ✓ |  | verwerfen (D-001 · tot) |
| `DO_SVV4_SONDE1_BEZ` | STRING(50) | SVV4 Sonde 1 Bezeichnung | ✓ |  | verwerfen (D-001 · tot) |
| `DO_SVV4_SONDE2` | STRING(50) | SVV4 Sonde 2 Artikelnummer | ✓ |  | verwerfen (D-001 · tot) |
| `DO_SVV4_SONDE2_BEZ` | STRING(50) | SVV4 Sonde 2 Bezeichnung | ✓ |  | verwerfen (D-001 · tot) |
| `DO_SVV4_SONDE2_SERNR` | STRING(50) | SVV4 Sonde 2 Seriennummer | ✓ |  | verwerfen (D-001 · tot) |
| `DO_SVV4_SONDE3` | STRING(50) | SVV4 Sonde 3 Artikelnummer | ✓ |  | verwerfen (D-001 · tot) |
| `DO_SVV4_SONDE3_BEZ` | STRING(50) | SVV4 Sonde 3 Bezeichnung | ✓ |  | verwerfen (D-001 · tot) |
| `DO_SVV4_SONDE3_SERNR` | STRING(50) | SVV4 Sonde 3 Seriennummer | ✓ |  | verwerfen (D-001 · tot) |
| `DO_SVV4_SONDE4` | STRING(50) | SVV4 Sonde 4 Artikelnummer | ✓ |  | verwerfen (D-001 · tot) |
| `DO_SVV4_SONDE4_BEZ` | STRING(50) | SVV4 Sonde 4 Bezeichnung | ✓ |  | verwerfen (D-001 · tot) |
| `DO_SVV4_SONDE4_SERNR` | STRING(50) | SVV4 Sonde 4 Seriennummer | ✓ |  | verwerfen (D-001 · tot) |
| `DO_SVV4_SYSTEM` | STRING(50) | SVV4 System Artikelnummer | ✓ |  | verwerfen (D-001 · tot) |
| `DO_SVV4_SYSTEM_BEZ2` | STRING(50) | SVV4 System Bezeichnung 2 | ✓ |  | verwerfen (D-001 · tot) |
| `DO_SVV4_SYSTEM_OPTIONEN` | STRING(255) | SVV4 System Optionen | ✓ |  | verwerfen (D-001 · tot) |
| `DO_SVV4_SYSTEM_SERNR` | STRING(50) | SVV4 System Seriennummer | ✓ |  | verwerfen (D-001 · tot) |
| `DO_SVV4_VERTRAG_ART` | STRING(50) | SVV4 Vertrag-Art | ✓ |  | verwerfen (D-001 · tot) |
| `DO_SVV4_VOM` | DATE | SVV4 Vertrags-Datum | ✓ |  | verwerfen (D-001 · tot) |
| `DO_SVV4_WAGEN` | STRING(50) | SVV4 Wagen Artikel | ✓ |  | verwerfen (D-001 · tot) |
| `DO_SVV4_WAGEN_BEZ` | STRING(50) | SVV4 Wagen Bezeichnung | ✓ |  | verwerfen (D-001 · tot) |
| `DO_SVV4_WAGEN_SERNR` | STRING(50) | SVV4 Wagen Seriennummer | ✓ |  | verwerfen (D-001 · tot) |
| `DO_SVV4_ZAHLUNG` | STRING(50) | SVV4 Zahlungskonditionen | ✓ |  | verwerfen (D-001 · tot) |
| `DO_SVV5_APPSW` | STRING(50) | SVV5 Applikation (SW) | ✓ |  | verwerfen (D-001 · tot) |
| `DO_SVV5_BASE` | STRING(50) | SVV5 Betriebssystem (OS) | ✓ |  | verwerfen (D-001 · tot) |
| `DO_SVV5_ELEKTRONIKVERSICHERUNG` | STRING(50) | SVV5 Elektronikversicherung | ✓ |  | verwerfen (D-001 · tot) |
| `DO_SVV5_ERFUELLUNGSORT` | STRING(50) | SVV5 Erfüllungsort (falls abw.) | ✓ |  | verwerfen (D-001 · tot) |
| `DO_SVV5_HERSTELLER` | STRING(50) | SVV5 Hersteller | ✓ |  | verwerfen (D-001 · tot) |
| `DO_SVV5_KOSTEN_FAHRTZONE` | CURRENCY | SVV5 Kosten Fahrtzone (KHK) | ✓ |  | verwerfen (D-001 · tot) |
| `DO_SVV5_KOSTEN_FAHRTZONE_VERT` | CURRENCY | SVV5 Kosten Fahrtzone (Vertrag) | ✓ |  | verwerfen (D-001 · tot) |
| `DO_SVV5_MAC` | STRING(50) | SVV5 MAC Adresse | ✓ |  | verwerfen (D-001 · tot) |
| `DO_SVV5_PRINTER` | STRING(50) | SVV5 Printer Artikelnummer | ✓ |  | verwerfen (D-001 · tot) |
| `DO_SVV5_PRINTER_SERNR` | STRING(50) | SVV5 Printer Seriennummer | ✓ |  | verwerfen (D-001 · tot) |
| `DO_SVV5_SONDE1` | STRING(50) | SVV5 Sonde 1 Artikelnummer | ✓ |  | verwerfen (D-001 · tot) |
| `DO_SVV5_SONDE1_BEZ` | STRING(50) | SVV5 Sonde 1 Bezeichnung | ✓ |  | verwerfen (D-001 · tot) |
| `DO_SVV5_SONDE1_SERNR` | STRING(50) | SVV5 Sonde 1 Seriennummer | ✓ |  | verwerfen (D-001 · tot) |
| `DO_SVV5_SONDE2` | STRING(50) | SVV5 Sonde 2 Artikelnummer | ✓ |  | verwerfen (D-001 · tot) |
| `DO_SVV5_SONDE2_BEZ` | STRING(50) | SVV5 Sonde 2 Bezeichnung | ✓ |  | verwerfen (D-001 · tot) |
| `DO_SVV5_SONDE2_SERNR` | STRING(50) | SVV5 Sonde 2 Seriennummer | ✓ |  | verwerfen (D-001 · tot) |
| `DO_SVV5_SONDE3` | STRING(50) | SVV5 Sonde 3 Artikelnummer | ✓ |  | verwerfen (D-001 · tot) |
| `DO_SVV5_SONDE3_BEZ` | STRING(50) | SVV5 Sonde 3 Bezeichnung | ✓ |  | verwerfen (D-001 · tot) |
| `DO_SVV5_SONDE4` | STRING(50) | SVV5 Sonde 4 Artikelnummer | ✓ |  | verwerfen (D-001 · tot) |
| `DO_SVV5_SONDE4_BEZ` | STRING(50) | SVV5 Sonde 4 Bezeichnung | ✓ |  | verwerfen (D-001 · tot) |
| `DO_SVV5_SONDE4_SERNR` | STRING(50) | SVV5 Sonde 4 Seriennummer | ✓ |  | verwerfen (D-001 · tot) |
| `DO_SVV5_SYSTEM` | STRING(50) | SVV5 System Artikelnummer | ✓ |  | verwerfen (D-001 · tot) |
| `DO_SVV5_SYSTEM_BEZ2` | STRING(50) | SVV5 System Bezeichnung 2 | ✓ |  | verwerfen (D-001 · tot) |
| `DO_SVV5_SYSTEM_OPTIONEN` | STRING(255) | SVV5 System Optionen | ✓ |  | verwerfen (D-001 · tot) |
| `DO_SVV5_SYSTEM_SERNNR` | STRING(50) | SVV5 System Seriennummer | ✓ |  | verwerfen (D-001 · tot) |
| `DO_SVV5_VOM` | DATE | SVV5 Vertrags-Datum | ✓ |  | verwerfen (D-001 · tot) |
| `DO_SVV5_WAGEN_BEZ` | STRING(50) | SVV5 Wagen Bezeichnung | ✓ |  | verwerfen (D-001 · tot) |
| `DO_SVV6_SYSTEM` | STRING(50) | SVV6 System Artikelnummer | ✓ |  | verwerfen (D-001 · tot) |
| `DO_SVV6_VETRAG_STATUS` | STRING(50) | SVV6 Vertrag-Status | ✓ |  | verwerfen (D-001 · tot) |
| `DO_SVV_ELEKTRONIKVERSICHERUNG` | STRING(50) | SVV Elektronikversicherung | ✓ |  | verwerfen (D-001 · tot) |
| `DO_SVV_HERSTELLER` | STRING(50) | SVV Hersteller | ✓ |  | verwerfen (D-001 · tot) |
| `DO_SVV_KOSTEN` | CURRENCY | SVV Kosten Grundwartung (KHK) | ✓ |  | verwerfen (D-001 · tot) |
| `DO_SVV_KOSTEN_FAHRTZONE` | CURRENCY | SVV Kosten Fahrtzone (KHK) | ✓ |  | verwerfen (D-001 · tot) |
| `DO_SVV_KOSTEN_FAHRTZONE_VERTRA` | CURRENCY | SVV Kosten Fahrtzone (Vertrag) | ✓ |  | verwerfen (D-001 · tot) |
| `DO_SVV_MAC` | STRING(50) | SVV MAC-Adresse | ✓ |  | verwerfen (D-001 · tot) |
| `DO_SVV_PRAXIS_ASP` | STRING(50) | Praxis Ansprechpartner | ✓ |  | verwerfen (D-001 · tot) |
| `DO_SVV_PRAXISSW` | STRING(50) | Praxis-Software | ✓ |  | **behalten** → IT-/Remote-Access-Doku (Ziel wahrsch. Device/Location; D-001) |
| `DO_SVV_PRAXISSW_BEMERKUNG` | STRING(255) | SVV Praxis-EDV Bemerkung | ✓ |  | **behalten** → IT-/Remote-Access-Doku (Ziel wahrsch. Device/Location; D-001) |
| `DO_SVV_PRAXISSW_BENUTZER` | STRING(50) | Server Benutzer | ✓ |  | **behalten** → IT-/Remote-Access-Doku (Ziel wahrsch. Device/Location; D-001) |
| `DO_SVV_PRAXISSW_GATE` | STRING(50) | Server Standardgate | ✓ |  | **behalten** → IT-/Remote-Access-Doku (Ziel wahrsch. Device/Location; D-001) |
| `DO_SVV_PRAXISSW_HWASP` | STRING(50) | Praxis-EDV Hardware ASP | ✓ |  | **behalten** → IT-/Remote-Access-Doku (Ziel wahrsch. Device/Location; D-001) |
| `DO_SVV_PRAXISSW_IP` | STRING(50) | Server IP-Adresse | ✓ |  | **behalten** → IT-/Remote-Access-Doku (Ziel wahrsch. Device/Location; D-001) |
| `DO_SVV_PRAXISSW_ITASP` | STRING(50) | Praxis-EDV IT ASP | ✓ |  | **behalten** → IT-/Remote-Access-Doku (Ziel wahrsch. Device/Location; D-001) |
| `DO_SVV_PRAXISSW_NETZSPEICHER` | STRING(50) | Server Netzspeicher | ✓ |  | **behalten** → IT-/Remote-Access-Doku (Ziel wahrsch. Device/Location; D-001) |
| `DO_SVV_PRAXISSW_PASSWORT` | STRING(50) | Server Passwort | ✓ |  | **behalten** → IT-/Remote-Access-Doku (Ziel wahrsch. Device/Location; D-001) |
| `DO_SVV_PRAXISSW_STOR_AETITEL` | STRING(50) | Praxis AE Title Speicher | ✓ |  | **behalten** → IT-/Remote-Access-Doku (Ziel wahrsch. Device/Location; D-001) |
| `DO_SVV_PRAXISSW_STOR_PORT` | STRING(50) | Praxis Port Speicher | ✓ |  | **behalten** → IT-/Remote-Access-Doku (Ziel wahrsch. Device/Location; D-001) |
| `DO_SVV_PRAXISSW_SUB` | STRING(50) | Server Subnetzmaske | ✓ |  | **behalten** → IT-/Remote-Access-Doku (Ziel wahrsch. Device/Location; D-001) |
| `DO_SVV_PRAXISSW_WL_PORT` | STRING(50) | Praxis Port Arbeitsliste | ✓ |  | **behalten** → IT-/Remote-Access-Doku (Ziel wahrsch. Device/Location; D-001) |
| `DO_SVV_PRAXISSW_WL_TITLE` | STRING(50) | Praxis AE Title Arbeitsliste | ✓ |  | **behalten** → IT-/Remote-Access-Doku (Ziel wahrsch. Device/Location; D-001) |
| `DO_SVV_PRINER_BEZ` | STRING(50) | SVV Printer Bezeichnung | ✓ |  | verwerfen (D-001 · tot) |
| `DO_SVV_PRINTER` | STRING(50) | SVV Printer Artikelnummer | ✓ |  | verwerfen (D-001 · tot) |
| `DO_SVV_PRINTER_SERNR` | STRING(50) | SVV Printer Seriennummer | ✓ |  | verwerfen (D-001 · tot) |
| `DO_SVV_SONDE1_BEZ` | STRING(50) | SVV Sonde 1 Bezeichnung | ✓ |  | verwerfen (D-001 · tot) |
| `DO_SVV_SONDE1_SERNR` | STRING(50) | SVV Sonde 1 Seriennummer | ✓ |  | verwerfen (D-001 · tot) |
| `DO_SVV_SYSTEM_OPTIONEN` | STRING(255) | SVV System Optionen | ✓ |  | verwerfen (D-001 · tot) |
| `DO_SVV_SYSTEM_SERNR` | STRING(50) | SVV System Seriennummer | ✓ |  | verwerfen (D-001 · tot) |
| `DO_SVV_VERTRAG_STATUS` | STRING(50) | SVV Vertrag-Status | ✓ |  | verwerfen (D-001 · tot) |
| `DO_SVV_VOM` | DATE | SVV Vertrags-Datum | ✓ |  | verwerfen (D-001 · tot) |
| `DO_SVV_WAGEN` | STRING(50) | SVV Wagen Artikel | ✓ |  | verwerfen (D-001 · tot) |
| `DO_SVV_WAGEN_BEZ` | STRING(50) | SVV Wagen Bezeichnung | ✓ |  | verwerfen (D-001 · tot) |
| `DO_SVV_WAGEN_SERNR` | STRING(50) | SVV Wagen Seriennummer | ✓ |  | verwerfen (D-001 · tot) |
| `DSGVO` | BOOLEAN | Datenschutzgrundverordnung | ✓ |  | offen |
| `DSGVOEWFAX` | BOOLEAN | Einwilligung FAX | ✓ |  | offen |
| `DSGVOEWMAIL` | BOOLEAN | Einwilligung MAIL | ✓ |  | offen |
| `DSGVOEWPOST` | BOOLEAN | Einwilligung POST | ✓ |  | offen |
| `DSGVOEWSMS` | BOOLEAN | Einwilligung SMS | ✓ |  | offen |
| `DSGVOEWTELEFON` | BOOLEAN | Einwilligung TELEFON | ✓ |  | offen |
| `durchwenwas1` | STRING(30) | durch wen/was 1 |  |  | offen |
| `durchwenwas2` | STRING(30) | durch wen/was 2 |  |  | offen |
| `EBIDINFO` | STRING(200) | EBID-Info |  |  | offen |
| `EBIDNUMBER` | STRING(30) | EBID-Nummer |  |  | offen |
| `EBIDSTATUS` | STRING(64) | EBID-Status |  |  | offen |
| `Eingangsdatum1` | STRING(30) | Eingangsdatum 1 |  |  | offen |
| `Eingangsdatum2` | STRING(30) | Eingangsdatum 2 |  |  | offen |
| `EmpRecruitmentDate` | DATETIME | Einstellungsdatum |  |  | offen |
| `EmpSeparationDate` | DATETIME | Austrittsdatum |  |  | offen |
| `EmpStatus` | STRING(30) | Servicevertrag |  |  | offen |
| `EVACTIVITYSCORE1` | INT | Evalanche Activity Score 1 |  |  | offen |
| `EVACTIVITYSCORE2` | INT | Evalanche Activity Score 2 |  |  | offen |
| `EVFORMOFORIGIN1` | STRING(100) | Evalanche Ursprungsformular 1 |  |  | offen |
| `EVFORMOFORIGIN2` | STRING(100) | Evalanche Ursprungsformular 2 |  |  | offen |
| `EVLASTSYNC` | DATETIME | Letzte Synchronisation |  |  | offen |
| `EVLASTUPDATE1` | DATETIME | Letzte Aktualisierung 1 |  |  | offen |
| `EVLASTUPDATE2` | DATETIME | Letzte Aktualisierung 2 |  |  | offen |
| `EVPERMISSION1` | STRING(50) | Evalanche Permission 1 |  |  | offen |
| `EVPERMISSION2` | STRING(50) | Evalanche Permission 2 |  |  | offen |
| `EVPROFILE1` | STRING(100) | Evalanche Profil 1 |  |  | offen |
| `EVPROFILE2` | STRING(100) | Evalanche Profil 2 |  |  | offen |
| `EVPROFILESCORE1` | INT | Evalanche Profile Score 1 |  |  | offen |
| `EVPROFILESCORE2` | INT | Evalanche Profile Score 2 |  |  | offen |
| `EVPROFILEURL1` | STRING(300) | Evalanche Profilauswertung 1 |  |  | offen |
| `EVPROFILEURL2` | STRING(300) | Evalanche Profilauswertung 2 |  |  | offen |
| `EVTRACKING1` | BOOLEAN | Evalanche Tracking deaktiviert 1 |  |  | offen |
| `EVTRACKING2` | BOOLEAN | Evalanche Tracking deaktiviert 2 |  |  | offen |
| `explInt1` | STRING(30) | expl. Int. 1 |  |  | offen |
| `Fachrichtung` | STRING(150) | Fachrichtung |  |  | offen |
| `FAHRTZONENPAUSCHALE` | DECIMAL | FAHRTZONENPAUSCHALE | ✓ |  | offen |
| `FANPORTFOLIO` | STRING(255) | fan!-Portfolio-Gruppe |  |  | offen |
| `FaxFieldStr1` | STRING(30) | Fax (Geschäftlich) |  |  | offen |
| `FaxFieldStr2` | STRING(30) | Not in Use 5  -  war Fax (Mobil) |  |  | offen |
| `FaxFieldStr3` | STRING(30) | Not in Use 6 - war Fax (PC) |  |  | offen |
| `FaxFieldStr4` | STRING(30) | Fax (Privat) |  |  | offen |
| `FaxFieldStr5` | STRING(30) | Fax |  |  | offen |
| `FinancialInstitute` | STRING(50) | Kreditinstitut |  |  | offen |
| `FirstContact` | STRING(30) | Erstkontakt |  |  | offen |
| `FirstContactDate` | DATETIME | Erstkontaktdatum |  |  | offen |
| `GEOCODESTATUS` | STRING(64) | Georeferenzierungsstatus |  |  | offen |
| `Gifts` | STRING(80) | Geschenke |  |  | offen |
| `gwAdditionalInfo1` | STRING(100) | Namenszusatz |  |  | offen |
| `gwAdditionalInfo2` | STRING(100) | Lieferort (Institution/Praxis) |  |  | offen |
| `gwAdditionalInfo3` | STRING(100) | Zusatzinfo(Privat) |  |  | offen |
| `gwBIC` | STRING(11) | BIC |  |  | offen |
| `gwBirthPlace` | STRING(30) | Geburtsort |  |  | offen |
| `gwBranch` | STRING(60) | Briefanrede(F) |  |  | offen |
| `GWCOMPANYLEGALFORM` | STRING(500) | Rechtliche Informationen |  |  | offen |
| `gwCostCenter` | STRING(30) | Kostenstelle |  |  | offen |
| `gwDeactivated` | BOOLEAN | Adresse deaktiviert |  |  | offen |
| `gwDenomination` | STRING(50) | Konfession |  |  | offen |
| `gwDepartment2` | STRING(30) | Not in Use 2 |  |  | offen |
| `GWDISTRICTCOURT` | STRING(40) | Registerstandort |  |  | offen |
| `GWEXTERNALADDRESSDATE` | DATETIME | Geprüft am |  |  | offen |
| `GWEXTERNALADDRESSNAME` | STRING(40) | Geprüft durch |  |  | offen |
| `gwFunction2` | STRING(100) | _not in Use |  |  | offen |
| `GWGENDER` | STRING(30) | Geschlecht |  |  | offen |
| `GWHDOACCESSTYPE` | STRING(100) | Helpdesk online |  |  | offen |
| `gwIBAN` | STRING(42) | IBAN |  |  | offen |
| `gwIsCompany` | BOOLEAN | ist Firma |  |  | offen |
| `gwIsContact` | BOOLEAN | ist Kontaktperson |  |  | offen |
| `gwIsEmployee` | BOOLEAN | ist Mitarbeiter |  |  | offen |
| `GWISEXTERNALEMPLOYEE` | BOOLEAN | Externer Mitarbeiter |  |  | offen |
| `gwKeepContactSynchron` | BOOLEAN | Feldwerte synchron |  |  | offen |
| `gwNationality` | STRING(50) | Staatsangehörigkeit |  |  | offen |
| `gwPersonInCharge` | STRING(255) | Not in Use 3 |  |  | offen |
| `gwPersonnelNumber` | STRING(30) | Personalnummer |  |  | offen |
| `gwSalesActivityTypeAllowed` | STRING(100) | Erlaubte Kontaktarten |  |  | offen |
| `gwSalesActivityTypePreferred` | STRING(20) | Bevorzugte Kontaktart |  |  | offen |
| `GWSSERVICEPASSWORDSET` | BOOLEAN | Helpdesk online Kennwort vergeben |  |  | offen |
| `GWSSTATUS` | STRING(80) | Status |  |  | offen |
| `GWSTATE1` | STRING(30) | Staat/Region |  |  | offen |
| `GWSTATE2` | STRING(30) | Staat/Region(Liefer) |  |  | offen |
| `GWSTATE3` | STRING(30) | Staat/Region(Privat) |  |  | offen |
| `GWSTYPE` | STRING(80) | Typ |  |  | offen |
| `GWTRADEREGISTER` | STRING(100) | Registernummer |  |  | offen |
| `GWTRUSTEE` | STRING(40) | Verantwortlicher (alt) |  |  | offen |
| `HDBLOCKEDFORSUPPORT` | BOOLEAN | Gesperrt für Support |  |  | offen |
| `ImFieldStr1` | STRING(100) | Instant Messaging |  |  | offen |
| `ImFieldStr4` | STRING(100) | Skype |  |  | offen |
| `IMFIELDSTR6` | STRING(100) | Skype for Business |  |  | offen |
| `InHouseZip` | STRING(20) | Hauspostcode |  |  | offen |
| `Interessean1` | STRING(30) | Interesse an 1 |  |  | offen |
| `Interessean2` | STRING(30) | Interesse an 2 |  |  | offen |
| `ITDANZAHLMA` | STRING(20) | Anzahl Mitarbeiter |  |  | offen |
| `ITDKLASSIFIZIERUNG` | STRING(20) | Klassifizierung |  |  | offen |
| `ITDLASTCONTACT` | DATETIME | Letzter Kontakt am |  |  | offen |
| `Kaufdatum` | STRING(30) | letztes Kaufdatum |  |  | offen |
| `KSgemeldetan` | STRING(30) | KS gemeldet an: |  |  | offen |
| `KSgewhrtan` | STRING(30) | generelle Bearbeitung |  |  | offen |
| `LASTCONTACTMEDIUM` | STRING(200) | Letzter Kontakt über |  |  | offen |
| `LASTCONTACTUSER` | STRING(200) | Kontaktperson |  |  | offen |
| `LEASING` | STRING(50) | Leasing | ✓ |  | offen |
| `LeisureActivities` | STRING(40) | Vorlieben |  |  | offen |
| `letzteAktion` | DATETIME | letzte Aktion |  |  | offen |
| `lostOrderan` | STRING(30) | _lost Order an |  |  | offen |
| `MailFieldStr1` | STRING(100) | E-Mail - geschäftlich Arzt |  |  | offen |
| `MailFieldStr2` | STRING(100) | E-Mail - Rechnungsversand |  |  | offen |
| `MailFieldStr3` | STRING(100) | E-Mail - Privat |  |  | offen |
| `MailFieldStr4` | STRING(100) | not in Use 7 - war E-Mail (Privat 2) |  |  | offen |
| `MailFieldStr5` | STRING(100) | E-Mail - Praxis |  |  | offen |
| `Mitarbeiter` | STRING(30) | Verantwortlicher Sales |  |  | offen |
| `Mobilausgeh` | STRING(30) | Mobil ausgehändigt |  |  | offen |
| `Name` | STRING(30) | Name |  |  | offen |
| `Notes` | STRING(255) | Schlagworte |  |  | offen |
| `NOTES2` | STRING(-1) | Schlagworte |  |  | offen |
| `nurfrMEDICA` | STRING(30) | _altes Medica Feld - Schrott |  |  | offen |
| `Payment` | STRING(30) | Zahlungsart |  |  | offen |
| `PhoneFieldStr1` | STRING(30) | Not in Use 4 - 1 - leer |  |  | offen |
| `PhoneFieldStr10` | STRING(30) | Telefon -Zentrale- |  |  | offen |
| `PhoneFieldStr2` | STRING(30) | Mobil - persönlich - |  |  | offen |
| `PhoneFieldStr3` | STRING(30) | Not in Use / jetzt Telefon - mobil - |  |  | offen |
| `PhoneFieldStr4` | STRING(30) | Telefon -persönliche Durchwahl- |  |  | offen |
| `PhoneFieldStr5` | STRING(30) | Mobil - privat - |  |  | offen |
| `PhoneFieldStr6` | STRING(30) | Mobil - persönlich Arzt - |  |  | offen |
| `PhoneFieldStr7` | STRING(30) | Telefon -Privat- |  |  | offen |
| `PhoneFieldStr8` | STRING(30) | Mobil - Zentrale Durchwahl - |  |  | offen |
| `PhoneFieldStr9` | STRING(30) | Telefon -Durchwahl- |  |  | offen |
| `PoBox1` | STRING(15) | Postfach |  |  | offen |
| `PoBox2` | STRING(15) | Lieferung (Postfach) |  |  | offen |
| `PoBoxZip1` | STRING(15) | Postfach PLZ |  |  | offen |
| `PoBoxZip2` | STRING(15) | Lieferung (Postfach PLZ) |  |  | offen |
| `POTOWN1` | STRING(30) | Postfach Ort |  |  | offen |
| `POTOWN2` | STRING(30) | Lieferung (Postfach Ort) |  |  | offen |
| `PREFERREDLANGUAGE` | STRING(80) | Bevorzugte Sprache |  |  | offen |
| `PREISANPASSUNG_2025` | BOOLEAN | Hinweis Preisanpaasung erfolgt | ✓ |  | offen |
| `Quelle1` | INT | Quelle 1 |  |  | offen |
| `Quelle2` | INT | Quelle 2 |  |  | offen |
| `Rckkauf` | STRING(30) | Rückkauf |  |  | offen |
| `Rebate` | DECIMAL | Rabatt |  |  | offen |
| `Referenzkunde` | DECIMAL | Referenzkunde löschen |  |  | offen |
| `RESPONSIBLE_SALES` | STRING(50) | Verantwortlicher (Sales) | ✓ |  | offen |
| `RESPONSIBLE_SERVICE` | STRING(50) | Verantwortlicher (Service) | ✓ |  | offen |
| `SERVER_NAME` | STRING(32) | Server Name | ✓ |  | offen |
| `SERVER_WLAN_PASSWORD` | STRING(64) | Server WLAN Password | ✓ |  | offen |
| `SERVER_WLAN_SSID` | STRING(64) | Server WLAN SSID | ✓ |  | offen |
| `SONOGDT_MINDRAY` | STRING(24) | SonoGDT - Mindray | ✓ |  | offen |
| `SONOGDT_MINDRAY_MENGE` | INT | SonoGDT - Mindray (Anzahl) | ✓ |  | offen |
| `Street1` | STRING(45) | Straße |  |  | offen |
| `Street2` | STRING(45) | Lieferung (Straße) |  |  | offen |
| `Street3` | STRING(45) | Straße (Privat) |  |  | offen |
| `Suburb1` | STRING(30) | Teilort |  |  | offen |
| `Suburb2` | STRING(30) | Lieferung (Teilort) |  |  | offen |
| `Suburb3` | STRING(30) | Teilort (Privat) |  |  | offen |
| `SunlightStatus` | STRING(30) | Sunlight Status |  |  | offen |
| `SVV2_VERTRAGS_INTERVALL` | STRING(11) | SVV2 Vertrags-Intervall | ✓ |  | verwerfen (D-001 · tot) |
| `SVV3_VERTRAGS_INTERVALL` | STRING(50) | SVV3 Vertrags-Intervall | ✓ |  | verwerfen (D-001 · tot) |
| `SVV4_VERTRAGS_INTERVALL` | STRING(50) | SVV4 Vertrags-Intervall | ✓ |  | verwerfen (D-001 · tot) |
| `SVV_VERTRAGS_INTERVALL` | STRING(50) | SVV Vertrags-Intervall | ✓ |  | verwerfen (D-001 · tot) |
| `TAXNUMBER` | STRING(30) | Steuernummer |  |  | offen |
| `Title` | STRING(30) | Titel |  |  | offen |
| `Town1` | STRING(30) | Ort |  |  | offen |
| `Town2` | STRING(30) | Lieferung (Ort) |  |  | offen |
| `Town3` | STRING(30) | Ort (Privat) |  |  | offen |
| `TurnOver` | DECIMAL | Umsatz |  |  | offen |
| `TurnOverGroup` | STRING(30) | Umsatzgruppe |  |  | offen |
| `TurnoverTaxId` | STRING(30) | Umsatzsteuer-ID |  |  | offen |
| `USStatus` | STRING(30) | US-Status (alt/Schrott) |  |  | offen |
| `VERSAKTIV` | BOOLEAN | Vers. aktiv | ✓ |  | offen |
| `VERSICHERUNG` | STRING(50) | Versicherung | ✓ |  | offen |
| `was` | STRING(30) | was |  |  | offen |
| `wasgekauft` | STRING(100) | was gekauft |  |  | offen |
| `WWWFieldStr1` | STRING(100) | Homepage |  |  | offen |
| `WWWFieldStr2` | STRING(100) | Link |  |  | offen |
| `WWWFieldStr3` | STRING(100) | Link 2 |  |  | offen |
| `WWWFieldStr4` | STRING(100) | Link 3 |  |  | offen |
| `WWWFieldStr5` | STRING(100) | Homepage privat |  |  | offen |
| `Zip1` | STRING(10) | PLZ |  |  | offen |
| `Zip2` | STRING(10) | Lieferung (PLZ) |  |  | offen |
| `Zip3` | STRING(10) | PLZ (Privat) |  |  | offen |
