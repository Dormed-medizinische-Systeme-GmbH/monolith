---
paths:
  - routes/**
  - bootstrap/app.php
  - app/Http/Middleware/**
  - config/database.php
---

# Routing, Zugriffspunkte und Datenbankverbindungen

> Kurswechsel 2026-09-14 (ADR-033, ADR-036, ADR-038). **Eine** Anwendung bedient alle
> vier Hostnames. Details: `.docs/01-architecture/MULTI_SUBDOMAIN.md`.

## Vier Hostnames, eine Anwendung

| Host | Route-Datei | DB-Rolle | Frontend |
| --- | --- | --- | --- |
| `dormed.de` | `routes/website.php` | `dormed_public` | Blade |
| `shop.dormed.de` | `routes/shop.php` | `dormed_public` → `dormed_customer` | Inertia/Svelte |
| `erp.dormed.de` | `routes/erp.php` | `dormed_staff` | Inertia/Svelte |
| `my.dormed.de` | `routes/portal.php` | `dormed_customer` | Inertia/Svelte |

Jede Route-Datei wird mit `->domain(...)` registriert, die Domain kommt aus der
Konfiguration — **nie als Literal** in die Route-Datei, sonst weichen Dev, Staging und
Prod voneinander ab.

> **Domains OHNE Port konfigurieren**, auch wenn der Dev-Stack auf `:8000` läuft.
> Laravels `HostValidator` matcht gegen `Request::getHost()`, und das schneidet den Port
> ab. Mit Port in `APP_DOMAIN_*` trifft keine einzige Route — 404 auf allen vier Hosts,
> ohne jede Fehlermeldung.

## Die Verbindung gehört an die Route-Group

Middleware auf der Group setzt die Verbindung, und bei Kunden zusätzlich
`app.company_id` aus der Session. **Nie ein `$connection` auf einem Model** — die
Grenze hängt am Zugriffspunkt, nicht am Datensatz.

Die Kunden-Policy ist **fail-closed**: ist `app.company_id` nicht gesetzt, liefert
`current_setting(..., true)` `NULL` und der Vergleich nie `true` — null Zeilen statt
aller. Vergisst die Middleware ihre Aufgabe, bricht das Portal, es leakt nicht.

**Die Variable muss bei jedem neuen Verbindungsaufbau gesetzt werden**, auch bei einem
Reconnect mitten im Request — nicht einmalig im Middleware-Aufruf.

> **Kein PgBouncer im Transaction-Mode** ohne Umbau auf `set_config(..., true)` in einer
> expliziten Transaktion. Eine sitzungsweite Variable überlebt den Verbindungswechsel
> dort nicht. Bei der Last aus ADR-033 wird kein Pooler gebraucht.

## Login (ADR-037)

Zwei Guards, zwei Tabellen, zwei Models (ADR-042) — **nie vermischen**:

| Guard | Tabelle | Model | Wo |
| --- | --- | --- | --- |
| `staff` | `users` | `User` | `erp.` |
| `customer` | `customer_accounts` | `CustomerAccount` | `my.` + `shop.` |

- **Immer den Guard explizit nennen** (`Auth::guard('customer')`). Der Standard-Guard
  heißt `staff`, nicht `web`, damit ein `auth()` ohne Argument nicht stillschweigend auf
  die Mitarbeiterseite fällt.
- **Jeder Zugriffspunkt benennt seine Verbindung ausdrücklich**, auch das ERP
  (`UseStaffConnection`), obwohl dessen Rolle der Standard ist. Der Standard ist in Tests,
  Artisan-Commands und Queue-Jobs jeweils ein anderer — eine geerbte Rolle wechselt lautlos
  mit dem Kontext.
- **Fortify bedient beide Guards über eine Installation** (ADR-043): `ignoreRoutes()`,
  Controller je Domain-Group, Middleware setzt `fortify.guard`/`fortify.passwords`.
  Diese Umschaltung ist **globaler Zustand pro Request** — sie gehört in die
  **äußerste** Middleware der Group. Was davor aufgelöst wird (andere Middleware,
  Route-Model-Binding, Event-Listener), sieht noch den alten Guard.
- **Keine Passkeys** (ADR-043). TOTP-2FA auf beiden Guards optional, nie erzwungen.
- **Kunde:** ein Zugang für Portal **und** Shop, fachlich am CRM-Kontakt
  (`customer_accounts.person_id` → `people`). Eine Anwendung, eine Session —
  `SESSION_DOMAIN` mit führendem Punkt, damit das Cookie über `my.` und `shop.` gilt.
  Der Cross-App-Mechanismus aus ADR-016 (geteilter `APP_KEY`, geteilte `sessions`-Tabelle)
  entfällt ersatzlos.
- **`TracksBlame` zeigt auf `users`** und bedeutet damit immer „welcher Mitarbeiter".
  Was ein Kunde erzeugt, referenziert `people` — nicht `users`.
- **`dormed.de`:** anonym, kein Login.

In `phpunit.xml`: `SESSION_DOMAIN=null`, Tests laufen gegen `localhost`.

## Zugriffspunkt ist KEINE Sicherheitsgrenze

Dass eine Route nur unter `erp.dormed.de` existiert, ist kein Autorisierungsersatz. Jede
Aktion braucht zusätzlich Policy / Gate / Query-Scope.

Die **Datenbankrolle** ist demgegenüber eine echte Grenze — sie schützt aber vor dem
falschen *Zugriffspunkt*, nicht vor der falschen *Abteilung*. Beides wird gebraucht.

## Do not

- Keine Geschäftslogik im DNS, Webserver oder Proxy.
- Keine Berechtigung anhand des Hostnamens.
- Kein `->domain()` mit hartkodierter Domain.
- Keine Anwendungsverbindung als `dormed_owner`.
