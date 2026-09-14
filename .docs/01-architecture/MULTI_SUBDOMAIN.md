# Multi-Subdomain-Routing

> **Kurswechsel 2026-09-14** (ADR-033, ADR-038). Vier eigenständige Laravel-Projekte sind
> aufgehoben. **Eine** Anwendung bedient alle vier Hostnames über Domain-Routing — der
> Ansatz aus ADR-003, der der Sache nach wieder gilt.

## Vier Hostnames, eine Anwendung

| Host | Route-Datei | Zugang | DB-Rolle (ADR-036) |
| --- | --- | --- | --- |
| `dormed.de` | `routes/website.php` | anonym | `dormed_public` |
| `shop.dormed.de` | `routes/shop.php` | anonym, nach Login Kunde | `dormed_public` → `dormed_customer` |
| `erp.dormed.de` | `routes/erp.php` | Mitarbeiter | `dormed_staff` |
| `my.dormed.de` | `routes/portal.php` | Kunde | `dormed_customer` |

Jede Route-Datei wird mit einem `->domain(...)`-Constraint registriert. Der Host bestimmt
damit dreierlei auf einmal: **Routen**, **Frontend-Stack** (ADR-039) und
**Datenbankverbindung**.

Die Domain kommt aus der Konfiguration, nicht als Literal in die Route-Datei — lokal,
Staging und Produktion unterscheiden sich nur in der `.env`.

## Die Verbindung folgt dem Zugriffspunkt

Das ist der Kern des Entwurfs und der Grund, warum ein Monolith hier sicherer ist als
vier Apps mit einem geteilten Datenbankbenutzer:

```text
Request auf dormed.de   → Middleware setzt pgsql_public
                          → kann Rechnungen nicht lesen, weil keine Grants bestehen

Request auf my.dormed.de → Middleware setzt pgsql_customer + app.company_id
                          → sieht nur Zeilen der eigenen Firma, fail-closed

Request auf erp.        → Standardverbindung dormed_staff
                          → Policies/Gates regeln, was die Abteilung darf
```

**Die Umschaltung gehört an die Route-Group, nicht ans Model.** Dasselbe `Product` wird
je nach Aufrufer über eine andere Rolle gelesen — ein `$connection` auf dem Model würde
die Grenze an den Datensatz hängen statt an den Zugriffspunkt und den Zweck zerstören.

## Login und Session (ADR-037, ergänzt ADR-016)

- **Kunde.** Ein Zugang für Portal **und** Shop über Guard `customer` auf
  `customer_accounts` (ADR-042), fachlich am CRM-Kontakt. Da beide
  Zugriffspunkte jetzt dieselbe Anwendung sind, entfällt der Mechanismus aus ADR-016
  (gleicher `APP_KEY`, geteilte `sessions`-Tabelle über App-Grenzen) **ersatzlos**: es
  gibt eine Session. Nötig ist nur ein `SESSION_DOMAIN` mit führendem Punkt, damit das
  Cookie über `my.` und `shop.` hinweg gilt.
- **Mitarbeiter.** Guard `staff` auf `users` (D-027/D-029, ADR-042). Ein Kunde meldet
  sich nie im ERP an, ein Mitarbeiter nie im Portal.
- **`dormed.de`.** Anonym, kein Login.

Zwei Guards mit zwei Providern sind Standard-Laravel. **Fortify bedient beide** über
eine Installation: `ignoreRoutes()`, Controller je Domain-Group, eine Middleware
schaltet `fortify.guard` um (ADR-043). **Keine Passkeys**, TOTP-2FA beidseitig optional.

Der Mitarbeiter-Login mit E-Mail und Passwort ist eine **Übergangslösung** — Ziel ist
Microsoft-Entra-SSO (D-029). Er existiert, weil SSO etwas braucht, worauf es aufsetzen
kann.

## Subdomain ist KEINE Sicherheitsgrenze

Dass eine Route nur unter `erp.dormed.de` existiert, ist kein Autorisierungsersatz. Jede
fachliche Aktion braucht zusätzlich eine Prüfung (Policy / Gate / Query-Scope). Kein
Vertrauen auf den Hostnamen.

Die **Datenbankrolle** ist demgegenüber eine echte Grenze — aber sie schützt vor dem
falschen *Zugriffspunkt*, nicht vor der falschen *Abteilung*. Beides wird gebraucht.

## Lokale Entwicklung

Basis-Domain `dormed.test` (reserviert, kein echtes DNS) — bedient die Website direkt ohne
Subdomain-Präfix, genau wie `dormed.de` live. In `/etc/hosts`:

```text
127.0.0.1 dormed.test erp.dormed.test my.dormed.test shop.dormed.test
```

Alle vier zeigen auf **denselben** Container und **denselben** Port (80, damit die
Hostnamen ohne Portangabe funktionieren). Details:
[`../../.ai/rules/local-stack.md`](../../.ai/rules/local-stack.md).

## Live-Domains (ADR-021)

`dormed.de` · `shop.dormed.de` · `erp.dormed.de` · `my.dormed.de`.
Staging über Coolify unter `dormed-*.everding.it`.

## Do not

- Keine Rückkehr zu getrennten Anwendungen je Domain (ADR-033).
- Keine Geschäftslogik im DNS, Webserver oder Proxy.
- Keine Berechtigung ausschließlich anhand des Hostnamens.
- Keine Datenbankverbindung, die am Model statt an der Route-Group hängt.
- Kein Zugriff der Anwendung als Schema-Eigentümer — RLS wäre still wirkungslos (ADR-036).
