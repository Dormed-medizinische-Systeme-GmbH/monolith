# CRM

## Zweck

Das CRM ist der erste echte fachliche Anwendungskontext.

Es ist der beste Kandidat für den ersten vertikalen Slice nach dem technischen Fundament.

## Code-Ort

Domänencode liegt unter `packages/core/src/Modules/Crm/` (Namespace
`Dormed\Core\Modules\Crm\`, Abhängigkeit: nur `Core` — siehe
`packages/core/src/Modules/Crm/module.php`, ADR-013). Die HTTP-Schicht liegt in der
eigenständigen `apps/crm`-App (`apps/crm/app/Http/Controllers/`) und ruft das Modul auf.
Struktur/Regeln: `docs/01-architecture/PROJECT_STRUCTURE.md`, `.ai/rules/architecture.md`.

## Erste Ausbaustufe

Zunächst:

```text
Company
Person
CompanyContact
Address
Location
```

Danach:

```text
Communication
Tasks
Appointments
Sales
```

## Ziel-UX

Ein Mitarbeiter soll eine Company als zentrale Arbeitsfläche verstehen können.

Beispiel:

```text
Company
├── Stammdaten
├── Kontakte
├── Locations
├── Geräte
├── Serviceverträge
├── Tickets/Servicefälle
├── Termine
├── E-Mails
├── Telefonate
├── Dokumente
├── Opportunities
└── Rechnungen
```

Die konkrete Navigation wird erst nach dem ersten funktionierenden UI-Slice festgelegt.

## Mitarbeiter

Mitarbeiter sind technische Benutzeridentitäten mit fachlicher Zuordnung:

```text
User
 -> Employee
    -> Department
    -> Roles/Permissions
```

## CRM ist kein Universalobjekt

Nicht alle Aktivitäten werden in einer einzigen generischen Tabelle mit einem `type` gespeichert.

Gemeinsame Activity-Infrastruktur darf existieren, aber fachliche Daten bleiben fachlich modelliert.
