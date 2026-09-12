---
paths:
  - apps/*/routes/**
  - apps/*/bootstrap/app.php
  - apps/*/app/Http/Middleware/**
---

# Routing & Cross-App-Login

> Kurswechsel 2026-09-12 (ADR-012, ADR-016). Ersetzt das frühere zentrale
> `config('domains.<ctx>')` + `App\Support\ApplicationContext`-Dispatch in einer
> Codebasis vollständig. Details: `.docs/01-architecture/MULTI_SUBDOMAIN.md`.

## Eine Domain pro App

Jede der vier Apps (`apps/website|shop|crm|portal`) bedient **ihre eigene** Domain über
ihr **eigenes** Laravel-Routing. Kein Host-Matching, kein `->domain(...)`-Constraint
nötig — die App kennt nur einen Host. `routes/web.php` in jeder App ist die normale
Laravel-Struktur, keine Kontext-Aufteilung mehr wie früher `routes/{crm,portal,shop}.php`
in einer einzigen Codebasis.

## Subdomain/App ist KEINE Sicherheitsgrenze

Dass eine Route nur in `apps/crm` existiert, ist kein Autorisierungsersatz. Jede Aktion
braucht zusätzlich eine fachliche Prüfung (Policy / Gate / Query-Scope) — sowohl in der
App als auch im aufgerufenen Core-Modul. Kein Vertrauen auf den Hostnamen oder die
App-Zugehörigkeit für Berechtigungen.

## Cross-App-Login (ADR-016)

- **`apps/crm`**: eigene, komplett getrennte Session/Nutzertabelle. Kein Cross-App-Cookie.
- **`apps/portal`** + **`apps/shop`**: teilen sich einen Kundenlogin. Gleicher
  `SESSION_DOMAIN` (führender Punkt) + gleicher `APP_KEY` + gemeinsame `sessions`-Tabelle
  in Postgres — **nur** für diese beiden Apps.
- **`apps/website`**: überwiegend anonym, eigener `APP_KEY`/eigene Session falls
  überhaupt ein Login vorkommt.

## Session (Portal/Shop)

`SESSION_DOMAIN=.dormed.test` (führender Punkt) hält eine Session über Portal + Shop. In
`phpunit.xml` je App `SESSION_DOMAIN=null` — Tests laufen gegen `localhost`.
