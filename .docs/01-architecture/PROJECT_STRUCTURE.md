# Projektstruktur

> **Kurswechsel 2026-09-14** (ADR-033–ADR-040). Ersetzt den Vier-App-Baum
> (`core/` + `*dormed.de/`) vollständig. Eine Laravel-Anwendung im
> Wurzelverzeichnis.

## Ziel

Für einen Laravel-Entwickler beim ersten Blick erkennbar — es ist eine **normale
Laravel-Anwendung**. Die einzige Besonderheit ist `app/Modules/`, und die einzige
Ebene, die es zusätzlich zu verstehen gibt, ist **Zugriffspunkt ≠ Modul**:

- ein **Zugriffspunkt** (`dormed.de`, `shop.`, `erp.`, `my.`) ist eine Domain mit
  Routen, Controllern, Views und einer Datenbankrolle,
- ein **Modul** (`CRM`, `Billing`, `Service`, …) ist eine fachliche Domäne mit Models
  und Services.

Die beiden Achsen sind orthogonal. Ein Zugriffspunkt benutzt viele Module; ein Modul
wird von mehreren Zugriffspunkten benutzt.

## Verzeichnisbaum

```text
/app/
    Modules/<Modul>/             ← die Domäne, Namespace App\Modules\<Modul>\
        module.php                 Manifest: Zweck + depends_on
        Models/
        Services/
        Data/                      DTOs / Value Objects
        Policies/
        Events/
    Http/
        Controllers/
            Website/               dormed.de   → Blade-Responses
            Shop/                  shop.       → Inertia-Responses
            Erp/                   erp.        → Inertia-Responses
            Portal/                my.         → Inertia-Responses
            Api/V1/                spätere Mobile-App (ADR-033)
        Middleware/
            UseCustomerConnection.php   setzt pgsql_customer + app.company_id
            UsePublicConnection.php     setzt pgsql_public
        Requests/
    Providers/

/routes/
    website.php     dormed.de              anonym, dormed_public
    shop.php        shop.dormed.de         anonym bis Login, dann dormed_customer
    erp.php         erp.dormed.de          dormed_staff
    portal.php      my.dormed.de           dormed_customer
    console.php

/database/
    migrations/     eine flache Reihe für die ganze Anwendung
    factories/
    seeders/

/resources/
    views/          Blade — vollständige Seiten nur für dormed.de (ADR-039),
                    dazu die Inertia-Wurzel-View und Blade-Komponenten
    js/             Svelte + Inertia für ERP, Portal, Shop

/tests/
    Feature/        alles über HTTP Erreichbare, je Zugriffspunkt gruppiert
    Unit/           Framework-freie Logik
    Architecture/   ModuleBoundariesTest

/.docs/             Spezifikation und Entscheidungen
/.ai/rules/         verbindliche Regeln für Agenten und Team
/.legacy/           Altprojekte als Referenz — ungetrackt (ADR-035/040)
/docker/            ein Dockerfile für die eine Anwendung
/docker-compose.yaml
```

## Was es nicht mehr gibt

| Weg | Warum |
| --- | --- |
| `core/` als Composer-Package | ein Package mit einem Konsumenten ist ein Unterordner (ADR-033) |
| `*dormed.de/`-App-Ordner | die Domains sind Routing, keine Projekte (ADR-033) |
| Path-Repositories, `composer update dormed/core` | entfällt mitsamt der Fehlerklasse, die daran hing |
| `docker-compose.prod.yaml` mit vier Services | ein App-Service, bei Bedarf N Replicas |
| Ein `vendor/` je App | eines |

## `app/Modules/<Modul>/` — die Domäne

