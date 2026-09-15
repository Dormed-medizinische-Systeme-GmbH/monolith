# Project Rules Index

Before planning or editing, find the row whose globs match the file's path and read that rule file.

| Globs | Rule file |
| --- | --- |
| `app/**`, `tests/**` | [architecture.md](architecture.md) |
| `routes/**`, `bootstrap/app.php`, `app/Http/Middleware/**`, `config/database.php` | [routing.md](routing.md) |
| `database/migrations/**`, `app/Modules/**/Models/**` | [database.md](database.md) |
| überall | [language.md](language.md) — Bezeichner Englisch, Oberfläche Deutsch |
| `docker-compose*.yaml`, `docker/**`, `phpunit.xml`, `.env.example` | [local-stack.md](local-stack.md) |

> **Struktur (ADR-033).** **Eine** Laravel-Anwendung im Repo-Wurzelverzeichnis, **eine**
> PostgreSQL-Datenbank, **vier** Zugriffspunkte über Domain-Routing — `dormed.de`
> (Website), `erp.dormed.de` (Mitarbeiter), `my.dormed.de` (Portal), `shop.dormed.de`
> (Shop). Kein `core`-Package, keine App-Ordner, keine Path-Repositories.
> Siehe `.docs/01-architecture/ARCHITECTURE.md`.
>
> Fachliche Module liegen unter `app/Modules/<Modul>/`. Die Mitarbeiter-Anwendung heißt
> **ERP**, nicht CRM — CRM ist darin eine Domäne neben Service, Billing und Inventory
> (ADR-031).
>
> **`.legacy/` ist ungetrackt und kein Code dieses Projekts** (ADR-035/040) — nur
> Lesestoff. Nie dorthin schreiben, nie von dort importieren.
