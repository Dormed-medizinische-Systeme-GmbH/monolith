# Projektstruktur

## Ziel

Für einen Laravel-Entwickler sofort verständlich und gleichzeitig fachliche Module
sauber abgegrenzt. Laravel-Standardverzeichnisse bleiben, wo Konventionen sinnvoll
sind. Die Domäne liegt **modul-first**, nicht layer-first verteilt.

## Zwei Ebenen

### `app/` — geteilte Plattform + HTTP-Shell (Standard-Laravel)

Alles, was allen Kontexten gehört und keine fachliche Domäne ist:

```text
app/
├── Http/
│   ├── Controllers/{Crm,Portal,Shop}/   dünn: Auth, Input, Modul-Aufruf, Response
│   ├── Middleware/
│   └── Requests/{Crm,Portal,Shop}/
├── Models/User.php                      Identität (Auth-Config zeigt hierauf)
├── Support/                             ApplicationContext, host-nahe Helfer
├── Providers/
└── View/
```

Controller unter `Http/Controllers/<Kontext>/` sind **dünn**: sie übersetzen
HTTP ↔ Modul-Aufruf. Keine Geschäftslogik, keine Eloquent-Queries.

### `app/Modules/<Modul>/` — die Domäne

Ein Ordner pro fachlichem Modul (`ARCHITECTURE.md` §4), Namespace `App\Modules\<Modul>\`.
Modul-intern geschichtet, **erst wenn Klassen entstehen**:

```text
app/Modules/
├── Core/
│   └── module.php                       Manifest: Zweck + erlaubte Abhängigkeiten
└── Crm/
    ├── module.php
    ├── Models/        Company, Person, CompanyContact, Address, Location
    ├── Actions/
    ├── Data/          DTOs / Value Objects
    └── Policies/
```

`module.php` gibt ein Array zurück und ist die **einzige Quelle** für die erlaubten
Abhängigkeiten (`depends_on`). Neues Modul = Ordner + `module.php`, erst wenn ein
Slice es braucht. Keine leeren Modulordner auf Vorrat.

## Subdomain ≠ Modul

`crm` / `portal` / `shop` sind **Einstiegs-/Präsentationskontexte** (andere
Auth-Zielgruppe, UI, Autorisierung). Sie sind keine Module. Portal und CRM
konsumieren dieselben Domänenmodule (z. B. `Service`, `Documents`, `Billing`) mit
unterschiedlichen Policies und View-Modellen.

## Abhängigkeitsregeln (ADR-005) — testgeschützt

`tests/Architecture/ModuleBoundariesTest` erzwingt:

- `depends_on` referenziert nur existierende Module, der Graph ist azyklisch.
- Ein Modul referenziert `App\Modules\X` nur, wenn es `X` in `depends_on` deklariert.
- Kein `use App\Http\…` in `app/Modules/**` — die Domäne kennt die HTTP-Schicht nicht.

Läuft in der normalen Suite mit (`php artisan test`).

## Routing

```text
routes/
├── web.php      host-unabhängig: auth.php, Profil, /dashboard
├── auth.php
├── crm.php      nur config('domains.crm')
├── portal.php   nur config('domains.portal')
└── shop.php     nur config('domains.shop')
```

Registrierung zentral in `bootstrap/app.php` via `withRouting(then: …)`. Details:
`docs/01-architecture/MULTI_SUBDOMAIN.md`, `.ai/rules/routing.md`.

## Datenbank

```text
database/
├── factories/
├── migrations/          eine flache Migrationsreihe für den ganzen Monolithen
└── seeders/
    ├── DatabaseSeeder.php
    └── Demo/            reproduzierbarer Demo-Seed (nie in Produktion)
```

## Assets

Globale UI-Assets zentral (`resources/js/{components,layouts,shared}`,
`resources/views/{components,layouts}`). Fachliche Assets nach Kontext
(`…/{crm,portal,shop}`). Gemeinsames Theme für alle Kontexte erlaubt. Ordner
entstehen mit dem ersten Asset, nicht auf Vorrat.

## Tests

```text
tests/
├── Feature/{Crm,Portal,Shop}/   die meisten Tests
├── Unit/                        nur Logik ohne Framework
└── Architecture/                Modulgrenzen
```

## Wichtige Regel

Die Struktur ist ein Zielbild. Verzeichnisse werden angelegt, wenn der erste
Slice sie füllt (ADR-010) — nicht vorab.
