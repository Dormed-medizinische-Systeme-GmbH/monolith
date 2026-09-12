# ServiceContract / Device — Feld-Zuordnung

Legacy [`Servicevertraege-NEU.xml`](Servicevertraege-NEU.xml) · 83 Felder. Basis: D-034 – D-045.
Synthese → `.docs/04-domain/SERVICE.md`.

| Feld | Typ | Label | Ziel | Notiz |
| --- | --- | --- | --- | --- |
| `ABWEICHENDE_RECHNUNG` | STRING(6) | Abweichende Rechnung | ServiceContract · billing_recipient? | → Rechnungsempfänger (D-004) — prüfen |
| `DEBITORENNUMMER` | STRING(50) | DebitorenNummer | verwerfen | → Company.debitor_number (D-009) |
| `ERFUELLUNGSORT_UEBERTRAG` | STRING(64) | ERFUELLUNGSORT_ÜBERTRAG | verwerfen | Adress-Dublette → Company/Location (D-034) |
| `ERSTEWARTUNG` | DATETIME | letzte Wartung | verwerfen | abgeleitet aus Maintenance (D-037/D-042) |
| `FIRMA` | STRING(100) | Firma | verwerfen | Adress-Dublette → Company/Location (D-034) |
| `FIRMA2` | STRING(100) | Firma2 | verwerfen | Adress-Dublette → Company/Location (D-034) |
| `GARANTIE_HERSTELLER` | DATETIME | Herstellergarantie | ServiceContract · warranty_* | Herstellergarantie/Kundengarantie |
| `GARANTIE_KUNDE` | DATETIME | Kundengarantie bis (SV) | ServiceContract · warranty_* | Herstellergarantie/Kundengarantie |
| `GWAUTONUM` | STRING(200) | Nummer | ServiceContract · number | Nummernkreis |
| `KEYWORD` | STRING(128) | Stichwort | ServiceContract · notes | Schlagworte |
| `KOSTEN_FAHRTZONE_EINMAL` | DECIMAL | Fahrtzeugpauschale (einmal) | verwerfen | nur current (D-036/D-020) |
| `KOSTEN_FAHRTZONE_KHK` | DECIMAL | Fahrzeugpauschale (Aktuell) | ServiceContract · travel_flat_rate | nur current (D-036/D-020) |
| `KOSTEN_FAHRTZONE_VERTRAG` | DECIMAL | Fahrzeugpauschale (Ursprung) | verwerfen | nur current (D-036/D-020) |
| `KOSTEN_WARTUNG_EINMAL` | DECIMAL | Wartungkosten (einmal) | verwerfen | nur current (D-036) |
| `KOSTEN_WARTUNG_KHK` | DECIMAL | Wartungskosten (Aktuell) | ServiceContract · maintenance_price | nur current (D-036) |
| `KOSTEN_WARTUNG_VERTRAG` | DECIMAL | Wartungskosten (Ursprung) | verwerfen | nur current (D-036) |
| `MEHRFACHWARTUNG` | BOOLEAN | Mehrfachwartung | verwerfen | abgeleitet aus Maintenance (D-037/D-042) |
| `MONAT` | STRING(32) | MONAT | verwerfen | abgeleitet aus Maintenance (D-037/D-042) |
| `NAECHSTEWARTUNG` | DATETIME | nächste Wartung | verwerfen | abgeleitet aus Maintenance (D-037/D-042) |
| `NOTES2` | TEXT | Schlagworte | ServiceContract · notes | Schlagworte |
| `ORT` | STRING(50) | Ort | verwerfen | Adress-Dublette → Company/Location (D-034) |
| `PLZ` | STRING(5) | PLZ | verwerfen | Adress-Dublette → Company/Location (D-034) |
| `PREISANPASSUNG` | BOOLEAN | Preisanpassung gemäß Wartungsbericht? | verwerfen | D-042 |
| `PREISANPASSUNG2025` | DATETIME | Preisanpassung gem. WB angekündigt | verwerfen | D-042 |
| `PRINTER_ARTIKELNUMMER` | STRING(32) | Printer Artikelnummer | DeviceComponent | typisiert (Sonde/Printer/Wagen/GDT), n je Device |
| `PRINTER_BEZEICHNUNG` | STRING(32) | Printer Bezeichnung | DeviceComponent | typisiert (Sonde/Printer/Wagen/GDT), n je Device |
| `PRINTER_SERIENNUMMER` | STRING(32) | Printer Seriennummer | DeviceComponent | typisiert (Sonde/Printer/Wagen/GDT), n je Device |
| `SONDE1_ARTIKELNUMMER` | STRING(32) | Sonde 1 Artikelnummer | DeviceComponent | typisiert (Sonde/Printer/Wagen/GDT), n je Device |
| `SONDE1_BEZEICHNUNG` | STRING(64) | Sonde 1 Bezeichnung | DeviceComponent | typisiert (Sonde/Printer/Wagen/GDT), n je Device |
| `SONDE1_SERIENNUMMER` | STRING(32) | Sonde 1 Seriennummer | DeviceComponent | typisiert (Sonde/Printer/Wagen/GDT), n je Device |
| `SONDE2_ARTIKELNUMMER` | STRING(32) | Sonde 2 Artikelnummer | DeviceComponent | typisiert (Sonde/Printer/Wagen/GDT), n je Device |
| `SONDE2_BEZEICHNUNG` | STRING(64) | Sonde 2 Bezeichnung | DeviceComponent | typisiert (Sonde/Printer/Wagen/GDT), n je Device |
| `SONDE2_SERIENNUMMER` | STRING(32) | Sonde 2 Seriennummer | DeviceComponent | typisiert (Sonde/Printer/Wagen/GDT), n je Device |
| `SONDE3_ARTIKELNUMMER` | STRING(32) | Sonde 3 Artikelnummer | DeviceComponent | typisiert (Sonde/Printer/Wagen/GDT), n je Device |
| `SONDE3_BEZEICHNUNG` | STRING(64) | Sonde 3 Bezeichnung | DeviceComponent | typisiert (Sonde/Printer/Wagen/GDT), n je Device |
| `SONDE3_SERIENNUMMER` | STRING(32) | Sonde 3 Seriennummer | DeviceComponent | typisiert (Sonde/Printer/Wagen/GDT), n je Device |
| `SONDE4_ARTIKELNUMMER` | STRING(32) | Sonde 4 Artikelnummer | DeviceComponent | typisiert (Sonde/Printer/Wagen/GDT), n je Device |
| `SONDE4_BEZEICHNUNG` | STRING(64) | Sonde 4 Bezeichnung | DeviceComponent | typisiert (Sonde/Printer/Wagen/GDT), n je Device |
| `SONDE4_SERIENNUMMER` | STRING(32) | Sonde 4 Seriennummer | DeviceComponent | typisiert (Sonde/Printer/Wagen/GDT), n je Device |
| `SONDE5_ARTIKELNUMMER` | STRING(32) | Sonde 5 Artikelnummer | DeviceComponent | typisiert (Sonde/Printer/Wagen/GDT), n je Device |
| `SONDE5_BEZEICHNUNG` | STRING(64) | Sonde 5 Bezeichnung | DeviceComponent | typisiert (Sonde/Printer/Wagen/GDT), n je Device |
| `SONDE5_SERIENNUMMER` | STRING(32) | Sonde 5 Seriennummer | DeviceComponent | typisiert (Sonde/Printer/Wagen/GDT), n je Device |
| `SONOGDT_ARTIKELNUMMER` | STRING(50) | SONOGDT Artikelnummer (KHK) | DeviceComponent | typisiert (Sonde/Printer/Wagen/GDT), n je Device |
| `SONOGDT_BEZEICHNUNG` | STRING(50) | SONOGDT Bezeichnung | DeviceComponent | typisiert (Sonde/Printer/Wagen/GDT), n je Device |
| `SONOGDT_LIZENZ` | STRING(32) | SONOGDT Lizenz | DeviceComponent | typisiert (Sonde/Printer/Wagen/GDT), n je Device |
| `SYSTEM_ARTIKELNUMMER` | STRING(32) | System Artikelnummer | Device | System-Stammdaten |
| `SYSTEM_AUSLIEFERUNGSDATUM` | DATE | Auslieferungsdatum | Device | System-Stammdaten |
| `SYSTEM_BAUJAHR` | STRING(8) | Baujahr | Device | System-Stammdaten |
| `SYSTEM_BENUTZER` | STRING(32) | System Benutzer | Device | System-Stammdaten |
| `SYSTEM_BEZEICHNUNG` | STRING(64) | System Kategorie (Wartung) | Device | System-Stammdaten |
| `SYSTEM_DHCP` | BOOLEAN | DHCP | Device · Netzwerk/DICOM | Feld auf Device (D-034) |
| `SYSTEM_GATEWAY` | STRING(32) | System Gateway | Device · Netzwerk/DICOM | Feld auf Device (D-034) |
| `SYSTEM_HERSTELLER` | STRING(64) | System Hersteller | Device | System-Stammdaten |
| `SYSTEM_IPADRESSE` | STRING(30) | System IP Adresse | Device · Netzwerk/DICOM | Feld auf Device (D-034) |
| `SYSTEM_MACADRESSE` | STRING(17) | System MAC Adresse | Device · Netzwerk/DICOM | Feld auf Device (D-034) |
| `SYSTEM_OPTIONEN` | STRING(255) | System Optionen | Device | System-Stammdaten |
| `SYSTEM_OS` | STRING(16) | System OS | Device | System-Stammdaten |
| `SYSTEM_PASSWORD` | STRING(32) | System Passwort | Device · Netzwerk/DICOM | Feld auf Device (D-034) |
| `SYSTEM_SERIENNUMMER` | STRING(32) | System Seriennummer | Device | System-Stammdaten |
| `SYSTEM_STORAGE_PORT` | INT | Storage (Port) | Device · Netzwerk/DICOM | Feld auf Device (D-034) |
| `SYSTEM_STORAGE_PORT_NEW` | STRING(10) | Storage (Port)_ | Device · Netzwerk/DICOM | Feld auf Device (D-034) |
| `SYSTEM_STORAGE_TITLE` | STRING(16) | Storage (Title) | Device · Netzwerk/DICOM | Feld auf Device (D-034) |
| `SYSTEM_SW` | STRING(16) | System Software | Device | System-Stammdaten |
| `SYSTEM_WORKLIST_PORT_NEW` | STRING(10) | Worklist (Port)_ | Device · Netzwerk/DICOM | Feld auf Device (D-034) |
| `SYSTEM_WORKLISTE_PORT` | INT | Worklist (Port) | Device · Netzwerk/DICOM | Feld auf Device (D-034) |
| `SYSTEM_WORKLISTE_TITLE` | STRING(16) | Worklist (Title) | Device · Netzwerk/DICOM | Feld auf Device (D-034) |
| `VERANTWORTLICHER_SERVICE` | STRING(64) | Verantwortlicher | verwerfen | → Company.responsible_service_id (D-016) |
| `VERTAG_GARANTIEVERSICHERUNG` | BOOLEAN | Garantieversicherung | ServiceContract · warranty_* | Herstellergarantie/Kundengarantie |
| `VERTRAG_DATUM_KUENDIGUNG` | DATETIME | Kündigungsdatum | ServiceContract · cancelled_at |  |
| `VERTRAG_ELEKTORNIKVERSICHERUNG` | BOOLEAN | Elektronikversicherung | ServiceContract · electronics_insurance* | bool + wo |
| `VERTRAG_ELEKTRONIKVERS_WO` | STRING(32) | Versicherung Wo? | ServiceContract · electronics_insurance* | bool + wo |
| `VERTRAG_FS_ENDE` | DATETIME | Full-Service Ende | ServiceContract · full_service_ends_at |  |
| `VERTRAG_GARANTIEVERS_BIS` | DATE | Garantieversicherung bis | ServiceContract · warranty_* | Herstellergarantie/Kundengarantie |
| `VERTRAG_LEASING` | BOOLEAN | Leasingvertrag | ServiceContract · leasing* | bool + wo |
| `VERTRAG_LEASING_WO` | STRING(32) | Leasing Wo? | ServiceContract · leasing* | bool + wo |
| `VERTRAG_ZAHLUNGSKONDITIONEN` | STRING(32) | Zahlungskonditionen | ServiceContract · payment_terms |  |
| `VERTRAGS_ART` | STRING(32) | Vertragsart | ServiceContract · contract_type | Enum — Werte offen |
| `VERTRAGS_DATUM` | DATETIME | Vertragsdatum | ServiceContract · signed_on |  |
| `VERTRAGS_INTERVALL` | STRING(16) | Intervall | ServiceContract · maintenance_interval_months | D-037 |
| `VERTRAGS_STATUS` | STRING(16) | Vertragsstatus | ServiceContract · status | Enum (aktiv/gekündigt/…) — Werte offen |
| `WAGEN_ARTIKELNUMMER` | STRING(32) | Wagen Artikelnummer | DeviceComponent | typisiert (Sonde/Printer/Wagen/GDT), n je Device |
| `WAGEN_BEZEICHNUNG` | STRING(32) | Wagen Bezeichnung | DeviceComponent | typisiert (Sonde/Printer/Wagen/GDT), n je Device |
| `WAGEN_SERIENNUMMER` | STRING(32) | Wagen Seriennummer | DeviceComponent | typisiert (Sonde/Printer/Wagen/GDT), n je Device |
