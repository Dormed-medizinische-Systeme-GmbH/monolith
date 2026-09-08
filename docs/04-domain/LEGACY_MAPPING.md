# Legacy Mapping

## Zweck

Die vorhandenen XML-Definitionen dienen zur Analyse des Ist-Zustands.

Sie sind keine Vorgabe für das neue Schema.

## Address

Die Legacy-`Address`-Entität ist sehr breit und übernimmt Rollen für:

- Unternehmen
- Kontaktpersonen
- Kunden
- Melder
- Mitarbeiter
- Rechnungsadressen
- Standorte bzw. andere Beziehungen
- generische Referenzen aus Opportunities, Tickets, Terminen, Dokumenten, E-Mails und Telefonaten

Die XML-Definition (`docs/09-legacy/xml/Adressen.xml`) enthält 356 Spalten, davon rund 176 Kunden-/Custom-Felder.

### Ziel

Aufteilen in fachlich klare Konzepte:

```text
Company
Person
CompanyContact
Address
Location
```

## Opportunities

Die Legacy-Opportunity enthält u. a.:

- Kunde
- Verkäufer
- Stellvertretung
- Abteilung
- Quelle
- Status
- Verkaufsphase
- Wahrscheinlichkeit
- Budget
- Betrag
- Zeitraum
- Wettbewerber
- Kooperation

Die Struktur ist ein guter Hinweis auf ein zukünftiges `SalesOpportunity`, aber nicht zwingend dessen finales Schema. (`docs/09-legacy/xml/Verkaufschancen.xml`, 34 Spalten)

## Appointments

Legacy-Termine enthalten u. a.:

- Start/Ende
- Dauer
- Alarm
- Kategorie
- Status
- Typ
- Teilnehmer
- Notizen
- Online-Meeting
- Ganztag

Zukünftig sollte `Appointment` als Scheduling-Objekt von fachlichen Vorgängen sauber getrennt werden. (`docs/09-legacy/xml/Termine.xml`, 25 Spalten)

## Tickets

Das Legacy-Ticket umfasst 112 Custom Fields. Darunter befinden sich:

- Workflow-/Statusfelder
- Abschlussfelder
- Wartungsarbeiten
- Sichtkontrolle
- Funktionskontrolle
- Messungen
- Servicepositionen
- Diagnose
- ausgeführte Arbeiten
- Fehlerursache
- Abrechnung

Das ist ein überladenes Universalobjekt und wird nicht als Zielstruktur übernommen. (`docs/09-legacy/xml/Tickets.xml`, 112 Spalten)

## ServiceContract

Der Legacy-Servicevertrag enthält gleichzeitig:

- Vertragsdaten
- Wartungsintervalle
- letzte/nächste Wartung
- Kosten
- Geräteinformationen
- Hersteller
- Seriennummer
- technische Netzwerkdaten
- Betriebssystem-/Softwaredaten
- Peripherie

Die XML-Definition (`docs/09-legacy/xml/Servicevertraege-NEU.xml`) umfasst 83 Spalten.

### Ziel

Trennung:

```text
ServiceContract
Device
DeviceConfiguration
DeviceComponent
Maintenance
```

Nicht alle genannten Objekte müssen als eigene Tabelle entstehen. Die fachliche Trennung ist zuerst entscheidend.

## Legacy-Regel

Ein Legacy-Feld wird nur übernommen, wenn seine fachliche Bedeutung im Zielmodell benötigt wird.

Ein Legacy-Feld wird nicht übernommen, nur weil es existiert.
