# Authorization

## Rollenmodell

Das System verwendet fachliche Berechtigungen statt einer einzelnen globalen `is_super_admin`-Abkürzung.

Mögliche Bereiche:

- Management
- Sales
- Service
- Accounting
- weitere Departments

Mitarbeiter derselben Abteilung haben grundsätzlich vergleichbare Rechte, sofern der konkrete Prozess nichts anderes verlangt.

## Grundprinzip

```text
Authentication
    -> Who are you?

Authorization
    -> What may you do?

Business Workflow
    -> What may you do now?
```

Der dritte Punkt ist entscheidend.

Ein Benutzer kann grundsätzlich die Berechtigung besitzen, eine Wartung zu bearbeiten, aber nicht jeden beliebigen Zustand einer Wartung verändern.

## Gamify-your-task-Prinzip

Die UI soll möglichst nur gültige nächste Aktionen anbieten.

Beispiel:

```text
[Wartung fällig]
       |
       +--> Termin planen
                 |
                 v
             Zugewiesen
                 |
                 +--> Arbeit starten
                           |
                           v
                       Durchführung
                           |
                           v
                    Kunde bestätigt
                           |
                           v
                       Abschluss
```

Ungültige Aktionen werden nicht nur versteckt, sondern serverseitig ebenfalls verhindert.

## Management

Management darf zusätzliche Berechtigungen erhalten.

Es wird kein Sonderpfad eingeführt, der die gesamte RBAC-/Policy-Architektur umgeht.

## Customer Contacts

Kundenkontakte werden über die zugehörige Company bzw. deren gültige Beziehungen gescoped.

Ein Contact einer Company darf nicht automatisch Daten anderer Companies sehen.

## Department ≠ Data Ownership

Die aktuelle Geschäftslogik basiert überwiegend auf Abteilungen, nicht auf individueller Kundenverteilung.

Es soll daher nicht ohne konkreten Bedarf ein kompliziertes Customer-Assignment-System gebaut werden.
