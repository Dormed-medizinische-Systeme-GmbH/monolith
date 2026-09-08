# Maintenance / ServiceCase — Feld-Zuordnung

Legacy [`Tickets.xml`](Tickets.xml) · 112 Felder. Basis: D-034 – D-045.
Synthese → `docs/04-domain/SERVICE.md`.

| Feld | Typ | Label | Ziel | Notiz |
| --- | --- | --- | --- | --- |
| `ATMOSPHERE` | STRING(32) | Atmosphäre | verwerfen | Stimmung Kunde (D-045) |
| `BESTAETIGUNG` | BOOLEAN | BESTAETIGUNG | Signatur-Gate | D-045 |
| `GWAUTONUM` | STRING(200) | Nummer | ServiceCase/Maintenance · number | Nummernkreis |
| `GWSSTATUS` | STRING(80) | Status | ServiceCase/Maintenance · status/type | Enum — Werte offen (Kollision mit HDO, s. D-012) |
| `GWSTYPE` | STRING(80) | Typ | ServiceCase/Maintenance · status/type | Enum — Werte offen (Kollision mit HDO, s. D-012) |
| `INTNOTES` | TEXT | Interne Notizen | ServiceCase/Maintenance · notes |  |
| `ISOFFLINE` | BOOLEAN | Offline Wartungsbericht erstellt | verwerfen | Offline = späterer Slice (D-041) |
| `ISOFFLINE_BEARBEITUNG` | BOOLEAN | Offline Bearbeitung erforderlich | verwerfen | Offline = späterer Slice (D-041) |
| `KEYWORD` | STRING(128) | Stichwort | ServiceCase/Maintenance · notes |  |
| `KULANZ` | BOOLEAN | Kulanz | ServiceCase · goodwill | bool |
| `LEIHGERAET` | BOOLEAN | Leihgerät erforderlich | ServiceCase · loan_device_required | bool |
| `MANUELLERMELDER` | STRING(32) | MANUELLERMELDER | verwerfen | → ServiceCase.reported_by = CompanyContact (CORE.md) |
| `MEHRFACHWARTUNG` | BOOLEAN | Mehrfachwartung | verwerfen | D-042 |
| `NOTES2` | TEXT | Schlagworte | ServiceCase/Maintenance · notes |  |
| `TICKET_ABSCHLUSS_1` | BOOLEAN | Keine Mängel festgestellt | MaintenanceReport · outcome | Mängelgrad / Abschluss (D-038) |
| `TICKET_ABSCHLUSS_2` | BOOLEAN | Mängel festgestellt | MaintenanceReport · outcome | Mängelgrad / Abschluss (D-038) |
| `TICKET_ABSCHLUSS_3` | BOOLEAN | Mängel mit Gefährdung | MaintenanceReport · outcome | Mängelgrad / Abschluss (D-038) |
| `TICKET_ABSCHLUSS_4` | BOOLEAN | Mängel Ausserbetriebnahme | MaintenanceReport · outcome | Mängelgrad / Abschluss (D-038) |
| `TICKET_ABSCHLUSS_KP` | BOOLEAN | Konstanzprüfung für KV | MeasurementProtocol · is_constancy_test | Mängelgrad / Abschluss (D-038) |
| `TICKET_ABSCHLUSS_MAENGEL` | TEXT | Mängel & Bemerkungen | MaintenanceReport · outcome | Mängelgrad / Abschluss (D-038) |
| `TICKET_DATUM_ABGERECHNET` | DATETIME | TICKET_DATUM_ABGERECHNET | verwerfen | → Invoice (Billing, D-043) |
| `TICKET_DATUM_GESCHLOSSEN` | DATETIME | TICKET_DATUM_GESCHLOSSEN | Maintenance/ServiceCase · finalized_at |  |
| `TICKET_DURCHGEFUEHRTEARBEITEN` | TEXT | Durchgeführte Arbeiten | Maintenance/ServiceCase · work_performed | Text |
| `TICKET_FUNKTIONSKONTROLLE_1` | BOOLEAN | Systemstart einwandfrei ohne Fehlermeld | ChecklistTemplate-Item / MaintenanceReport-Ergebnis | D-038 |
| `TICKET_FUNKTIONSKONTROLLE_10` | BOOLEAN | M-Mode Abläufe zeitlich korrekt | ChecklistTemplate-Item / MaintenanceReport-Ergebnis | D-038 |
| `TICKET_FUNKTIONSKONTROLLE_11` | BOOLEAN | EKG-Ableitung störungsfrei | ChecklistTemplate-Item / MaintenanceReport-Ergebnis | D-038 |
| `TICKET_FUNKTIONSKONTROLLE_12` | BOOLEAN | Drucker (wenn vorhanden) fehlerfrei | ChecklistTemplate-Item / MaintenanceReport-Ergebnis | D-038 |
| `TICKET_FUNKTIONSKONTROLLE_13` | BOOLEAN | Netzwerk stabil / keine Verzögerungen | ChecklistTemplate-Item / MaintenanceReport-Ergebnis | D-038 |
| `TICKET_FUNKTIONSKONTROLLE_2` | BOOLEAN | Bed.-und Prüfprot. vollständig/aktuell | ChecklistTemplate-Item / MaintenanceReport-Ergebnis | D-038 |
| `TICKET_FUNKTIONSKONTROLLE_3` | BOOLEAN | eingest. Eindringtiefe gem. Spezifikatio | ChecklistTemplate-Item / MaintenanceReport-Ergebnis | D-038 |
| `TICKET_FUNKTIONSKONTROLLE_4` | BOOLEAN | Werte Meßfunktion korrekt u. zuverlässig | ChecklistTemplate-Item / MaintenanceReport-Ergebnis | D-038 |
| `TICKET_FUNKTIONSKONTROLLE_5` | BOOLEAN | Kristalle arbeiten störungsfrei | ChecklistTemplate-Item / MaintenanceReport-Ergebnis | D-038 |
| `TICKET_FUNKTIONSKONTROLLE_6` | BOOLEAN | Farbdoppler sauber und störungsfrei | ChecklistTemplate-Item / MaintenanceReport-Ergebnis | D-038 |
| `TICKET_FUNKTIONSKONTROLLE_7` | BOOLEAN | PW Doppler-Messung präzise Signale | ChecklistTemplate-Item / MaintenanceReport-Ergebnis | D-038 |
| `TICKET_FUNKTIONSKONTROLLE_8` | BOOLEAN | CW Doppler kontinuierlich und genau | ChecklistTemplate-Item / MaintenanceReport-Ergebnis | D-038 |
| `TICKET_FUNKTIONSKONTROLLE_9` | BOOLEAN | 3D/4D Bildgebung hat klare Darstellung | ChecklistTemplate-Item / MaintenanceReport-Ergebnis | D-038 |
| `TICKET_GESAMT_BRUTTO` | DECIMAL | Gesamtpreis brutto | verwerfen/abgeleitet | Summen → Billing (D-043) |
| `TICKET_GESAMT_NETTO` | DECIMAL | Gesamtpreis netto | verwerfen/abgeleitet | Summen → Billing (D-043) |
| `TICKET_GWSFEHLERURSACHE` | TEXT | Fehlerursache | ServiceCase · fault_cause | Text |
| `TICKET_LEISTUNG_POS1_BEZ` | STRING(50) | Leistung Position 1 Bezeichnung | line_items | D-043 |
| `TICKET_LEISTUNG_POS1_EPREIS` | CURRENCY | Leistung Position 1 Einzelpreis | line_items | D-043 |
| `TICKET_LEISTUNG_POS1_MENGE` | DECIMAL | Leistung Position 1 Menge | line_items | D-043 |
| `TICKET_MAIL` | STRING(64) | Mail | verwerfen | → contact_channels (D-010) |
| `TICKET_MESSWERTE_ART` | STRING(64) | Messverfahren | MeasurementProtocol | D-044 |
| `TICKET_MESSWERTE_PRUEFMITTEL` | STRING(128) | Prüfmittel | MeasurementProtocol | D-044 |
| `TICKET_MESSWERTE_SCHUTZKLASSE` | STRING(32) | Schutzklasse | MeasurementProtocol | D-044 |
| `TICKET_MESSWERTE_SK1_IEGA` | DECIMAL | Schutzklasse 1 IEGA | MeasurementProtocol | D-044 |
| `TICKET_MESSWERTE_SK1_IEPA` | DECIMAL | Schutzklasse 1 IEPA | MeasurementProtocol | D-044 |
| `TICKET_MESSWERTE_SK1_IGA` | DECIMAL | Schutzklasse 1 IGA | MeasurementProtocol | D-044 |
| `TICKET_MESSWERTE_SK1_IPA` | DECIMAL | Schutzklasse 1 IPA | MeasurementProtocol | D-044 |
| `TICKET_MESSWERTE_SK1_RSL` | DECIMAL | Schutzklasse 1 RSL | MeasurementProtocol | D-044 |
| `TICKET_MESSWERTE_SK1_ULN` | DECIMAL | Schutzklasse 1 ULN | MeasurementProtocol | D-044 |
| `TICKET_MESSWERTE_SK2_IEGA` | DECIMAL | Schutzklasse 2 IEGA | MeasurementProtocol | D-044 |
| `TICKET_MESSWERTE_SK2_IEPA` | DECIMAL | Schutzklasse 2 IEPA | MeasurementProtocol | D-044 |
| `TICKET_MESSWERTE_SK2_IGA` | DECIMAL | Schutzklasse 2 IGA | MeasurementProtocol | D-044 |
| `TICKET_MESSWERTE_SK2_IPA` | DECIMAL | Schutzklasse 2 IPA | MeasurementProtocol | D-044 |
| `TICKET_MESSWERTE_SK2_RSL` | DECIMAL | Schutzklasse 2 RSL | MeasurementProtocol | D-044 |
| `TICKET_MESSWERTE_SK2_ULN` | DECIMAL | Schutzklasse 2 ULN | MeasurementProtocol | D-044 |
| `TICKET_MWST` | DECIMAL | 19 % Mwst. | verwerfen/abgeleitet | Summen → Billing (D-043) |
| `TICKET_POS1_BEZEICHNUNG` | STRING(100) | Pos. 1 Bezeichnung | line_items | D-043 |
| `TICKET_POS1_EP` | DECIMAL | Pos. 1 EP | line_items | D-043 |
| `TICKET_POS1_GP` | DECIMAL | Pos. 1 GP | line_items | D-043 |
| `TICKET_POS1_MENGE` | DECIMAL | Pos. 1 Menge | line_items | D-043 |
| `TICKET_POS2_BEZEICHNUNG` | STRING(100) | Pos. 2 Bezeichnung | line_items | D-043 |
| `TICKET_POS2_EP` | DECIMAL | Pos. 2 EP | line_items | D-043 |
| `TICKET_POS2_GP` | DECIMAL | Pos. 2 GP | line_items | D-043 |
| `TICKET_POS2_MENGE` | DECIMAL | Pos. 2 Menge | line_items | D-043 |
| `TICKET_POS3_BEZEICHNUNG` | STRING(100) | Pos. 3 Bezeichnung | line_items | D-043 |
| `TICKET_POS3_EP` | DECIMAL | Pos. 3 EP | line_items | D-043 |
| `TICKET_POS3_GP` | DECIMAL | Pos. 3 GP | line_items | D-043 |
| `TICKET_POS3_MENGE` | DECIMAL | Pos. 3 Menge | line_items | D-043 |
| `TICKET_POS4_BEZEICHNUNG` | STRING(100) | Pos. 4 Bezeichnung | line_items | D-043 |
| `TICKET_POS4_EP` | DECIMAL | Pos. 4 EP | line_items | D-043 |
| `TICKET_POS4_GP` | DECIMAL | Pos. 4 GP | line_items | D-043 |
| `TICKET_POS4_MENGE` | DECIMAL | Pos. 4 Menge | line_items | D-043 |
| `TICKET_POS5_BEZEICHNUNG` | STRING(100) | Pos. 5 Bezeichnung | line_items | D-043 |
| `TICKET_POS5_EP` | DECIMAL | Pos. 5 EP | line_items | D-043 |
| `TICKET_POS5_GP` | DECIMAL | Pos. 5 GP | line_items | D-043 |
| `TICKET_POS5_MENGE` | DECIMAL | Pos. 5 Menge | line_items | D-043 |
| `TICKET_POS6_BEZEICHNUNG` | STRING(100) | Pos. 6 Bezeichnung | line_items | D-043 |
| `TICKET_POS6_EP` | DECIMAL | Pos. 6 EP | line_items | D-043 |
| `TICKET_POS6_GP` | DECIMAL | Pos. 6 GP | line_items | D-043 |
| `TICKET_POS6_MENGE` | DECIMAL | Pos. 6 Menge | line_items | D-043 |
| `TICKET_POS7_BEZEICHNUNG` | STRING(100) | Pos. 7 Bezeichnung | line_items | D-043 |
| `TICKET_POS7_EP` | DECIMAL | Pos. 7 EP | line_items | D-043 |
| `TICKET_POS7_GP` | DECIMAL | Pos. 7 GP | line_items | D-043 |
| `TICKET_POS7_MENGE` | DECIMAL | Pos. 7 Menge | line_items | D-043 |
| `TICKET_RUECKRUFNUMMER` | STRING(32) | Rufnummer | verwerfen | → contact_channels (D-010) |
| `TICKET_SICHTKONTROLLE_1` | BOOLEAN | frei von Verschmutzungen und Rückständen | ChecklistTemplate-Item / MaintenanceReport-Ergebnis | D-038 |
| `TICKET_SICHTKONTROLLE_10` | BOOLEAN | Akustische Linsen sauber unbeschädigt | ChecklistTemplate-Item / MaintenanceReport-Ergebnis | D-038 |
| `TICKET_SICHTKONTROLLE_2` | BOOLEAN | Aufschriften / Beschriftungen lesbar | ChecklistTemplate-Item / MaintenanceReport-Ergebnis | D-038 |
| `TICKET_SICHTKONTROLLE_3` | BOOLEAN | Typenschild vorhanden und erkennbar | ChecklistTemplate-Item / MaintenanceReport-Ergebnis | D-038 |
| `TICKET_SICHTKONTROLLE_4` | BOOLEAN | Netzwerkkabel unbeschädigt (eingesteckt) | ChecklistTemplate-Item / MaintenanceReport-Ergebnis | D-038 |
| `TICKET_SICHTKONTROLLE_5` | BOOLEAN | Monitorbild klar und artefaktfrei | ChecklistTemplate-Item / MaintenanceReport-Ergebnis | D-038 |
| `TICKET_SICHTKONTROLLE_6` | BOOLEAN | Buchsen/Steckverbindungen sauber und fes | ChecklistTemplate-Item / MaintenanceReport-Ergebnis | D-038 |
| `TICKET_SICHTKONTROLLE_7` | BOOLEAN | Keine mech. Beschädigungen/Verformungen | ChecklistTemplate-Item / MaintenanceReport-Ergebnis | D-038 |
| `TICKET_SICHTKONTROLLE_8` | BOOLEAN | Sondenkabel/Knickschutz intakt und flexi | ChecklistTemplate-Item / MaintenanceReport-Ergebnis | D-038 |
| `TICKET_SICHTKONTROLLE_9` | BOOLEAN | Gerätegehäuse frei von Rissen/Dellen | ChecklistTemplate-Item / MaintenanceReport-Ergebnis | D-038 |
| `TICKET_SYSTEM` | STRING(50) | System | verwerfen | → Device-Relation |
| `TICKET_TECHNIKERDIAGNOSE` | TEXT | Diagnose Techniker | ServiceCase · technician_diagnosis | Text |
| `TICKET_TICKETESCALATIONSTIME1` | DATETIME | Eskalationszeitpunkt | ServiceCase · escalated_at |  |
| `TICKET_TICKETUSERNAME` | STRING(50) | Verantwortlicher | Maintenance/ServiceCase · assigned_technician_id | → users |
| `TICKET_WARTUNGSARBEITEN_1` | BOOLEAN | Gerät geöffnet und alle Bestandteile ger | ChecklistTemplate-Item / MaintenanceReport-Ergebnis | D-038 |
| `TICKET_WARTUNGSARBEITEN_2` | BOOLEAN | Lüftungsschlitze Monitor gereinigt | ChecklistTemplate-Item / MaintenanceReport-Ergebnis | D-038 |
| `TICKET_WARTUNGSARBEITEN_3` | BOOLEAN | Tastatur und Trackball gereinigt | ChecklistTemplate-Item / MaintenanceReport-Ergebnis | D-038 |
| `TICKET_WARTUNGSARBEITEN_4` | BOOLEAN | Drucker gereinigt / Ausdruck geprüft | ChecklistTemplate-Item / MaintenanceReport-Ergebnis | D-038 |
| `TICKET_WARTUNGSARBEITEN_5` | BOOLEAN | Sondenleistung am Phantom/Körper geprüft | ChecklistTemplate-Item / MaintenanceReport-Ergebnis | D-038 |
| `TICKET_WARTUNGSARBEITEN_6` | BOOLEAN | Meßfunktionen und Programme geprüft | ChecklistTemplate-Item / MaintenanceReport-Ergebnis | D-038 |
| `TICKET_WARTUNGSARBEITEN_7` | BOOLEAN | STK & Funktion gemäß DIN EN 62353 | ChecklistTemplate-Item / MaintenanceReport-Ergebnis | D-038 |
| `TICKET_WARTUNGSARBEITEN_8` | BOOLEAN | Dokumentation (STK-Protokoll) für KV | ChecklistTemplate-Item / MaintenanceReport-Ergebnis | D-038 |
| `TICKET_ZUSATZINFORMATIONEN` | STRING(255) | TICKET_ZUSATZINFORMATIONEN | ServiceCase/Maintenance · notes |  |
| `WARTEAURUECKMELDUNG` | BOOLEAN | Warte auf Rückmeldung vom Kunden | ServiceCase · status=waiting_customer | als Status statt Flag |
