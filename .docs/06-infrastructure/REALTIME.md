# Realtime

> **Vertagt (ADR-027, 2026-09-13).** Reverb wird **vorerst nicht gebaut** — es gibt
> keinen Realtime-Service in `docker-compose.yaml`/`docker-compose.prod.yaml`. Kein Anwendungsfall im
> aktuell spezifizierten Umfang erzwingt Push: „Kunde bucht Wartung" hängt an
> `my.dormed.de` (nicht in der Bauphase, ADR-020), und Ticketstatus wie
> Management-Cockpit (D-126) sind Listen und Kennzahlen, die beim Laden aktuell sind.
>
> Dieses Dokument beschreibt damit das **Zielbild**, nicht den Bauauftrag. Nachrüsten
> ist additiv: Service hinzufügen, `BROADCAST_CONNECTION` setzen, Events und Channels
> ergänzen — nichts am Skelett muss dafür umgestellt werden.

## Ziel

Realtime ist ein wichtiger Bestandteil der späteren Anwendung.

Beispiele:

- Kunde bucht Wartung -> Mitarbeiter erhält Live-Information
- Ticket-/Servicestatus ändert sich
- Management-Dashboard aktualisiert sich
- später mobile Statusinformationen

## Technologie

Laravel Reverb ist der aktuelle Zielkandidat — **wenn** Realtime gebaut wird (ADR-027).
Dann als **eigener** Prozess/Container je App: ein Container hat genau ein `CMD`, und
der HTTP-Server (Octane/FrankenPHP) und `reverb:start` sind zwei Daemons, die sonst
einen Supervisor im Container bräuchten. **Kein** von allen vier Apps geteilter
Reverb-Prozess (aus ADR-024 weiterhin gültig).

Realtime wird über Laravel Events/Channels strukturiert.

## Sicherheit

Private Channels müssen serverseitig autorisiert werden.

Ein Benutzer darf niemals Events eines anderen Kunden-/Unternehmenskontexts abonnieren können.

## Nicht erforderlich

Chat ist derzeit kein Kernfeature.

Realtime wird für fachliche Status- und Benachrichtigungsfälle priorisiert.
