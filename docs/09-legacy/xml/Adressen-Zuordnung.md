# Adressen — Feld-Zuordnung (Entwurf)

Vorschlag je Legacy-Feld → Zielmodell. Basis: D-001 – D-017 im `../../07-decisions/grill-log.md`.
Review bucket­weise: `Ziel` bestätigen/korrigieren, dann Synthese nach `docs/04-domain/`.

| Feld | Typ | Label | Ziel | Herkunft/Notiz |
| --- | --- | --- | --- | --- |
| `AddressLetter` | STRING(60) | Briefanrede | verwerfen | Anrede-Zeilen (D-015) |
| `AddressTerm` | STRING(30) | Anrede | verwerfen | Anrede-Zeilen (D-015) |
| `ADRKHKMATCHCODE` | STRING(100) | Deb./Kred. Matchcode | Company · Buchhaltung | D-009 |
| `AdrNumber` | STRING(30) | Deb./Kred. Konto | Company · Buchhaltung | D-009 |
| `Anrede2` | STRING(60) | Anrede 2 | verwerfen | Anrede-Zeilen (D-015) |
| `Anrede3` | STRING(60) | Anrede 3 | verwerfen | Anrede-Zeilen (D-015) |
| `Anrede4` | STRING(60) | Anrede 4 | verwerfen | Anrede-Zeilen (D-015) |
| `Anschaffungwan` | STRING(30) | wann gekauft | verwerfen | Legacy-Wildwuchs (D-017) |
| `AVV` | BOOLEAN | Auftragsverarbeitungsvertrag | Company · avv_signed_at | D-013 |
| `BankAccountHolder` | STRING(30) | Kontoinhaber | Company · Bank | — |
| `BankAccountNr` | STRING(20) | Kontonummer | verwerfen | Kto/BLZ → durch IBAN/BIC ersetzt |
| `BankZipNr` | STRING(20) | Bankleitzahl | verwerfen | Kto/BLZ → durch IBAN/BIC ersetzt |
| `Birthday` | DATETIME | Geburtstag | verwerfen | Beziehungspflege/Personendetail — offen |
| `BirthdayGreetings` | BOOLEAN | Geburtstagskarte | verwerfen | Beziehungspflege/Personendetail — offen |
| `BMeABl` | DATETIME | _BMe/ABl | verwerfen | Legacy-Wildwuchs (D-017) |
| `BudgetfKauf` | INT | Budget f. Kauf | verwerfen | Legacy-Wildwuchs (D-017) |
| `CASFunction` | STRING(100) | Funktion Ansprechpartner | CompanyContact · role | = role-String (D-005) |
| `CASWIDNR` | STRING(25) | Wirtschafts-Identifikationsnummer | Company · Recht/Steuer | — |
| `Category` | STRING(255) | Kategorie | verwerfen | Segmentierung/Sales-Pflege (D-017) |
| `ChristianName` | STRING(30) | Vorname | Person · first_name | — |
| `ChristmasGreetings` | BOOLEAN | Weihnachtskarte | verwerfen | Beziehungspflege/Personendetail — offen |
| `CompName` | STRING(255) | Firma (Anrede) | Company · name | Firma (Anrede) |
| `CompName2` | STRING(60) | Name | verwerfen | Kurzname-Dublette zu CompName — prüfen |
| `COUNTRY1` | STRING(80) | Land | Address (Sitz) | Sitzadresse (D-014) |
| `COUNTRY2` | STRING(80) | Lieferung (Land) | Location | Lieferadresse → Location (D-014) |
| `COUNTRY3` | STRING(80) | Land (Privat) | verwerfen | Privatadresse (D-014) |
| `CurrencyNat` | STRING(3) | Währung | verwerfen | Segmentierung/Sales-Pflege (D-017) |
| `Department` | STRING(30) | Abteilung | CompanyContact · department | nullable String — prüfen |
| `DO_SV2_KOSTEN` | CURRENCY | SVV2 Kosten (Vertrag) | verwerfen | D-001 tot |
| `DO_SV2_KOSTEN_KHK` | CURRENCY | SVV2 Kosten (KHK) | verwerfen | D-001 tot |
| `DO_SV3_KOSTEN` | CURRENCY | SVV3 Kosten (Vertrag) | verwerfen | D-001 tot |
| `DO_SV3_KOSTEN_KHK` | CURRENCY | SVV3 Kosten (KHK) | verwerfen | D-001 tot |
| `DO_SV4_KOSTEN` | CURRENCY | SVV4 Kosten (Vertrag) | verwerfen | D-001 tot |
| `DO_SV4_KOSTEN_KHK` | CURRENCY | SVV4 Kosten (KHK) | verwerfen | D-001 tot |
| `DO_SV5_KOSTEN` | CURRENCY | SVV5 Kosten (Vertrag) | verwerfen | D-001 tot |
| `DO_SV5_KOSTEN_KHK` | CURRENCY | SVV5 Kosten (KHK) | verwerfen | D-001 tot |
| `DO_SV_KOSTEN` | CURRENCY | SVV Kosten (Vertrag) | verwerfen | D-001 tot |
| `DO_SV_KOSTEN_KHK` | CURRENCY | SVV Kosten (KHK) | verwerfen | D-001 tot |
| `DO_SVV2_HERSTELLER` | STRING(50) | SVV2 Hersteller | verwerfen | D-001 tot |
| `DO_SVV2_KOSTEN_FAHRTZONE` | CURRENCY | SVV2 Kosten Fahrtzone (KHK) | verwerfen | D-001 tot |
| `DO_SVV2_KOSTEN_FAHRTZONE_VERT` | CURRENCY | SVV2 Kosten Fahrtzone (Vertrag) | verwerfen | D-001 tot |
| `DO_SVV2_MAC` | STRING(50) | SVV2 MAC-Adresse | verwerfen | D-001 tot |
| `DO_SVV2_NAECHSTEWARTUNG` | DATE | SVV2 nächste Wartung | verwerfen | D-001 tot |
| `DO_SVV2_PRINER_BEZ` | STRING(50) | SVV2 Printer Bezeichnung | verwerfen | D-001 tot |
| `DO_SVV2_PRINTER_SERNR` | STRING(50) | SVV2 Printer Seriennummer | verwerfen | D-001 tot |
| `DO_SVV2_SONDE4_SERNR` | STRING(50) | SVV2 Sonde 4 Seriennummer | verwerfen | D-001 tot |
| `DO_SVV2_SYSTEM` | STRING(50) | SVV2 System Artikelnummer | verwerfen | D-001 tot |
| `DO_SVV2_SYSTEM_BEZ` | STRING(50) | SVV2 System Bezeichnung | verwerfen | D-001 tot |
| `DO_SVV2_SYSTEM_SERNR` | STRING(50) | SVV2 System Seriennummer | verwerfen | D-001 tot |
| `DO_SVV2_VERTRAG_ART` | STRING(50) | SVV2 Vertrag-Art | verwerfen | D-001 tot |
| `DO_SVV2_WAGEN` | STRING(50) | SVV2 Wagen Artikel | verwerfen | D-001 tot |
| `DO_SVV2_WAGEN_BEZ` | STRING(50) | SVV2 Wagen Bezeichnung | verwerfen | D-001 tot |
| `DO_SVV2_WAGEN_SERNR` | STRING(50) | SVV2 Wagen Seriennummer | verwerfen | D-001 tot |
| `DO_SVV2_ZAHLUNG` | STRING(50) | SVV2 Zahlungskonditionen | verwerfen | D-001 tot |
| `DO_SVV3_APPSW` | STRING(50) | SVV3 Applikation (SW) | verwerfen | D-001 tot |
| `DO_SVV3_BASE` | STRING(50) | SVV3 Betriebssystem (OS) | verwerfen | D-001 tot |
| `DO_SVV3_ELEKTRONIKVERSICHERUNG` | STRING(50) | SVV3 Elektronikversicherung | verwerfen | D-001 tot |
| `DO_SVV3_ERFUELLUNGSORT` | STRING(50) | SVV3 Erfüllungsort (falls abw.) | verwerfen | D-001 tot |
| `DO_SVV3_HERSTELLER` | STRING(50) | SVV3 Hersteller | verwerfen | D-001 tot |
| `DO_SVV3_KOSTEN_FAHRTZONE` | CURRENCY | SVV3 Kosten Fahrtzone (KHK) | verwerfen | D-001 tot |
| `DO_SVV3_KOSTEN_FAHRTZONE_VERT` | CURRENCY | SVV3 Kosten Fahrtzone (Vertrag) | verwerfen | D-001 tot |
| `DO_SVV3_MAC` | STRING(50) | SVV3 MAC Adresse | verwerfen | D-001 tot |
| `DO_SVV3_PRINER_BEZ` | STRING(50) | SVV3 Printer Bezeichnung | verwerfen | D-001 tot |
| `DO_SVV3_PRINTER` | STRING(50) | SVV3 Printer Artikelnummer | verwerfen | D-001 tot |
| `DO_SVV3_PRINTER_SERNR` | STRING(50) | SVV3 Printer Seriennummer | verwerfen | D-001 tot |
| `DO_SVV3_SONDE1` | STRING(50) | SVV3 Sonde 1 Artikelnummer | verwerfen | D-001 tot |
| `DO_SVV3_SONDE1_BEZ` | STRING(50) | SVV3 Sonde 1 Bezeichnung | verwerfen | D-001 tot |
| `DO_SVV3_SONDE2` | STRING(50) | SVV3 Sonde 2 Artikelnummer | verwerfen | D-001 tot |
| `DO_SVV3_SONDE2_BEZ` | STRING(50) | SVV3 Sonde 2 Bezeichnung | verwerfen | D-001 tot |
| `DO_SVV3_SONDE2_SERNR` | STRING(50) | SVV3 Sonde 2 Seriennummer | verwerfen | D-001 tot |
| `DO_SVV3_SONDE3` | STRING(50) | SVV3 Sonde 3 Artikelnummer | verwerfen | D-001 tot |
| `DO_SVV3_SONDE3_BEZ` | STRING(50) | SVV3 Sonde 3 Bezeichnung | verwerfen | D-001 tot |
| `DO_SVV3_SONDE4` | STRING(50) | SVV3 Sonde 4 Artikelnummer | verwerfen | D-001 tot |
| `DO_SVV3_SONDE4_BEZ` | STRING(50) | SVV3 Sonde 4 Bezeichnung | verwerfen | D-001 tot |
| `DO_SVV3_SYSTEM` | STRING(50) | SVV3 System Artikelnummer | verwerfen | D-001 tot |
| `DO_SVV3_SYSTEM_BEZ` | STRING(50) | SVV3 System Bezeichnung | verwerfen | D-001 tot |
| `DO_SVV3_SYSTEM_OPTIONEN` | STRING(255) | SVV3 System Optionen | verwerfen | D-001 tot |
| `DO_SVV3_SYSTEM_SERNR` | STRING(50) | SVV3 System Seriennummer | verwerfen | D-001 tot |
| `DO_SVV3_VERTRAG_ART` | STRING(50) | SVV3 Vertrag-Art | verwerfen | D-001 tot |
| `DO_SVV3_VETRAG_STATUS` | STRING(50) | SVV3 Vertrag-Status | verwerfen | D-001 tot |
| `DO_SVV3_VOM` | DATE | SVV3 Vertrags-Datum | verwerfen | D-001 tot |
| `DO_SVV3_WAGEN` | STRING(50) | SVV3 Wagen Artikel | verwerfen | D-001 tot |
| `DO_SVV3_WAGEN_BEZ` | STRING(50) | SVV3 Wagen Bezeichnung | verwerfen | D-001 tot |
| `DO_SVV3_WAGEN_SERNR` | STRING(50) | SVV3 Wagen Seriennummer | verwerfen | D-001 tot |
| `DO_SVV3_ZAHLUNG` | STRING(50) | SVV3 Zahlungskonditionen | verwerfen | D-001 tot |
| `DO_SVV4_APPSW` | STRING(50) | SVV4 Applikation (SW) | verwerfen | D-001 tot |
| `DO_SVV4_BASE` | STRING(50) | SVV4 Betriebssystem (OS) | verwerfen | D-001 tot |
| `DO_SVV4_BAUJAHR` | STRING(50) | SVV4 Baujahr (KHK) | verwerfen | D-001 tot |
| `DO_SVV4_ELEKTRONIKVERSICHERUNG` | STRING(50) | SVV4 Elektronikversicherung | verwerfen | D-001 tot |
| `DO_SVV4_ERFUELLUNGSORT` | STRING(50) | SVV4 Erfüllungsort (falls abw.) | verwerfen | D-001 tot |
| `DO_SVV4_HERSTELLER` | STRING(50) | SVV4 Hersteller | verwerfen | D-001 tot |
| `DO_SVV4_KOSTEN_FAHRTZONE` | CURRENCY | SVV4 Kosten Fahrtzone (KHK) | verwerfen | D-001 tot |
| `DO_SVV4_KOSTEN_FAHRTZONE_VERT` | CURRENCY | SVV4 Kosten Fahrtzone (Vertrag) | verwerfen | D-001 tot |
| `DO_SVV4_MAC` | STRING(50) | SVV4 MAC Adresse | verwerfen | D-001 tot |
| `DO_SVV4_PRINTER` | STRING(50) | SVV4 Printer Artikelnummer | verwerfen | D-001 tot |
| `DO_SVV4_PRINTER_BEZ` | STRING(50) | SVV4 Printer Bezeichnung | verwerfen | D-001 tot |
| `DO_SVV4_PRINTER_SERNR` | STRING(50) | SVV4 Printer Seriennummer | verwerfen | D-001 tot |
| `DO_SVV4_SONDE1` | STRING(50) | SVV4 Sonde 1 Artikelnummer | verwerfen | D-001 tot |
| `DO_SVV4_SONDE1_BEZ` | STRING(50) | SVV4 Sonde 1 Bezeichnung | verwerfen | D-001 tot |
| `DO_SVV4_SONDE2` | STRING(50) | SVV4 Sonde 2 Artikelnummer | verwerfen | D-001 tot |
| `DO_SVV4_SONDE2_BEZ` | STRING(50) | SVV4 Sonde 2 Bezeichnung | verwerfen | D-001 tot |
| `DO_SVV4_SONDE2_SERNR` | STRING(50) | SVV4 Sonde 2 Seriennummer | verwerfen | D-001 tot |
| `DO_SVV4_SONDE3` | STRING(50) | SVV4 Sonde 3 Artikelnummer | verwerfen | D-001 tot |
| `DO_SVV4_SONDE3_BEZ` | STRING(50) | SVV4 Sonde 3 Bezeichnung | verwerfen | D-001 tot |
| `DO_SVV4_SONDE3_SERNR` | STRING(50) | SVV4 Sonde 3 Seriennummer | verwerfen | D-001 tot |
| `DO_SVV4_SONDE4` | STRING(50) | SVV4 Sonde 4 Artikelnummer | verwerfen | D-001 tot |
| `DO_SVV4_SONDE4_BEZ` | STRING(50) | SVV4 Sonde 4 Bezeichnung | verwerfen | D-001 tot |
| `DO_SVV4_SONDE4_SERNR` | STRING(50) | SVV4 Sonde 4 Seriennummer | verwerfen | D-001 tot |
| `DO_SVV4_SYSTEM` | STRING(50) | SVV4 System Artikelnummer | verwerfen | D-001 tot |
| `DO_SVV4_SYSTEM_BEZ2` | STRING(50) | SVV4 System Bezeichnung 2 | verwerfen | D-001 tot |
| `DO_SVV4_SYSTEM_OPTIONEN` | STRING(255) | SVV4 System Optionen | verwerfen | D-001 tot |
| `DO_SVV4_SYSTEM_SERNR` | STRING(50) | SVV4 System Seriennummer | verwerfen | D-001 tot |
| `DO_SVV4_VERTRAG_ART` | STRING(50) | SVV4 Vertrag-Art | verwerfen | D-001 tot |
| `DO_SVV4_VOM` | DATE | SVV4 Vertrags-Datum | verwerfen | D-001 tot |
| `DO_SVV4_WAGEN` | STRING(50) | SVV4 Wagen Artikel | verwerfen | D-001 tot |
| `DO_SVV4_WAGEN_BEZ` | STRING(50) | SVV4 Wagen Bezeichnung | verwerfen | D-001 tot |
| `DO_SVV4_WAGEN_SERNR` | STRING(50) | SVV4 Wagen Seriennummer | verwerfen | D-001 tot |
| `DO_SVV4_ZAHLUNG` | STRING(50) | SVV4 Zahlungskonditionen | verwerfen | D-001 tot |
| `DO_SVV5_APPSW` | STRING(50) | SVV5 Applikation (SW) | verwerfen | D-001 tot |
| `DO_SVV5_BASE` | STRING(50) | SVV5 Betriebssystem (OS) | verwerfen | D-001 tot |
| `DO_SVV5_ELEKTRONIKVERSICHERUNG` | STRING(50) | SVV5 Elektronikversicherung | verwerfen | D-001 tot |
| `DO_SVV5_ERFUELLUNGSORT` | STRING(50) | SVV5 Erfüllungsort (falls abw.) | verwerfen | D-001 tot |
| `DO_SVV5_HERSTELLER` | STRING(50) | SVV5 Hersteller | verwerfen | D-001 tot |
| `DO_SVV5_KOSTEN_FAHRTZONE` | CURRENCY | SVV5 Kosten Fahrtzone (KHK) | verwerfen | D-001 tot |
| `DO_SVV5_KOSTEN_FAHRTZONE_VERT` | CURRENCY | SVV5 Kosten Fahrtzone (Vertrag) | verwerfen | D-001 tot |
| `DO_SVV5_MAC` | STRING(50) | SVV5 MAC Adresse | verwerfen | D-001 tot |
| `DO_SVV5_PRINTER` | STRING(50) | SVV5 Printer Artikelnummer | verwerfen | D-001 tot |
| `DO_SVV5_PRINTER_SERNR` | STRING(50) | SVV5 Printer Seriennummer | verwerfen | D-001 tot |
| `DO_SVV5_SONDE1` | STRING(50) | SVV5 Sonde 1 Artikelnummer | verwerfen | D-001 tot |
| `DO_SVV5_SONDE1_BEZ` | STRING(50) | SVV5 Sonde 1 Bezeichnung | verwerfen | D-001 tot |
| `DO_SVV5_SONDE1_SERNR` | STRING(50) | SVV5 Sonde 1 Seriennummer | verwerfen | D-001 tot |
| `DO_SVV5_SONDE2` | STRING(50) | SVV5 Sonde 2 Artikelnummer | verwerfen | D-001 tot |
| `DO_SVV5_SONDE2_BEZ` | STRING(50) | SVV5 Sonde 2 Bezeichnung | verwerfen | D-001 tot |
| `DO_SVV5_SONDE2_SERNR` | STRING(50) | SVV5 Sonde 2 Seriennummer | verwerfen | D-001 tot |
| `DO_SVV5_SONDE3` | STRING(50) | SVV5 Sonde 3 Artikelnummer | verwerfen | D-001 tot |
| `DO_SVV5_SONDE3_BEZ` | STRING(50) | SVV5 Sonde 3 Bezeichnung | verwerfen | D-001 tot |
| `DO_SVV5_SONDE4` | STRING(50) | SVV5 Sonde 4 Artikelnummer | verwerfen | D-001 tot |
| `DO_SVV5_SONDE4_BEZ` | STRING(50) | SVV5 Sonde 4 Bezeichnung | verwerfen | D-001 tot |
| `DO_SVV5_SONDE4_SERNR` | STRING(50) | SVV5 Sonde 4 Seriennummer | verwerfen | D-001 tot |
| `DO_SVV5_SYSTEM` | STRING(50) | SVV5 System Artikelnummer | verwerfen | D-001 tot |
| `DO_SVV5_SYSTEM_BEZ2` | STRING(50) | SVV5 System Bezeichnung 2 | verwerfen | D-001 tot |
| `DO_SVV5_SYSTEM_OPTIONEN` | STRING(255) | SVV5 System Optionen | verwerfen | D-001 tot |
| `DO_SVV5_SYSTEM_SERNNR` | STRING(50) | SVV5 System Seriennummer | verwerfen | D-001 tot |
| `DO_SVV5_VOM` | DATE | SVV5 Vertrags-Datum | verwerfen | D-001 tot |
| `DO_SVV5_WAGEN_BEZ` | STRING(50) | SVV5 Wagen Bezeichnung | verwerfen | D-001 tot |
| `DO_SVV6_SYSTEM` | STRING(50) | SVV6 System Artikelnummer | verwerfen | D-001 tot |
| `DO_SVV6_VETRAG_STATUS` | STRING(50) | SVV6 Vertrag-Status | verwerfen | D-001 tot |
| `DO_SVV_ELEKTRONIKVERSICHERUNG` | STRING(50) | SVV Elektronikversicherung | verwerfen | D-001 tot |
| `DO_SVV_HERSTELLER` | STRING(50) | SVV Hersteller | verwerfen | D-001 tot |
| `DO_SVV_KOSTEN` | CURRENCY | SVV Kosten Grundwartung (KHK) | verwerfen | D-001 tot |
| `DO_SVV_KOSTEN_FAHRTZONE` | CURRENCY | SVV Kosten Fahrtzone (KHK) | verwerfen | D-001 tot |
| `DO_SVV_KOSTEN_FAHRTZONE_VERTRA` | CURRENCY | SVV Kosten Fahrtzone (Vertrag) | verwerfen | D-001 tot |
| `DO_SVV_MAC` | STRING(50) | SVV MAC-Adresse | verwerfen | D-001 tot |
| `DO_SVV_PRAXIS_ASP` | STRING(50) | Praxis Ansprechpartner | verwerfen | D-001 tot |
| `DO_SVV_PRAXISSW` | STRING(50) | Praxis-Software | → Device/Praxis-IT | D-001-Ausnahme (Service-Bereich) |
| `DO_SVV_PRAXISSW_BEMERKUNG` | STRING(255) | SVV Praxis-EDV Bemerkung | → Device/Praxis-IT | D-001-Ausnahme (Service-Bereich) |
| `DO_SVV_PRAXISSW_BENUTZER` | STRING(50) | Server Benutzer | → Device/Praxis-IT | D-001-Ausnahme (Service-Bereich) |
| `DO_SVV_PRAXISSW_GATE` | STRING(50) | Server Standardgate | → Device/Praxis-IT | D-001-Ausnahme (Service-Bereich) |
| `DO_SVV_PRAXISSW_HWASP` | STRING(50) | Praxis-EDV Hardware ASP | → Device/Praxis-IT | D-001-Ausnahme (Service-Bereich) |
| `DO_SVV_PRAXISSW_IP` | STRING(50) | Server IP-Adresse | → Device/Praxis-IT | D-001-Ausnahme (Service-Bereich) |
| `DO_SVV_PRAXISSW_ITASP` | STRING(50) | Praxis-EDV IT ASP | → Device/Praxis-IT | D-001-Ausnahme (Service-Bereich) |
| `DO_SVV_PRAXISSW_NETZSPEICHER` | STRING(50) | Server Netzspeicher | → Device/Praxis-IT | D-001-Ausnahme (Service-Bereich) |
| `DO_SVV_PRAXISSW_PASSWORT` | STRING(50) | Server Passwort | → Device/Praxis-IT | D-001-Ausnahme (Service-Bereich) |
| `DO_SVV_PRAXISSW_STOR_AETITEL` | STRING(50) | Praxis AE Title Speicher | → Device/Praxis-IT | D-001-Ausnahme (Service-Bereich) |
| `DO_SVV_PRAXISSW_STOR_PORT` | STRING(50) | Praxis Port Speicher | → Device/Praxis-IT | D-001-Ausnahme (Service-Bereich) |
| `DO_SVV_PRAXISSW_SUB` | STRING(50) | Server Subnetzmaske | → Device/Praxis-IT | D-001-Ausnahme (Service-Bereich) |
| `DO_SVV_PRAXISSW_WL_PORT` | STRING(50) | Praxis Port Arbeitsliste | → Device/Praxis-IT | D-001-Ausnahme (Service-Bereich) |
| `DO_SVV_PRAXISSW_WL_TITLE` | STRING(50) | Praxis AE Title Arbeitsliste | → Device/Praxis-IT | D-001-Ausnahme (Service-Bereich) |
| `DO_SVV_PRINER_BEZ` | STRING(50) | SVV Printer Bezeichnung | verwerfen | D-001 tot |
| `DO_SVV_PRINTER` | STRING(50) | SVV Printer Artikelnummer | verwerfen | D-001 tot |
| `DO_SVV_PRINTER_SERNR` | STRING(50) | SVV Printer Seriennummer | verwerfen | D-001 tot |
| `DO_SVV_SONDE1_BEZ` | STRING(50) | SVV Sonde 1 Bezeichnung | verwerfen | D-001 tot |
| `DO_SVV_SONDE1_SERNR` | STRING(50) | SVV Sonde 1 Seriennummer | verwerfen | D-001 tot |
| `DO_SVV_SYSTEM_OPTIONEN` | STRING(255) | SVV System Optionen | verwerfen | D-001 tot |
| `DO_SVV_SYSTEM_SERNR` | STRING(50) | SVV System Seriennummer | verwerfen | D-001 tot |
| `DO_SVV_VERTRAG_STATUS` | STRING(50) | SVV Vertrag-Status | verwerfen | D-001 tot |
| `DO_SVV_VOM` | DATE | SVV Vertrags-Datum | verwerfen | D-001 tot |
| `DO_SVV_WAGEN` | STRING(50) | SVV Wagen Artikel | verwerfen | D-001 tot |
| `DO_SVV_WAGEN_BEZ` | STRING(50) | SVV Wagen Bezeichnung | verwerfen | D-001 tot |
| `DO_SVV_WAGEN_SERNR` | STRING(50) | SVV Wagen Seriennummer | verwerfen | D-001 tot |
| `DSGVO` | BOOLEAN | Datenschutzgrundverordnung | consents | D-013 (DSGVO-Info erteilt?) — prüfen |
| `DSGVOEWFAX` | BOOLEAN | Einwilligung FAX | consents | D-013 |
| `DSGVOEWMAIL` | BOOLEAN | Einwilligung MAIL | consents | D-013 |
| `DSGVOEWPOST` | BOOLEAN | Einwilligung POST | consents | D-013 |
| `DSGVOEWSMS` | BOOLEAN | Einwilligung SMS | consents | D-013 |
| `DSGVOEWTELEFON` | BOOLEAN | Einwilligung TELEFON | consents | D-013 |
| `durchwenwas1` | STRING(30) | durch wen/was 1 | verwerfen | Legacy-Wildwuchs (D-017) |
| `durchwenwas2` | STRING(30) | durch wen/was 2 | verwerfen | Legacy-Wildwuchs (D-017) |
| `EBIDINFO` | STRING(200) | EBID-Info | verwerfen | Marketing-Integration (D-011) |
| `EBIDNUMBER` | STRING(30) | EBID-Nummer | verwerfen | Marketing-Integration (D-011) |
| `EBIDSTATUS` | STRING(64) | EBID-Status | verwerfen | Marketing-Integration (D-011) |
| `Eingangsdatum1` | STRING(30) | Eingangsdatum 1 | verwerfen | Legacy-Wildwuchs (D-017) |
| `Eingangsdatum2` | STRING(30) | Eingangsdatum 2 | verwerfen | Legacy-Wildwuchs (D-017) |
| `EmpRecruitmentDate` | DATETIME | Einstellungsdatum | → Identity/Employee | D-012 (vertagt) |
| `EmpSeparationDate` | DATETIME | Austrittsdatum | → Identity/Employee | D-012 (vertagt) |
| `EmpStatus` | STRING(30) | Servicevertrag | → Identity/Employee | D-012 (vertagt) |
| `EVACTIVITYSCORE1` | INT | Evalanche Activity Score 1 | verwerfen | Marketing-Integration (D-011) |
| `EVACTIVITYSCORE2` | INT | Evalanche Activity Score 2 | verwerfen | Marketing-Integration (D-011) |
| `EVFORMOFORIGIN1` | STRING(100) | Evalanche Ursprungsformular 1 | verwerfen | Marketing-Integration (D-011) |
| `EVFORMOFORIGIN2` | STRING(100) | Evalanche Ursprungsformular 2 | verwerfen | Marketing-Integration (D-011) |
| `EVLASTSYNC` | DATETIME | Letzte Synchronisation | verwerfen | Marketing-Integration (D-011) |
| `EVLASTUPDATE1` | DATETIME | Letzte Aktualisierung 1 | verwerfen | Marketing-Integration (D-011) |
| `EVLASTUPDATE2` | DATETIME | Letzte Aktualisierung 2 | verwerfen | Marketing-Integration (D-011) |
| `EVPERMISSION1` | STRING(50) | Evalanche Permission 1 | verwerfen | Marketing-Integration (D-011) |
| `EVPERMISSION2` | STRING(50) | Evalanche Permission 2 | verwerfen | Marketing-Integration (D-011) |
| `EVPROFILE1` | STRING(100) | Evalanche Profil 1 | verwerfen | Marketing-Integration (D-011) |
| `EVPROFILE2` | STRING(100) | Evalanche Profil 2 | verwerfen | Marketing-Integration (D-011) |
| `EVPROFILESCORE1` | INT | Evalanche Profile Score 1 | verwerfen | Marketing-Integration (D-011) |
| `EVPROFILESCORE2` | INT | Evalanche Profile Score 2 | verwerfen | Marketing-Integration (D-011) |
| `EVPROFILEURL1` | STRING(300) | Evalanche Profilauswertung 1 | verwerfen | Marketing-Integration (D-011) |
| `EVPROFILEURL2` | STRING(300) | Evalanche Profilauswertung 2 | verwerfen | Marketing-Integration (D-011) |
| `EVTRACKING1` | BOOLEAN | Evalanche Tracking deaktiviert 1 | verwerfen | Marketing-Integration (D-011) |
| `EVTRACKING2` | BOOLEAN | Evalanche Tracking deaktiviert 2 | verwerfen | Marketing-Integration (D-011) |
| `explInt1` | STRING(30) | expl. Int. 1 | verwerfen | Legacy-Wildwuchs (D-017) |
| `Fachrichtung` | STRING(150) | Fachrichtung | Company · medical_specialty | D-017 (bleibt) |
| `FAHRTZONENPAUSCHALE` | DECIMAL | FAHRTZONENPAUSCHALE | offen · Bereich Service | — |
| `FANPORTFOLIO` | STRING(255) | fan!-Portfolio-Gruppe | verwerfen | Marketing-Integration (D-011) |
| `FaxFieldStr1` | STRING(30) | Fax (Geschäftlich) | contact_channels | D-010 |
| `FaxFieldStr2` | STRING(30) | Not in Use 5  -  war Fax (Mobil) | verwerfen | Legacy-Schrott |
| `FaxFieldStr3` | STRING(30) | Not in Use 6 - war Fax (PC) | verwerfen | Legacy-Schrott |
| `FaxFieldStr4` | STRING(30) | Fax (Privat) | contact_channels | D-010 |
| `FaxFieldStr5` | STRING(30) | Fax | contact_channels | D-010 |
| `FinancialInstitute` | STRING(50) | Kreditinstitut | Company · Bank | — |
| `FirstContact` | STRING(30) | Erstkontakt | verwerfen | Segmentierung/Sales-Pflege (D-017) |
| `FirstContactDate` | DATETIME | Erstkontaktdatum | verwerfen | Segmentierung/Sales-Pflege (D-017) |
| `GEOCODESTATUS` | STRING(64) | Georeferenzierungsstatus | Address (Sitz) | Adress-Metadaten (Prüfung/Geocoding) — offen |
| `Gifts` | STRING(80) | Geschenke | verwerfen | Legacy-Wildwuchs (D-017) |
| `gwAdditionalInfo1` | STRING(100) | Namenszusatz | Person · name_suffix | Namenszusatz (D-015) — prüfen |
| `gwAdditionalInfo2` | STRING(100) | Lieferort (Institution/Praxis) | Location | Lieferort (Institution/Praxis) (D-014) |
| `gwAdditionalInfo3` | STRING(100) | Zusatzinfo(Privat) | verwerfen | privat (D-014) |
| `gwBIC` | STRING(11) | BIC | Company · Bank | — |
| `gwBirthPlace` | STRING(30) | Geburtsort | verwerfen | Beziehungspflege/Personendetail — offen |
| `gwBranch` | STRING(60) | Briefanrede(F) | verwerfen | Anrede-Zeilen (D-015) |
| `GWCOMPANYLEGALFORM` | STRING(500) | Rechtliche Informationen | Company · Recht/Steuer | — |
| `gwCostCenter` | STRING(30) | Kostenstelle | → Identity/Employee | D-012 (vertagt) |
| `gwDeactivated` | BOOLEAN | Adresse deaktiviert | Company · archived_at | „Adresse deaktiviert" → Soft-Archiv |
| `gwDenomination` | STRING(50) | Konfession | verwerfen | Beziehungspflege/Personendetail — offen |
| `gwDepartment2` | STRING(30) | Not in Use 2 | verwerfen | Legacy-Schrott |
| `GWDISTRICTCOURT` | STRING(40) | Registerstandort | Company · Recht/Steuer | — |
| `GWEXTERNALADDRESSDATE` | DATETIME | Geprüft am | Address (Sitz) | Adress-Metadaten (Prüfung/Geocoding) — offen |
| `GWEXTERNALADDRESSNAME` | STRING(40) | Geprüft durch | Address (Sitz) | Adress-Metadaten (Prüfung/Geocoding) — offen |
| `gwFunction2` | STRING(100) | _not in Use | verwerfen | Legacy-Schrott |
| `GWGENDER` | STRING(30) | Geschlecht | Person · gender | D-015 |
| `GWHDOACCESSTYPE` | STRING(100) | Helpdesk online | → Portal | D-012 (vertagt) |
| `gwIBAN` | STRING(42) | IBAN | Company · Bank | — |
| `gwIsCompany` | BOOLEAN | ist Firma | → Identity/Employee | D-012 (vertagt) |
| `gwIsContact` | BOOLEAN | ist Kontaktperson | → Identity/Employee | D-012 (vertagt) |
| `gwIsEmployee` | BOOLEAN | ist Mitarbeiter | → Identity/Employee | D-012 (vertagt) |
| `GWISEXTERNALEMPLOYEE` | BOOLEAN | Externer Mitarbeiter | → Identity/Employee | D-012 (vertagt) |
| `gwKeepContactSynchron` | BOOLEAN | Feldwerte synchron | verwerfen | CAS-intern |
| `gwNationality` | STRING(50) | Staatsangehörigkeit | verwerfen | Beziehungspflege/Personendetail — offen |
| `gwPersonInCharge` | STRING(255) | Not in Use 3 | verwerfen | Legacy-Schrott |
| `gwPersonnelNumber` | STRING(30) | Personalnummer | → Identity/Employee | D-012 (vertagt) |
| `gwSalesActivityTypeAllowed` | STRING(100) | Erlaubte Kontaktarten | verwerfen | Kontaktart-Präferenz — offen (D-010?) |
| `gwSalesActivityTypePreferred` | STRING(20) | Bevorzugte Kontaktart | verwerfen | Kontaktart-Präferenz — offen (D-010?) |
| `GWSSERVICEPASSWORDSET` | BOOLEAN | Helpdesk online Kennwort vergeben | → Portal | D-012 (vertagt) |
| `GWSSTATUS` | STRING(80) | Status | → Portal | D-012 (vertagt) |
| `GWSTATE1` | STRING(30) | Staat/Region | Address (Sitz) | Sitzadresse (D-014) |
| `GWSTATE2` | STRING(30) | Staat/Region(Liefer) | Location | Lieferadresse → Location (D-014) |
| `GWSTATE3` | STRING(30) | Staat/Region(Privat) | verwerfen | Privatadresse (D-014) |
| `GWSTYPE` | STRING(80) | Typ | → Portal | D-012 (vertagt) |
| `GWTRADEREGISTER` | STRING(100) | Registernummer | Company · Recht/Steuer | — |
| `GWTRUSTEE` | STRING(40) | Verantwortlicher (alt) | verwerfen | Legacy-Wildwuchs (D-017) |
| `HDBLOCKEDFORSUPPORT` | BOOLEAN | Gesperrt für Support | → Portal | D-012 (vertagt) |
| `ImFieldStr1` | STRING(100) | Instant Messaging | contact_channels | D-010 |
| `ImFieldStr4` | STRING(100) | Skype | contact_channels | D-010 |
| `IMFIELDSTR6` | STRING(100) | Skype for Business | contact_channels | D-010 |
| `InHouseZip` | STRING(20) | Hauspostcode | Address (Sitz) | Sitzadresse (D-014) |
| `Interessean1` | STRING(30) | Interesse an 1 | verwerfen | Segmentierung/Sales-Pflege (D-017) |
| `Interessean2` | STRING(30) | Interesse an 2 | verwerfen | Segmentierung/Sales-Pflege (D-017) |
| `ITDANZAHLMA` | STRING(20) | Anzahl Mitarbeiter | verwerfen | Segmentierung/Sales-Pflege (D-017) |
| `ITDKLASSIFIZIERUNG` | STRING(20) | Klassifizierung | verwerfen | Segmentierung/Sales-Pflege (D-017) |
| `ITDLASTCONTACT` | DATETIME | Letzter Kontakt am | verwerfen | Segmentierung/Sales-Pflege (D-017) |
| `Kaufdatum` | STRING(30) | letztes Kaufdatum | verwerfen | Legacy-Wildwuchs (D-017) |
| `KSgemeldetan` | STRING(30) | KS gemeldet an: | verwerfen | Legacy-Wildwuchs (D-017) |
| `KSgewhrtan` | STRING(30) | generelle Bearbeitung | verwerfen | Legacy-Wildwuchs (D-017) |
| `LASTCONTACTMEDIUM` | STRING(200) | Letzter Kontakt über | verwerfen | Segmentierung/Sales-Pflege (D-017) |
| `LASTCONTACTUSER` | STRING(200) | Kontaktperson | verwerfen | Segmentierung/Sales-Pflege (D-017) |
| `LEASING` | STRING(50) | Leasing | offen · Bereich Service | — |
| `LeisureActivities` | STRING(40) | Vorlieben | verwerfen | Legacy-Wildwuchs (D-017) |
| `letzteAktion` | DATETIME | letzte Aktion | verwerfen | Segmentierung/Sales-Pflege (D-017) |
| `lostOrderan` | STRING(30) | _lost Order an | verwerfen | Legacy-Schrott |
| `MailFieldStr1` | STRING(100) | E-Mail - geschäftlich Arzt | contact_channels | D-010 |
| `MailFieldStr2` | STRING(100) | E-Mail - Rechnungsversand | contact_channels | D-010 |
| `MailFieldStr3` | STRING(100) | E-Mail - Privat | contact_channels | D-010 |
| `MailFieldStr4` | STRING(100) | not in Use 7 - war E-Mail (Privat 2) | verwerfen | Legacy-Schrott |
| `MailFieldStr5` | STRING(100) | E-Mail - Praxis | contact_channels | D-010 |
| `Mitarbeiter` | STRING(30) | Verantwortlicher Sales | Company · responsible_sales_id | D-016 |
| `Mobilausgeh` | STRING(30) | Mobil ausgehändigt | verwerfen | Geräte-Ausgabe → Service, hier weg |
| `Name` | STRING(30) | Name | Person · last_name | „Name" = Nachname (Personenzeile) |
| `Notes` | STRING(255) | Schlagworte | Company · notes | — |
| `NOTES2` | TEXT | Schlagworte | verwerfen | Dublette zu Notes — prüfen |
| `nurfrMEDICA` | STRING(30) | _altes Medica Feld - Schrott | verwerfen | Legacy-Schrott |
| `Payment` | STRING(30) | Zahlungsart | verwerfen | Segmentierung/Sales-Pflege (D-017) |
| `PhoneFieldStr1` | STRING(30) | Not in Use 4 - 1 - leer | verwerfen | Legacy-Schrott |
| `PhoneFieldStr10` | STRING(30) | Telefon -Zentrale- | contact_channels | D-010 |
| `PhoneFieldStr2` | STRING(30) | Mobil - persönlich - | contact_channels | D-010 |
| `PhoneFieldStr3` | STRING(30) | Not in Use / jetzt Telefon - mobil - | verwerfen | Legacy-Schrott |
| `PhoneFieldStr4` | STRING(30) | Telefon -persönliche Durchwahl- | contact_channels | D-010 |
| `PhoneFieldStr5` | STRING(30) | Mobil - privat - | contact_channels | D-010 |
| `PhoneFieldStr6` | STRING(30) | Mobil - persönlich Arzt - | contact_channels | D-010 |
| `PhoneFieldStr7` | STRING(30) | Telefon -Privat- | contact_channels | D-010 |
| `PhoneFieldStr8` | STRING(30) | Mobil - Zentrale Durchwahl - | contact_channels | D-010 |
| `PhoneFieldStr9` | STRING(30) | Telefon -Durchwahl- | contact_channels | D-010 |
| `PoBox1` | STRING(15) | Postfach | Address (Sitz) | Sitzadresse (D-014) |
| `PoBox2` | STRING(15) | Lieferung (Postfach) | Location | Lieferadresse → Location (D-014) |
| `PoBoxZip1` | STRING(15) | Postfach PLZ | Address (Sitz) | Sitzadresse (D-014) |
| `PoBoxZip2` | STRING(15) | Lieferung (Postfach PLZ) | Location | Lieferadresse → Location (D-014) |
| `POTOWN1` | STRING(30) | Postfach Ort | Address (Sitz) | Sitzadresse (D-014) |
| `POTOWN2` | STRING(30) | Lieferung (Postfach Ort) | Location | Lieferadresse → Location (D-014) |
| `PREFERREDLANGUAGE` | STRING(80) | Bevorzugte Sprache | Person · locale | Korrespondenz-/Portalsprache |
| `PREISANPASSUNG_2025` | BOOLEAN | Hinweis Preisanpaasung erfolgt | verwerfen | Segmentierung/Sales-Pflege (D-017) |
| `Quelle1` | INT | Quelle 1 | verwerfen | Segmentierung/Sales-Pflege (D-017) |
| `Quelle2` | INT | Quelle 2 | verwerfen | Segmentierung/Sales-Pflege (D-017) |
| `Rckkauf` | STRING(30) | Rückkauf | verwerfen | Legacy-Wildwuchs (D-017) |
| `Rebate` | DECIMAL | Rabatt | verwerfen | Segmentierung/Sales-Pflege (D-017) |
| `Referenzkunde` | DECIMAL | Referenzkunde löschen | verwerfen | Segmentierung/Sales-Pflege (D-017) |
| `RESPONSIBLE_SALES` | STRING(50) | Verantwortlicher (Sales) | Company · responsible_sales_id | D-016 |
| `RESPONSIBLE_SERVICE` | STRING(50) | Verantwortlicher (Service) | Company · responsible_service_id | D-016 |
| `SERVER_NAME` | STRING(32) | Server Name | → Device/Praxis-IT | D-001-Ausnahme (Service-Bereich) |
| `SERVER_WLAN_PASSWORD` | STRING(64) | Server WLAN Password | → Device/Praxis-IT | D-001-Ausnahme (Service-Bereich) |
| `SERVER_WLAN_SSID` | STRING(64) | Server WLAN SSID | → Device/Praxis-IT | D-001-Ausnahme (Service-Bereich) |
| `SONOGDT_MINDRAY` | STRING(24) | SonoGDT - Mindray | → Device/Praxis-IT | D-001-Ausnahme (Service-Bereich) |
| `SONOGDT_MINDRAY_MENGE` | INT | SonoGDT - Mindray (Anzahl) | → Device/Praxis-IT | D-001-Ausnahme (Service-Bereich) |
| `Street1` | STRING(45) | Straße | Address (Sitz) | Sitzadresse (D-014) |
| `Street2` | STRING(45) | Lieferung (Straße) | Location | Lieferadresse → Location (D-014) |
| `Street3` | STRING(45) | Straße (Privat) | verwerfen | Privatadresse (D-014) |
| `Suburb1` | STRING(30) | Teilort | Address (Sitz) | Sitzadresse (D-014) |
| `Suburb2` | STRING(30) | Lieferung (Teilort) | Location | Lieferadresse → Location (D-014) |
| `Suburb3` | STRING(30) | Teilort (Privat) | verwerfen | Privatadresse (D-014) |
| `SunlightStatus` | STRING(30) | Sunlight Status | verwerfen | Marketing-Integration (D-011) |
| `SVV2_VERTRAGS_INTERVALL` | STRING(11) | SVV2 Vertrags-Intervall | verwerfen | Toter Slot-Block (D-001) |
| `SVV3_VERTRAGS_INTERVALL` | STRING(50) | SVV3 Vertrags-Intervall | verwerfen | Toter Slot-Block (D-001) |
| `SVV4_VERTRAGS_INTERVALL` | STRING(50) | SVV4 Vertrags-Intervall | verwerfen | Toter Slot-Block (D-001) |
| `SVV_VERTRAGS_INTERVALL` | STRING(50) | SVV Vertrags-Intervall | verwerfen | Toter Slot-Block (D-001) |
| `TAXNUMBER` | STRING(30) | Steuernummer | Company · Recht/Steuer | — |
| `Title` | STRING(30) | Titel | Person · title | D-015 |
| `Town1` | STRING(30) | Ort | Address (Sitz) | Sitzadresse (D-014) |
| `Town2` | STRING(30) | Lieferung (Ort) | Location | Lieferadresse → Location (D-014) |
| `Town3` | STRING(30) | Ort (Privat) | verwerfen | Privatadresse (D-014) |
| `TurnOver` | DECIMAL | Umsatz | verwerfen | Segmentierung/Sales-Pflege (D-017) |
| `TurnOverGroup` | STRING(30) | Umsatzgruppe | verwerfen | Segmentierung/Sales-Pflege (D-017) |
| `TurnoverTaxId` | STRING(30) | Umsatzsteuer-ID | Company · Recht/Steuer | — |
| `USStatus` | STRING(30) | US-Status (alt/Schrott) | verwerfen | Legacy-Schrott |
| `VERSAKTIV` | BOOLEAN | Vers. aktiv | offen · Bereich Service | — |
| `VERSICHERUNG` | STRING(50) | Versicherung | offen · Bereich Service | — |
| `was` | STRING(30) | was | verwerfen | Legacy-Wildwuchs (D-017) |
| `wasgekauft` | STRING(100) | was gekauft | verwerfen | Legacy-Wildwuchs (D-017) |
| `WWWFieldStr1` | STRING(100) | Homepage | contact_channels | D-010 |
| `WWWFieldStr2` | STRING(100) | Link | contact_channels | D-010 |
| `WWWFieldStr3` | STRING(100) | Link 2 | contact_channels | D-010 |
| `WWWFieldStr4` | STRING(100) | Link 3 | contact_channels | D-010 |
| `WWWFieldStr5` | STRING(100) | Homepage privat | verwerfen | Homepage privat (D-014/15) |
| `Zip1` | STRING(10) | PLZ | Address (Sitz) | Sitzadresse (D-014) |
| `Zip2` | STRING(10) | Lieferung (PLZ) | Location | Lieferadresse → Location (D-014) |
| `Zip3` | STRING(10) | PLZ (Privat) | verwerfen | Privatadresse (D-014) |
