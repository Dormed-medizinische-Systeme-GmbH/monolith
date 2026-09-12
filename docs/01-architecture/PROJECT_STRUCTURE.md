# Projektstruktur

> **Kurswechsel 2026-09-12** (ADR-011–ADR-018). Ersetzt den bisherigen Ein-App-Baum
> (`app/Modules/<Modul>/` in einer Laravel-Anwendung) vollständig.

## Ziel

Für einen Laravel-Entwickler sofort verständlich, mit klarer Trennung zwischen
**Deployment-Einheit** (App) und **fachlicher Domäne** (Modul in `packages/core`). Diese
zwei Achsen sind orthogonal (`ARCHITECTURE.md` §1/§4) und dürfen nicht verwechselt werden.

## Verzeichnisbaum

```text
/packages/core/                  ← Composer-Package, KEIN eigenständiges Laravel-Projekt
    src/
        Modules/
            Core/                ← Company, Person, CompanyContact, Address, Location
                module.php        Manifest: Zweck + erlaubte Abhängigkeiten (depends_on)
                Models/
                Services/
                Data/             DTOs / Value Objects
                Policies/
            Identity/
            Service/              Device, ServiceContract, Maintenance, ServiceCase, …
            Billing/               Invoice, Payment, …
            Sales/                 Opportunity, …
            Scheduling/            Appointment, …
            Documents/
            Inventory/
            Platform/              Soft-Delete/Papierkorb, Audit — geteilte technische Infrastruktur
            Integrations/
        Events/                  domänenübergreifende Events (z. B. InvoiceIssued)
    database/
        migrations/              ALLE Migrations liegen NUR hier — eine flache Reihe
        factories/
        seeders/
    composer.json                Paketname z. B. `dormed/core`, Namespace `Dormed\Core\`

/apps/website/                   ← eigenständiges Laravel-Projekt (ADR-017). Frontend: Blade
                                   (bestehende Views). Existiert bereits — nur architektonisch
                                   eingebettet, kein aktiver Aufbau jetzt (ADR-020).
    composer.json                bindet packages/core per Path-Repository ein
    Dockerfile
    app/ resources/ routes/ config/ tests/ ...   app-spezifischer Code

/apps/shop/                      ← eigenständiges Laravel-Projekt. Frontend: Inertia.js + Svelte.
                                   Läuft bereits LIVE in einem separaten Repo außerhalb dieses
                                   Monorepos — wird später hereinmigriert (ADR-019/020). Jetzt nur
                                   Platzhalter/Wiring, kein aktiver Aufbau.
/apps/crm/                       ← eigenständiges Laravel-Projekt. Frontend: Inertia.js + Svelte
                                   (ADR-019). **Aktueller Bau-Fokus** neben packages/core (ADR-020).
/apps/portal/                    ← eigenständiges Laravel-Projekt. Existiert größtenteils bereits
                                   (Frontend-Stack noch ungeklärt, vermutlich Inertia). Jetzt nur
                                   Platzhalter/Wiring, kein aktiver Aufbau (ADR-020).

/compose.yaml                    lokaler Dev-Stack: 4 Apps + Postgres
/compose.prod.yaml                Coolify-Stack (ADR-014)
/docker/
    <app>/Dockerfile              Multi-Stage je App, siehe DOCKER.md
```

## Composer-Verdrahtung (pro App identisch)

Jede App in `/apps/*` bindet `packages/core` als lokales Path-Repository ein:

```json
{
    "repositories": [
        { "type": "path", "url": "../../packages/core" }
    ],
    "require": {
        "dormed/core": "*"
    },
    "minimum-stability": "dev",
    "prefer-stable": true
}
```

Das ist **derselbe PHP-Code**, kein Duplikat. Composer symlinkt im lokalen Dev, im
Docker-Build wird der Code kopiert (siehe `../06-infrastructure/DOCKER.md`).

## Zwei Ebenen — wo gehört was hin

### `packages/core` — die Domäne (SSOT, ADR-011/013)