Namespace `App\Modules\<Modul>\`. Modul-intern geschichtet, **erst wenn Klassen
entstehen** (ADR-010) — keine leeren Modulordner auf Vorrat.

`module.php` gibt ein Array zurück und ist die **einzige** Quelle für erlaubte
Abhängigkeiten:

```php
return [
    'name' => 'Service',
    'description' => '…',
    'depends_on' => ['Core', 'Inventory', 'Documents', 'Billing'],
];
```

## Abhängigkeitsregeln — testgeschützt

`tests/Architecture/ModuleBoundariesTest` erzwingt:

- `depends_on` referenziert nur existierende Module, der Graph ist azyklisch.
- Ein Modul referenziert `App\Modules\X` nur, wenn es `X` in `depends_on` deklariert.
- **Kein `App\Http\…` in `app/Modules/**`.** Die Domäne kennt keine HTTP-Schicht.
  Diese Regel ist wichtiger als zuvor: Früher trennte eine Package-Grenze die beiden
  Welten physisch, jetzt liegen sie im selben `app/`-Baum und nur dieser Test hält sie
  auseinander.

## Controller

Dünn. Sie übersetzen HTTP ↔ Modul-Aufruf: Auth, Input, Service aufrufen, Response. Keine
Geschäftslogik, keine mehrzeiligen Eloquent-Ketten — die leben im Modul.

Gruppiert nach Zugriffspunkt (`Http/Controllers/Erp/`, `…/Website/`), weil sich daran
Routing, Frontend-Stack und Datenbankverbindung entscheiden.

## Frontend (ADR-039)

```text
resources/views/website/       Blade-Vollseiten — nur dormed.de (78 Seiten)
resources/views/components/    Blade-Komponenten — <x-layout> und Unterteile
resources/views/mail/          Mail-Vorlagen des Kontaktformulars
resources/views/app.blade.php  Inertia-Wurzel-View
resources/css/website/         style.css, widgets.css — eigene Vite-Einstiegspunkte
resources/js/website/          consent.js
resources/sitemap/             sitemap.xml, sitemap-system-pages.xml
resources/js/pages/erp/        Svelte
resources/js/pages/portal/
resources/js/pages/shop/
public/assets/                 Bilder und Prospekte der Website (58 MB, statisch)
```

**Blade-Komponenten liegen bewusst auf Wurzelebene**, nicht unter `website/`: Blade löst
`<x-layout>` immer aus `resources/views/components/` auf. Dadurch ließen sich die 78
Seiten ohne eine einzige Änderung an ihrem Inhalt übernehmen — nur die View-Namen in den
Routen tragen das Präfix `website.`.

**Route-Namen tragen dasselbe Präfix.** Vier Zugriffspunkte teilen sich in einem
Monolithen ein Namensregister; ohne Präfix würde ein späteres `kontakt` im Shop das der
Website stillschweigend überschreiben. Die URL-Pfade sind unverändert — kein Redirect,
kein SEO-Verlust.

Keine Inertia-Seiten unter `dormed.de`, keine Blade-Vollseiten im ERP.

## Datenbank

Eine flache Migrationsreihe unter `database/migrations/`. Migrations laufen als
**separater Deploy-Schritt**, nie im Container-Boot (ADR-015) — und als `dormed_owner`,
nie als die Rolle, mit der die Anwendung arbeitet (ADR-036).

Standards für Migrations und Models: [`../../.ai/rules/database.md`](../../.ai/rules/database.md)
und [`../04-database/DATABASE.md`](../04-database/DATABASE.md).

## `.legacy/` — Referenz, kein Code

```text
.legacy/dormed.de/         bestehender Blade-Auftritt
.legacy/shop.dormed.de/    bestehende Inertia/Svelte-Shop-App
.legacy/my.dormed.de/      bestehendes Portal (noch zu klonen)
```

Eigene Git-Projekte, in `.gitignore` des Monorepos, von keinem Werkzeug dieses
Repositorys erfasst (ADR-040). Zweck: fachliche Vorlage beim Neubau — hereinziehen,
nachlesen, **nie** zurückschreiben.

## Wichtige Regel

Die Struktur ist ein Zielbild. Verzeichnisse werden angelegt, wenn der erste Slice sie
füllt (ADR-010) — nicht vorab.

## Migrationshinweis

Der am 2026-09-13/14 erzeugte Baum (`core/`, `erp.dormed.de/`, `docker/<app>/`) ist
gelöscht, nicht portiert. Die fachlichen Specs (`../04-domain/*.md`) bleiben inhaltlich
gültig — nur ihre „Modul:"-Pfadangabe lautet jetzt `app/Modules/<Modul>/`.

Der Alt-Shop und die Alt-Website werden **nachgebaut**, nicht migriert (ADR-018); ihr Code
bleibt als Vorlage unter `.legacy/` lesbar.
