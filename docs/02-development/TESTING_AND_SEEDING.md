# Testing und Seed-Daten

## Ziel

Die lokale Anwendung muss mit reproduzierbaren Demo-Daten sofort überprüfbar sein.

## Seeder

Es gibt einen zentralen `DatabaseSeeder`.

Er delegiert an klar benannte Demo-/Test-Seeder.

Beispiel:

```text
database/seeders/
├── DatabaseSeeder.php
└── Demo/
    ├── DemoUserSeeder.php
    ├── DemoCompanySeeder.php
    └── ...
```

Nur tatsächlich implementierte Bereiche werden geseedet.

## Demo-Daten

Demo-Daten müssen:

- eindeutig als Testdaten erkennbar sein,
- reproduzierbar sein,
- keine realen personenbezogenen Daten enthalten,
- keine Produktionsdaten simulieren, die mit echten Personen verwechselt werden könnten,
- über `php artisan migrate:fresh --seed` reproduzierbar sein.

## Benutzer

Mindestens ein technischer Demo-Account für interne Nutzung und, sobald das Customer-Modell existiert, ein Demo-Kundenkonto sollen vorgesehen werden.

Passwörter dürfen nur aus lokalen, explizit dokumentierten Testwerten bestehen.

## Tests

Priorität:

1. Domain-/Business-Rule-Tests
2. Autorisierungstests
3. Workflow-/State-Tests
4. Datenbankintegrität
5. HTTP/Feature-Tests
6. UI-Tests, wenn sinnvoll

## Workflow-Prinzip

Tests müssen verhindern, dass Benutzer fachlich ungültige Aktionen durchführen können.

Beispiel:

```text
Maintenance due
 -> scheduled
 -> assigned
 -> performed
 -> customer confirmed
 -> finalized
 -> billing approved
```

Ein Benutzer darf nicht direkt von `due` nach `billing approved` springen, wenn der Fachprozess dies nicht erlaubt.

## Testdaten und Produktionsdaten

Demo-Seeder und Produktionsimport sind zwei unterschiedliche Mechanismen.

Ein späterer Live-Import muss separat entwickelt und dokumentiert werden.
