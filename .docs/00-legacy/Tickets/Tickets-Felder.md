# Legacy-Felder — TICKETS (Tickets)

Quelle: [`Tickets.xml`](Tickets.xml) · 112 Spalten · 112 Custom · 4 Pflicht

**Ist-Analyse (ADR-004).** `Entscheidung` wird in `/grill-me` gesetzt: `übernehmen` (in welches Zielmodell/-feld) · `verwerfen` · `offen`.

## Cluster nach Namenspräfix

| Präfix | Felder |
| --- | --- |
| `TICKET_*` | 97 |
| `(kein Präfix)` | 12 |
| `GW…` | 3 |

## Typverteilung

| Typ | Anzahl |
| --- | --- |
| BOOLEAN | 43 |
| DECIMAL | 37 |
| STRING | 22 |
| TEXT | 6 |
| DATETIME | 3 |
| CURRENCY | 1 |

## Felder

| Feld | Typ | Label (de) | Custom | Pflicht | Entscheidung |
| --- | --- | --- | :-: | :-: | --- |
| `ATMOSPHERE` | STRING(32) | Atmosphäre | ✓ |  | offen |
| `BESTAETIGUNG` | BOOLEAN | BESTAETIGUNG | ✓ |  | offen |
| `GWAUTONUM` | STRING(200) | Nummer | ✓ |  | offen |
| `GWSSTATUS` | STRING(80) | Status | ✓ | ✓ | offen |
| `GWSTYPE` | STRING(80) | Typ | ✓ | ✓ | offen |
| `INTNOTES` | TEXT | Interne Notizen | ✓ |  | offen |
| `ISOFFLINE` | BOOLEAN | Offline Wartungsbericht erstellt | ✓ |  | offen |
| `ISOFFLINE_BEARBEITUNG` | BOOLEAN | Offline Bearbeitung erforderlich | ✓ |  | offen |
| `KEYWORD` | STRING(128) | Stichwort | ✓ |  | offen |
| `KULANZ` | BOOLEAN | Kulanz | ✓ |  | offen |
| `LEIHGERAET` | BOOLEAN | Leihgerät erforderlich | ✓ |  | offen |
| `MANUELLERMELDER` | STRING(32) | MANUELLERMELDER | ✓ |  | offen |
| `MEHRFACHWARTUNG` | BOOLEAN | Mehrfachwartung | ✓ |  | offen |
| `NOTES2` | TEXT | Schlagworte | ✓ |  | offen |
| `TICKET_ABSCHLUSS_1` | BOOLEAN | Keine Mängel festgestellt | ✓ |  | offen |
| `TICKET_ABSCHLUSS_2` | BOOLEAN | Mängel festgestellt | ✓ |  | offen |
| `TICKET_ABSCHLUSS_3` | BOOLEAN | Mängel mit Gefährdung | ✓ |  | offen |
| `TICKET_ABSCHLUSS_4` | BOOLEAN | Mängel Ausserbetriebnahme | ✓ |  | offen |
| `TICKET_ABSCHLUSS_KP` | BOOLEAN | Konstanzprüfung für KV | ✓ |  | offen |
| `TICKET_ABSCHLUSS_MAENGEL` | TEXT | Mängel & Bemerkungen | ✓ |  | offen |
| `TICKET_DATUM_ABGERECHNET` | DATETIME | TICKET_DATUM_ABGERECHNET | ✓ |  | offen |
| `TICKET_DATUM_GESCHLOSSEN` | DATETIME | TICKET_DATUM_GESCHLOSSEN | ✓ |  | offen |
| `TICKET_DURCHGEFUEHRTEARBEITEN` | TEXT | Durchgeführte Arbeiten | ✓ |  | offen |
| `TICKET_FUNKTIONSKONTROLLE_1` | BOOLEAN | Systemstart einwandfrei ohne Fehlermeld | ✓ |  | offen |
| `TICKET_FUNKTIONSKONTROLLE_10` | BOOLEAN | M-Mode Abläufe zeitlich korrekt | ✓ |  | offen |
| `TICKET_FUNKTIONSKONTROLLE_11` | BOOLEAN | EKG-Ableitung störungsfrei | ✓ |  | offen |
| `TICKET_FUNKTIONSKONTROLLE_12` | BOOLEAN | Drucker (wenn vorhanden) fehlerfrei | ✓ |  | offen |
| `TICKET_FUNKTIONSKONTROLLE_13` | BOOLEAN | Netzwerk stabil / keine Verzögerungen | ✓ |  | offen |
| `TICKET_FUNKTIONSKONTROLLE_2` | BOOLEAN | Bed.-und Prüfprot. vollständig/aktuell | ✓ |  | offen |
| `TICKET_FUNKTIONSKONTROLLE_3` | BOOLEAN | eingest. Eindringtiefe gem. Spezifikatio | ✓ |  | offen |
| `TICKET_FUNKTIONSKONTROLLE_4` | BOOLEAN | Werte Meßfunktion korrekt u. zuverlässig | ✓ |  | offen |
| `TICKET_FUNKTIONSKONTROLLE_5` | BOOLEAN | Kristalle arbeiten störungsfrei | ✓ |  | offen |
| `TICKET_FUNKTIONSKONTROLLE_6` | BOOLEAN | Farbdoppler sauber und störungsfrei | ✓ |  | offen |
| `TICKET_FUNKTIONSKONTROLLE_7` | BOOLEAN | PW Doppler-Messung präzise Signale | ✓ |  | offen |
| `TICKET_FUNKTIONSKONTROLLE_8` | BOOLEAN | CW Doppler kontinuierlich und genau | ✓ |  | offen |
| `TICKET_FUNKTIONSKONTROLLE_9` | BOOLEAN | 3D/4D Bildgebung hat klare Darstellung | ✓ |  | offen |
| `TICKET_GESAMT_BRUTTO` | DECIMAL | Gesamtpreis brutto | ✓ |  | offen |
| `TICKET_GESAMT_NETTO` | DECIMAL | Gesamtpreis netto | ✓ |  | offen |
| `TICKET_GWSFEHLERURSACHE` | TEXT | Fehlerursache | ✓ |  | offen |
| `TICKET_LEISTUNG_POS1_BEZ` | STRING(50) | Leistung Position 1 Bezeichnung | ✓ |  | offen |
| `TICKET_LEISTUNG_POS1_EPREIS` | CURRENCY | Leistung Position 1 Einzelpreis | ✓ |  | offen |
| `TICKET_LEISTUNG_POS1_MENGE` | DECIMAL | Leistung Position 1 Menge | ✓ |  | offen |
| `TICKET_MAIL` | STRING(64) | Mail | ✓ |  | offen |
| `TICKET_MESSWERTE_ART` | STRING(64) | Messverfahren | ✓ |  | offen |
| `TICKET_MESSWERTE_PRUEFMITTEL` | STRING(128) | Prüfmittel | ✓ |  | offen |
| `TICKET_MESSWERTE_SCHUTZKLASSE` | STRING(32) | Schutzklasse | ✓ |  | offen |
| `TICKET_MESSWERTE_SK1_IEGA` | DECIMAL | Schutzklasse 1 IEGA | ✓ |  | offen |
| `TICKET_MESSWERTE_SK1_IEPA` | DECIMAL | Schutzklasse 1 IEPA | ✓ |  | offen |
| `TICKET_MESSWERTE_SK1_IGA` | DECIMAL | Schutzklasse 1 IGA | ✓ |  | offen |
| `TICKET_MESSWERTE_SK1_IPA` | DECIMAL | Schutzklasse 1 IPA | ✓ |  | offen |
| `TICKET_MESSWERTE_SK1_RSL` | DECIMAL | Schutzklasse 1 RSL | ✓ |  | offen |
| `TICKET_MESSWERTE_SK1_ULN` | DECIMAL | Schutzklasse 1 ULN | ✓ |  | offen |
| `TICKET_MESSWERTE_SK2_IEGA` | DECIMAL | Schutzklasse 2 IEGA | ✓ |  | offen |
| `TICKET_MESSWERTE_SK2_IEPA` | DECIMAL | Schutzklasse 2 IEPA | ✓ |  | offen |
| `TICKET_MESSWERTE_SK2_IGA` | DECIMAL | Schutzklasse 2 IGA | ✓ |  | offen |
| `TICKET_MESSWERTE_SK2_IPA` | DECIMAL | Schutzklasse 2 IPA | ✓ |  | offen |
| `TICKET_MESSWERTE_SK2_RSL` | DECIMAL | Schutzklasse 2 RSL | ✓ |  | offen |
| `TICKET_MESSWERTE_SK2_ULN` | DECIMAL | Schutzklasse 2 ULN | ✓ |  | offen |
| `TICKET_MWST` | DECIMAL | 19 % Mwst. | ✓ |  | offen |
| `TICKET_POS1_BEZEICHNUNG` | STRING(100) | Pos. 1 Bezeichnung | ✓ |  | offen |
| `TICKET_POS1_EP` | DECIMAL | Pos. 1 EP | ✓ |  | offen |
| `TICKET_POS1_GP` | DECIMAL | Pos. 1 GP | ✓ |  | offen |
| `TICKET_POS1_MENGE` | DECIMAL | Pos. 1 Menge | ✓ |  | offen |
| `TICKET_POS2_BEZEICHNUNG` | STRING(100) | Pos. 2 Bezeichnung | ✓ |  | offen |
| `TICKET_POS2_EP` | DECIMAL | Pos. 2 EP | ✓ |  | offen |
| `TICKET_POS2_GP` | DECIMAL | Pos. 2 GP | ✓ |  | offen |
| `TICKET_POS2_MENGE` | DECIMAL | Pos. 2 Menge | ✓ |  | offen |
| `TICKET_POS3_BEZEICHNUNG` | STRING(100) | Pos. 3 Bezeichnung | ✓ |  | offen |
| `TICKET_POS3_EP` | DECIMAL | Pos. 3 EP | ✓ |  | offen |
| `TICKET_POS3_GP` | DECIMAL | Pos. 3 GP | ✓ |  | offen |
| `TICKET_POS3_MENGE` | DECIMAL | Pos. 3 Menge | ✓ |  | offen |
| `TICKET_POS4_BEZEICHNUNG` | STRING(100) | Pos. 4 Bezeichnung | ✓ |  | offen |
| `TICKET_POS4_EP` | DECIMAL | Pos. 4 EP | ✓ |  | offen |
| `TICKET_POS4_GP` | DECIMAL | Pos. 4 GP | ✓ |  | offen |
| `TICKET_POS4_MENGE` | DECIMAL | Pos. 4 Menge | ✓ |  | offen |
| `TICKET_POS5_BEZEICHNUNG` | STRING(100) | Pos. 5 Bezeichnung | ✓ |  | offen |
| `TICKET_POS5_EP` | DECIMAL | Pos. 5 EP | ✓ |  | offen |
| `TICKET_POS5_GP` | DECIMAL | Pos. 5 GP | ✓ |  | offen |
| `TICKET_POS5_MENGE` | DECIMAL | Pos. 5 Menge | ✓ |  | offen |
| `TICKET_POS6_BEZEICHNUNG` | STRING(100) | Pos. 6 Bezeichnung | ✓ |  | offen |
| `TICKET_POS6_EP` | DECIMAL | Pos. 6 EP | ✓ |  | offen |
| `TICKET_POS6_GP` | DECIMAL | Pos. 6 GP | ✓ |  | offen |
| `TICKET_POS6_MENGE` | DECIMAL | Pos. 6 Menge | ✓ |  | offen |
| `TICKET_POS7_BEZEICHNUNG` | STRING(100) | Pos. 7 Bezeichnung | ✓ |  | offen |
| `TICKET_POS7_EP` | DECIMAL | Pos. 7 EP | ✓ |  | offen |
| `TICKET_POS7_GP` | DECIMAL | Pos. 7 GP | ✓ |  | offen |
| `TICKET_POS7_MENGE` | DECIMAL | Pos. 7 Menge | ✓ |  | offen |
| `TICKET_RUECKRUFNUMMER` | STRING(32) | Rufnummer | ✓ | ✓ | offen |
| `TICKET_SICHTKONTROLLE_1` | BOOLEAN | frei von Verschmutzungen und Rückständen | ✓ |  | offen |
| `TICKET_SICHTKONTROLLE_10` | BOOLEAN | Akustische Linsen sauber unbeschädigt | ✓ |  | offen |
| `TICKET_SICHTKONTROLLE_2` | BOOLEAN | Aufschriften / Beschriftungen lesbar | ✓ |  | offen |
| `TICKET_SICHTKONTROLLE_3` | BOOLEAN | Typenschild vorhanden und erkennbar | ✓ |  | offen |
| `TICKET_SICHTKONTROLLE_4` | BOOLEAN | Netzwerkkabel unbeschädigt (eingesteckt) | ✓ |  | offen |
| `TICKET_SICHTKONTROLLE_5` | BOOLEAN | Monitorbild klar und artefaktfrei | ✓ |  | offen |
| `TICKET_SICHTKONTROLLE_6` | BOOLEAN | Buchsen/Steckverbindungen sauber und fes | ✓ |  | offen |
| `TICKET_SICHTKONTROLLE_7` | BOOLEAN | Keine mech. Beschädigungen/Verformungen | ✓ |  | offen |
| `TICKET_SICHTKONTROLLE_8` | BOOLEAN | Sondenkabel/Knickschutz intakt und flexi | ✓ |  | offen |
| `TICKET_SICHTKONTROLLE_9` | BOOLEAN | Gerätegehäuse frei von Rissen/Dellen | ✓ |  | offen |
| `TICKET_SYSTEM` | STRING(50) | System | ✓ |  | offen |
| `TICKET_TECHNIKERDIAGNOSE` | TEXT | Diagnose Techniker | ✓ |  | offen |
| `TICKET_TICKETESCALATIONSTIME1` | DATETIME | Eskalationszeitpunkt | ✓ |  | offen |
| `TICKET_TICKETUSERNAME` | STRING(50) | Verantwortlicher | ✓ | ✓ | offen |
| `TICKET_WARTUNGSARBEITEN_1` | BOOLEAN | Gerät geöffnet und alle Bestandteile ger | ✓ |  | offen |
| `TICKET_WARTUNGSARBEITEN_2` | BOOLEAN | Lüftungsschlitze Monitor gereinigt | ✓ |  | offen |
| `TICKET_WARTUNGSARBEITEN_3` | BOOLEAN | Tastatur und Trackball gereinigt | ✓ |  | offen |
| `TICKET_WARTUNGSARBEITEN_4` | BOOLEAN | Drucker gereinigt / Ausdruck geprüft | ✓ |  | offen |
| `TICKET_WARTUNGSARBEITEN_5` | BOOLEAN | Sondenleistung am Phantom/Körper geprüft | ✓ |  | offen |
| `TICKET_WARTUNGSARBEITEN_6` | BOOLEAN | Meßfunktionen und Programme geprüft | ✓ |  | offen |
| `TICKET_WARTUNGSARBEITEN_7` | BOOLEAN | STK & Funktion gemäß DIN EN 62353 | ✓ |  | offen |
| `TICKET_WARTUNGSARBEITEN_8` | BOOLEAN | Dokumentation (STK-Protokoll) für KV | ✓ |  | offen |
| `TICKET_ZUSATZINFORMATIONEN` | STRING(255) | TICKET_ZUSATZINFORMATIONEN | ✓ |  | offen |
| `WARTEAURUECKMELDUNG` | BOOLEAN | Warte auf Rückmeldung vom Kunden | ✓ |  | offen |
