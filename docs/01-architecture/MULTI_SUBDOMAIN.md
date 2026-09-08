# Multi-Subdomain-Architektur

## Ziel

Eine Laravel-Anwendung soll mehrere Subdomains bedienen:

- CRM
- Customer Portal
- Shop

Alle Subdomains teilen sich:

- Laravel-Code
- PostgreSQL
- Authentifizierung
- gemeinsame Assets
- UI-Komponenten
- technische Services

## Wichtig

Subdomains sind keine Sicherheitsgrenze.

Ein Request auf `portal.*` darf nur deshalb keine CRM-Funktion erhalten, weil eine Route nicht registriert wurde. Autorisierung muss zusätzlich fachlich erfolgen.

## Request-Kontext

Der Hostname wird früh im Request verarbeitet.

Implementiert als:

- `App\Support\ApplicationContext` — Enum `Crm | Portal | Shop`, plus
  `tryFromHost()`, `host()`, `label()`.
- `App\Http\Middleware\ResolveApplicationContext` — an die `web`-Middleware-Gruppe
  angehängt (`bootstrap/app.php`). Löst den Kontext aus `$request->getHost()`,
  bindet ihn als Container-Instanz und teilt ihn allen Views als
  `$applicationContext` mit. Unbekannter Host ⇒ `null` (kein Fehler).

Die Host-Namen stehen zentral in `config/domains.php` (`env(DOMAIN_CRM|DOMAIN_PORTAL|DOMAIN_SHOP)`).

## Routing

Routen sind nach Kontext gruppiert:

```text
routes/
├── web.php      # host-unabhängig: Auth (auth.php), Profil, /dashboard
├── crm.php      # nur config('domains.crm')
├── portal.php   # nur config('domains.portal')
└── shop.php     # nur config('domains.shop')
```

Registrierung in `bootstrap/app.php` via `withRouting(then: ...)`: pro Kontext eine
Gruppe mit `->domain(config("domains.$ctx"))`, `web`-Middleware und `$ctx.`-Namensprefix
(`route('crm.home')` ⇒ `http://crm.dormed.test`). Danach eine host-freie
`welcome`-Fallback-Route für unbekannte Hosts.

Wichtig: `web.php` wird vor `then:` registriert – dort darf keine URI liegen, die
eine Kontext-Route mit gleichem Pfad beschattet (`/` wurde deshalb aus `web.php` entfernt).

## Gemeinsame Komponenten

Bevorzugt:

```text
resources/
├── js/components
├── js/layouts
├── js/shared
└── views/components
```

Fachliche UI:

```text
resources/
├── js/crm
├── js/portal
└── js/shop
```

## Authentifizierung

Eine Identität kann perspektivisch mehrere Anwendungskontexte nutzen.

Beispiel:

```text
User
 ├── Employee
 └── CustomerContact
```

Das ist eine fachliche Zuordnung, kein Grund für getrennte Auth-Systeme.

## Lokale Entwicklung

Basis-Domain lokal: `dormed.test` (reserviert, kein echtes DNS). In `/etc/hosts`:

```text
127.0.0.1 dormed.test crm.dormed.test portal.dormed.test shop.dormed.test
```

Der `app`-Container (`compose.yaml`) läuft mit `php artisan serve --host=0.0.0.0`
und akzeptiert jeden Host-Header. Domain-Matching ignoriert den Port, daher
funktioniert `:8000` transparent.

## Cookies / Sessions

Geteilte Session über alle Subdomains ist aktiv konfiguriert:

- `SESSION_DOMAIN=.dormed.test` (führender Punkt ⇒ ein Cookie für alle `*.dormed.test`).
- `SESSION_SAME_SITE=lax` genügt – die Subdomains sind same-site.
- `login` / `register` / `logout` liegen in `routes/auth.php` (via `web.php`) ohne
  Domain-Constraint, gelten also für jeden Kontext.
- In Tests ist `SESSION_DOMAIN` in `phpunit.xml` auf `null` gesetzt (Requests laufen
  gegen `localhost`).

Prod: `SESSION_DOMAIN` auf die echte Basis-Domain setzen, `SESSION_SECURE_COOKIE=true`.

## Do not

- Keine drei Laravel-Projekte.
- Keine drei `vendor/`-Installationen.
- Keine getrennten Datenbanken pro Subdomain.
- Keine Geschäftslogik im DNS/Webserver.
- Keine Berechtigung ausschließlich anhand des Hostnamens.
