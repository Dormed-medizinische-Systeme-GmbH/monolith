# Multi-App-Routing (ehem. Multi-Subdomain-Architektur)

> **Kurswechsel 2026-09-12** (ADR-012, ADR-016). Der bisherige Ansatz — eine
> Laravel-Anwendung bedient drei Subdomains über zentralen Host-Dispatch — ist
> **aufgehoben**. Jede Domäne ist jetzt ein eigenständiges Laravel-Projekt. Dieses
> Dokument beschreibt den neuen Zielzustand.

## Ziel

Vier eigenständige Laravel-Projekte bedienen je ihre eigene (Sub-)Domain:

- `apps/website` — öffentlicher Auftritt
- `apps/shop` — E-Commerce
- `apps/crm` — internes Vertriebs-/Kundenmanagement
- `apps/portal` — Kundenportal

Jede App hat ihr **eigenes** Routing (`apps/<app>/routes/web.php`, keine
Domain-Constraint nötig — die App bedient nur ihre eine Domain). Es gibt **kein**
zentrales `config('domains.<ctx>')` + `App\Support\ApplicationContext` +
`ResolveApplicationContext`-Middleware mehr — das war eine Lösung für „eine Codebasis,
mehrere Hosts" und entfällt, weil es jetzt eine Codebasis **pro** Host/App gibt
(ADR-012).

## Live-Domains (ADR-021)

| App | Live-Domain | Status |
| --- | --- | --- |
| `website` | `dormed.de` | bestehende Blade-Frontpage |
| `portal` | `my.dormed.de` | größtenteils bereits vorhanden |
| `shop` | `shop.dormed.de` | bereits live, separates Repo, wird hereinmigriert |
| `crm` | `crm.dormed.de` | bereits aktiv |

Staging/Test läuft weiterhin über Coolify unter `dormed-{crm,portal,shop}.everding.it`
(`.ai/rules/local-stack.md`) — die `dormed.de`-Domains sind das Produktions-Ziel.

## Was weiterhin geteilt wird

- `packages/core`-Code (Models, Migrations, Domain-Services — ADR-011/013)
- Eine PostgreSQL-Instanz
- **Nicht mehr automatisch geteilt:** Session/Login. Siehe unten.

## Login/Session über App-Grenzen (ADR-016)

Die vier Apps haben **keinen** einheitlichen Login. Konkret:

- **`apps/crm`** (Mitarbeiter): eigene, komplett getrennte Session/Nutzertabelle
  (`D-027/D-029`, `../03-security/IDENTITY_RBAC.md`). Kein Zugriff auf Kunden-Sessions.
- **`apps/portal`** + **`apps/shop`**: teilen sich **einen** Kundenlogin — derselbe
  Kunde bewegt sich mit einem Account/einer Session in beiden Apps.
- **`apps/website`**: überwiegend anonym, kein Login-Zwang.

### Mechanismus Portal ↔ Shop (Vorschlag, bei Umsetzung zu bestätigen)

- Gleicher `SESSION_DOMAIN` (führender Punkt, z. B. `.dormed.test` / `.everding.it`) für
  **nur** diese beiden Apps.
- Gleicher `APP_KEY` zwischen `apps/portal` und `apps/shop` (nötig, damit ein
  verschlüsseltes Session-Cookie von der jeweils anderen App entschlüsselt werden kann).
- `SESSION_DRIVER=database`, beide Apps schreiben/lesen dieselbe `sessions`-Tabelle in
  der einen gemeinsamen Postgres-Instanz.
- `apps/crm` und `apps/website` bekommen einen **eigenen** `APP_KEY` und eine **eigene**
  Session — kein Cross-App-Cookie mit Portal/Shop.

Das ist die kleinste Lösung, die ohne einen zusätzlichen zentralen Auth-Service
funktioniert. Ein SSO-Layer zwischen Portal/Shop (analog zum späteren Entra-SSO für CRM,
`D-029`) ist ein möglicher späterer Ausbau, aber kein aktueller Bedarf.

## Subdomain ist weiterhin KEINE Sicherheitsgrenze

Auch wenn jede App jetzt technisch isoliert läuft: eine fachliche Aktion braucht immer
zusätzlich eine Autorisierungsprüfung (Policy/Gate/Query-Scope) innerhalb der App bzw. im
aufgerufenen Core-Modul. Kein Vertrauen auf „das läuft ja eh nur in `apps/crm`".

## Lokale Entwicklung

Basis-Domain lokal weiterhin `dormed.test` (reserviert, kein echtes DNS). In `/etc/hosts`:

```text
127.0.0.1 crm.dormed.test portal.dormed.test shop.dormed.test website.dormed.test
```

Jede App läuft in ihrem eigenen Container (siehe `compose.yaml`,
`../06-infrastructure/DOCKER.md`) und akzeptiert nur ihren eigenen Host — kein
host-generischer `php artisan serve`-Container mehr, der für alle drei zuständig ist.

## Cookies / Sessions — Details

- `SESSION_DOMAIN` für Portal/Shop: `.dormed.test` (Dev) / echte Basis-Domain (Prod),
  `SESSION_SECURE_COOKIE=true` in Prod.
- `apps/crm`, `apps/website`: eigener `SESSION_DOMAIN` (kann identisch mit der jeweiligen
  App-Domain sein, kein führender Punkt nötig, da kein Cross-App-Sharing).
- In Tests: `SESSION_DOMAIN=null` je App, HTTP-Tests laufen gegen `localhost`.

## Do not

- Keine Rückkehr zu „eine Laravel-Codebasis, drei/vier Subdomains" (das war ADR-003,
  jetzt superseded).
- Kein Cross-App-Cookie zwischen `crm`/`website` und Portal/Shop.
- Keine getrennten Datenbanken pro App (weiterhin eine Postgres-Instanz, ADR-011).
- Keine Geschäftslogik im DNS/Webserver/Proxy.
- Keine Berechtigung ausschließlich anhand des Hostnamens oder der App-Zugehörigkeit.
