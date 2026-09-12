# Realtime

## Ziel

Realtime ist ein wichtiger Bestandteil der späteren Anwendung.

Beispiele:

- Kunde bucht Wartung -> Mitarbeiter erhält Live-Information
- Ticket-/Servicestatus ändert sich
- Management-Dashboard aktualisiert sich
- später mobile Statusinformationen

## Technologie

Laravel Reverb ist der aktuelle Zielkandidat.

Realtime wird über Laravel Events/Channels strukturiert.

## Sicherheit

Private Channels müssen serverseitig autorisiert werden.

Ein Benutzer darf niemals Events eines anderen Kunden-/Unternehmenskontexts abonnieren können.

## Nicht erforderlich

Chat ist derzeit kein Kernfeature.

Realtime wird für fachliche Status- und Benachrichtigungsfälle priorisiert.
