# Service-Domain

## Ziel

Service und Wartung sind getrennte fachliche Prozesse.

## Maintenance

Eine Wartung entsteht aus der Servicevereinbarung bzw. dem Wartungszeitpunkt.

Wichtig:

```text
planned_due_at
performed_at
finalized_at
```

sind fachlich unterschiedliche Zeitpunkte.

Wenn eine Wartung am 20.08.2026 tatsächlich durchgeführt wird, kann die nächste Jahreswartung aus dem tatsächlichen Durchführungsdatum entstehen.

Beispiel:

```text
due:       2026-03-01
performed: 2026-08-20
next due:  2027-08-20
```

Das ist ein fachlicher Grundsatz und darf nicht durch eine simple `last_maintenance`-/`next_maintenance`-Freitexteingabe ersetzt werden.

## Service Case

Ein Servicefall entsteht beispielsweise durch eine Störung.

Er kann enthalten:

- Diagnose
- Arbeiten
- Ersatzteile
- Messungen
- Servicebericht
- Abrechnung

Er darf nicht versehentlich als Wartung behandelt werden.

## Workflow

Der Ursprung des Vorgangs bestimmt den zulässigen Workflow.

Eine Wartung darf nicht einfach einen Servicebericht erzeugen, wenn der Prozess einen Wartungsbericht verlangt.

Die UI soll nur gültige Aktionen anbieten.

## Maintenance Report

Vorgesehene fachliche Bereiche:

1. Sichtprüfung
2. Funktionsprüfung
3. Messungen
4. ausgeführte Arbeiten
5. Mängel/Defekte
6. Betriebsstatus
7. Fotos/Nachweise
8. Kundenbestätigung

Checklisten sollen strukturiert und versionierbar sein.

Keine Spalten wie:

```text
visual_check_1
visual_check_2
...
```

als primäres Modell.

## Signatur

Kundenbestätigung ist ein Workflow-Gate.

Sie bestätigt die dokumentierten Leistungs-/Sachverhaltsdaten und kann Grundlage der Abrechnung sein.

Eine einfache Signatur ist nicht automatisch eine rechtlich qualifizierte elektronische Signatur.

Stärkere Signaturverfahren werden separat integriert, falls fachlich/legal erforderlich.