Ein Ordner pro fachlichem Modul (`ARCHITECTURE.md` §4), Namespace
`Dormed\Core\Modules\<Modul>\`. Modul-intern geschichtet, **erst wenn Klassen
entstehen** (ADR-010) — keine leeren Modulordner auf Vorrat.

`module.php` gibt ein Array zurück und ist die **einzige Quelle** für die erlaubten
Abhängigkeiten (`depends_on`):

```php
return [
    'name' => 'Service',
    'description' => '…',
    'depends_on' => ['Core', 'Documents', 'Billing'],
];
```

### `apps/<app>` — geteilte Plattform + HTTP-Shell je App (Standard-Laravel)

Alles, was nur einer einzelnen Domäne (Website/Shop/CRM/Portal) gehört und keine
fachliche Domänen-Logik ist:

```text
apps/crm/app/
├── Http/
│   ├── Controllers/     dünn: Auth, Input, Modul-Aufruf (aus packages/core), Response
│   ├── Middleware/
│   └── Requests/
├── Providers/
└── View/
```

Controller sind **dünn**: sie übersetzen HTTP ↔ Aufruf eines Core-Moduls (Action /
Domain-Service). Keine Geschäftslogik, keine Eloquent-Queries direkt in der App
(`03-domain-boundaries-rules.md` Regel 1+2).

**Kein `App\Modules\…` mehr in einer `apps/*`-App** — Models/Migrations/domänenübergreifende
Services gibt es dort nicht (Regel 1).

## Abhängigkeitsregeln (ADR-005/ADR-013) — testgeschützt

`packages/core/tests/Architecture/ModuleBoundariesTest` erzwingt:

- `depends_on` referenziert nur existierende Module, der Graph ist azyklisch.
- Ein Modul referenziert `Dormed\Core\Modules\X` nur, wenn es `X` in `depends_on`
  deklariert.
- Kein `use App\Http\…`/App-Namespace in `packages/core/src/**` — die Domäne kennt keine
  HTTP-Schicht und keine einzelne App.
- Eine `apps/*`-App ist **kein** Knoten im `depends_on`-Graphen — sie referenziert
  `packages/core` als Ganzes.

## Routing

Jede App hat ihr **eigenes** Routing, keine zentrale Host-Unterscheidung mehr
(ADR-012, ersetzt das bisherige `config/domains.php` + `ApplicationContext`):

```text
apps/crm/routes/web.php       # apps/crm bedient ausschließlich seine eigene Domain
apps/portal/routes/web.php
apps/shop/routes/web.php
apps/website/routes/web.php
```

Details, insbesondere zum geteilten Portal/Shop-Login: `MULTI_SUBDOMAIN.md`.

## Datenbank

```text
packages/core/database/
├── factories/
├── migrations/          eine flache Migrationsreihe für ALLE vier Apps
└── seeders/
    ├── DatabaseSeeder.php
    └── Demo/             reproduzierbarer Demo-Seed (nie in Produktion)
```

Migrations werden **nie** aus einer `apps/*`-App heraus ausgeführt, sondern zentral
(ADR-015, `../06-infrastructure/DOCKER.md`).

## Assets

App-lokale UI-Assets liegen in der jeweiligen App (`apps/<app>/resources/`). Ein
gemeinsames Theme über die Apps hinweg ist eine Design-, keine Code-Teilungsfrage (kein
gemeinsames `resources/`-Verzeichnis mehr, da jede App ihr eigenes Laravel-Projekt ist).

## Tests

```text
packages/core/tests/
├── Unit/                        Logik ohne Framework
└── Architecture/                Modulgrenzen (ModuleBoundariesTest)

apps/<app>/tests/
└── Feature/                     alles über HTTP Erreichbare dieser App
```

## Wichtige Regel

Die Struktur ist ein Zielbild. Verzeichnisse werden angelegt, wenn der erste Slice sie
füllt (ADR-010) — nicht vorab.

## Migrationshinweis (Bestandscode)

Der bisherige Ein-App-Baum (`app/Modules/{Core,Crm}`, 5 Migrations, `routes/{crm,portal,
shop}.php`, `ApplicationContext`) wird **verworfen**, nicht Feld-für-Feld portiert
(ADR-018). Die fachlichen Specs (`../04-domain/CORE.md` etc.) bleiben inhaltlich gültig —
nur ihre „Modul:"-Pfadangabe wandert von `app/Modules/<Modul>/` nach
`packages/core/src/Modules/<Modul>/`.
