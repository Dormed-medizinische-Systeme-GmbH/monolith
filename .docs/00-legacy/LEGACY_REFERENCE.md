# Legacy Reference

## Zweck

Diese Datei dokumentiert die Rolle der bereitgestellten Altdefinitionen.

> **Struktur 2026-09-12:** je Legacy-Objekt ein eigener Domänen-Ordner mit allen
> zugehörigen Dateien zusammen (rohes XML-Schema, `*-Felder.md`-Inventar,
> `*-Zuordnung.md`-Synthese, ggf. ein echter CSV-Datenexport) — statt getrennter
> `xml/`/`csv/`/`mapping/`-Ordner nach Dateityp. Lese-Anleitung/Format-Erklärung:
> [`README.md`](README.md).

| Domäne | Ordner | Rohes Schema | Feld-Inventar | Zuordnung | Echte Daten |
| --- | --- | --- | --- | --- | --- |
| Address/Kontakte | [`Adressen/`](Adressen/) | [`Adressen.xml`](Adressen/Adressen.xml) (356 Spalten) | [`Adressen-Felder.md`](Adressen/Adressen-Felder.md) | [`Adressen-Zuordnung.md`](Adressen/Adressen-Zuordnung.md) | [`Kontakte.csv`](Adressen/Kontakte.csv) |
| Serviceverträge | [`Servicevertraege/`](Servicevertraege/) | [`Servicevertraege-NEU.xml`](Servicevertraege/Servicevertraege-NEU.xml) (83 Spalten) | [`Servicevertraege-NEU-Felder.md`](Servicevertraege/Servicevertraege-NEU-Felder.md) | [`Servicevertraege-Zuordnung.md`](Servicevertraege/Servicevertraege-Zuordnung.md) | [`Servicevertraege.csv`](Servicevertraege/Servicevertraege.csv) |
| Tickets | [`Tickets/`](Tickets/) | [`Tickets.xml`](Tickets/Tickets.xml) (112 Spalten) | [`Tickets-Felder.md`](Tickets/Tickets-Felder.md) | [`Tickets-Zuordnung.md`](Tickets/Tickets-Zuordnung.md) | [`Tickets.csv`](Tickets/Tickets.csv) |
| Termine | [`Termine/`](Termine/) | [`Termine.xml`](Termine/Termine.xml) (25 Spalten) | [`Termine-Felder.md`](Termine/Termine-Felder.md) | — | [`Kalendaransicht.png`](Termine/Kalendaransicht.png) (Screenshot der Kalenderansicht im Altsystem) |
| Verkaufschancen | [`Verkaufschancen/`](Verkaufschancen/) | [`Verkaufschancen.xml`](Verkaufschancen/Verkaufschancen.xml) (34 Spalten) | [`Verkaufschancen-Felder.md`](Verkaufschancen/Verkaufschancen-Felder.md) | — | — |

Rohe Schema-Exporte = CAS genesisWorld `gwconnect`-Objektdefinitionen aus dem
produktiven CRM, nur Feld-**Definitionen**, keine Beispieldaten. Die CSV-Dateien
sind echte **Daten**-Exporte (populierte Zeilen) — nützlich, um Enum-Werte/
Feldbelegung real zu verifizieren, statt sie aus dem Schema zu erraten. Alle
stammen aus dem aktuellen System und dienen als fachliche **Ist-Referenz**
(ADR-004) — nicht als Zielschema.

### Notizen zu den echten Datenexporten

- **`Servicevertraege/Servicevertraege.csv`** (4 Beispielzeilen). Encoding-Defekt
  im Quellexport: einzelne Umlaute/ß sind als kaputte Mojibake-Fragmente (`Ã¤`,
  `Ã¼`, teils unvollständig) statt korrekter UTF-8-Zeichen enthalten — Ursache
  liegt im Original-Export, nicht behebbar ohne Datenverlust-Risiko, daher
  unverändert abgelegt. Zahlen-/Datums-/Status-Spalten sind unbetroffen.
  Bestätigt real: `Vertragsart` = „Grundwartung", `Vertragsstatus` ∈ {„aktiv",
  „Angebot", „gekündigt", „kein Interesse"} (deckt sich mit D-079/D-081),
  `ERFUELLUNGSORT_ÜBERTRAG`-Feld existiert real (stützt D-082/D-084).
- **`Adressen/Kontakte.csv`** (1 Beispielzeile, ~330 Spalten). Gleicher
  Encoding-Defekt, unverändert abgelegt. Bestätigt real: der komplette
  `DO_SV*`/`DO_SVV*`-Slot-Block ist in dieser Zeile leer (stützt D-001 „nicht
  mehr in Verwendung"), `ist Firma`/`ist Kontaktperson`/`ist Mitarbeiter`-
  Diskriminatoren existieren real (D-012), Consent-Felder je Kanal
  (`Einwilligung FAX/MAIL/POST/SMS/TELEFON`) existieren real (D-013),
  `FAHRTZONENPAUSCHALE` war produktiv befüllt (225,00 — historischer Wert, wird
  durch `travel_zones`/D-084 abgelöst, keine Handlung nötig). Auffällig:
  `Verantwortlicher Sales` und `Verantwortlicher (Sales)` sind zwei separate
  Spalten mit demselben Wert — Legacy-Dublette, unkritisch (D-016 nutzt ohnehin
  nur ein Zielfeld).
- **`Tickets/Tickets.csv`** (2 Beispielzeilen). Gleicher Encoding-Defekt,
  unverändert abgelegt. Bestätigt real: `Typ` = „Allgemeiner Support" (deckt sich
  mit D-097), Messprotokoll-/Checklisten-Felder (`Schutzklasse 1/2 IEGA/IEPA/…`,
  vier `Mängel …`-Booleans, Sicht-/Funktionskontroll-Punkte) entsprechen 1:1
  `MeasurementProtocol`/`MaintenanceReport` (D-038/D-044), „Fahrtzone Wartung"
  taucht real als eigene Rechnungsposition auf. **Real beobachtete `Status`-Werte
  weichen von D-083 ab** — s. offene Rückfrage in `grill-log.md` (D-083 evtl. zu
  revidieren).

## Kernerkenntnisse

### Adressen (`Adressen/Adressen.xml`)

Das alte `Address`-Objekt ist ein universelles Beziehungsobjekt mit sehr vielen
Verwendungszwecken (Unternehmen, Kontaktpersonen, Rechnungsadressen, Standorte …).

### Opportunities (`Verkaufschancen/Verkaufschancen.xml`)

Das alte Opportunity-Modell besitzt bereits typische Sales-Daten wie Status,
Phase, Wahrscheinlichkeit, Budget und Wettbewerber.

### Termine (`Termine/Termine.xml`)

Termine sind derzeit relativ generisch und müssen im Zielsystem stärker mit
fachlichen Vorgängen verknüpft werden.

### Tickets (`Tickets/Tickets.xml`)

Tickets vermischen Service, Wartung, Checklisten, Messungen, Arbeiten und Abrechnung.

### Serviceverträge (`Servicevertraege/Servicevertraege-NEU.xml`)

Serviceverträge vermischen Vertrag, Gerät und technische Gerätekonfiguration.

## Konsequenz

Die neue Architektur soll diese Vermischung reduzieren und fachliche Prozesse
explizit modellieren. Die konkrete Zerlegung wird in `/grill-me` festgelegt und
in `.docs/04-domain/` + der ROADMAP dokumentiert.
