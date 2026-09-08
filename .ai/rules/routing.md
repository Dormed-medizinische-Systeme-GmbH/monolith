---
paths:
  - routes/**
  - bootstrap/app.php
  - app/Http/Middleware/**
  - config/domains.php
  - app/Support/ApplicationContext.php
---

# Routing & Application Context

## Multi-Subdomain-Struktur (ADR-003)

Eine Codebasis, drei Kontext-Subdomains: `crm`, `portal`, `shop`. Host-Namen
**ausschließlich** aus `config('domains.<ctx>')` (env `DOMAIN_CRM` / `DOMAIN_PORTAL`
/ `DOMAIN_SHOP`), nie hart kodieren. Werte ohne Port – Route-Host-Matching nutzt
`Request::getHost()`.

- Kontext-Routen gehören in `routes/<ctx>.php`. Registrierung zentral in
  `bootstrap/app.php` über `withRouting(then: ...)`: Schleife über die drei
  Kontexte, je `Route::middleware('web')->domain(config("domains.$ctx"))->as("$ctx.")->group(...)`.
- `routes/web.php` ist host-unabhängig (Auth via `routes/auth.php`, Profil,
  `/dashboard`). Es wird **vor** `then:` registriert – dort darf keine URI liegen,
  die eine Kontext-Route mit gleichem Pfad beschattet. Deshalb kein `/` in `web.php`;
  die `welcome`-Fallback-Route steht am Ende des `then:`-Callbacks.
- Neue geteilte, für jeden Kontext geltende Routen ⇒ `web.php` / `auth.php`.
  Kontextspezifisch ⇒ die passende `routes/<ctx>.php`.

## Application Context

`App\Support\ApplicationContext` (Enum) + `App\Http\Middleware\ResolveApplicationContext`
(an `web`-Gruppe angehängt) lösen den Kontext aus dem Host, binden ihn im Container
und teilen ihn als `$applicationContext` an alle Views. Unbekannter Host ⇒ `null`.

## Subdomain ist KEINE Sicherheitsgrenze

Dass eine Route auf `portal.*` nicht registriert ist, ist kein Autorisierungsersatz.
Jede Aktion braucht zusätzlich eine fachliche Prüfung (Policy / Gate / Query-Scope).
Kein Vertrauen auf den Hostnamen für Berechtigungen.

## Session

`SESSION_DOMAIN=.dormed.test` (führender Punkt) hält eine Session über alle
Subdomains. In `phpunit.xml` ist `SESSION_DOMAIN=null` – Tests laufen gegen
`localhost`; HTTP-Tests für Kontext-Routen mit vollständiger URL aufrufen
(`$this->get('http://crm.dormed.test/')`).
