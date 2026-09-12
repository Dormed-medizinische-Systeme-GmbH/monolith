# Identität & Autorisierung

Autoritative deklarative Spec. Entscheidungen **D-026 – D-033**
([`../07-decisions/grill-log.md`](../07-decisions/grill-log.md)). Konkretisiert
[`AUTHORIZATION.md`](AUTHORIZATION.md).

## Grundsatz

- **Mitarbeiter = User** (ein Modell, D-026). Kein separates `Employee`.
- **Zielbild: SSO-only über Microsoft Entra ID** (D-027) — kommt als eigener
  ROADMAP-Slice „oben drauf".
- **Interim** (bis SSO): selbstverwaltete `users` mit lokaler Auth, geschlossen
  (keine Registrierung, kein Self-Service-Passwort-Reset, D-032).
- Autorisierung: **Rollen → Permission-Katalog** (handgerollt, kein Package, D-030).
- **Kunden** (Portal/Shop) sind **nicht** hier — eigener Auth-Pfad, Bereich Portal.

## `users`

| Feld | Typ | Null | Notiz |
| --- | --- | :-: | --- |
| `first_name` | string | – | |
| `last_name` | string | – | |
| `email` | string | – | unique, Login |
| `password` | string | ✓ | nullable (später SSO-User ohne Passwort) |
| `entra_oid` | string | ✓ | unique — **jetzt reserviert**, von SSO (D-029) befüllt |
| `is_admin` | boolean | – | default `false` — Bootstrap/IT-Bypass (D-028) |
| `is_active` | boolean | – | default `true` — inaktiv ⇒ kein Login, aus `responsible_*` ausgeblendet |
| `last_login_at` | datetime | ✓ | |

`SoftDeletes` (D-018). **Kein** Personalnummer/Kostenstelle/HR-Datum (D-033). **Kein**
`name`-Feld — `getNameAttribute()`-Accessor (`first_name . ' ' . last_name`), keine
Spalte (D-093; ursprünglicher Breeze-Kompat-Grund entfällt mit ADR-023/Fortify).

**Beziehungen:** `roles()` `belongsToMany` über `role_user`.

## `roles` + `role_user`

`roles`: `key` (string, unique), `name` (string), `is_active` (bool). Pivot
`role_user` mit `is_primary` (bool — Anzeige-Rolle).

Jeder aktive User hat **≥ 1 Rolle**.

**Seed (D-031)** — genau diese 6, `key`:

| key | Zweck |
| --- | --- |
| `management` | Geschäftsführung — alle Permissions (über Katalog, **nicht** `is_admin`) |
| `backoffice` | Stammdatenpflege, Papierkorb/Löschen (D-023) |
| `sales` | Vertrieb — Companies, Kontakte, Verkaufschancen |
| `service` | Service — Companies (lesen), Servicefälle, Wartungen, Termine |
| `accounting` | Buchhaltung — Rechnungen, Zahlungen, KHK |
| `it` | Technisch/administrativ (nicht = `is_admin`) |

Service-Split (Innendienst/Außendienst-Techniker) = mögliche spätere Verfeinerung.

## Permission-Katalog

Strings im Schema **`<modul>.<ressource>.<aktion>`**, z. B. `crm.companies.update`,
`service.cases.close`, `platform.records.trash.manage`.

- Jedes Modul steuert seine Permissions zum Katalog bei (`config/authorization.php`
  → `permissions`, plus je Modul eine Teil-Liste).
- **Rolle → Permissions**-Map in `config/authorization.php` → `roles`:
  - `management` → `['*']`
  - `backoffice` → Stammdaten-`*` + `platform.records.trash.manage`
  - `sales` → `crm.companies.*`, `crm.contacts.*`, `crm.people.*`, `sales.*`
  - `service` → `crm.companies.view`, `crm.people.view`, `service.*`, `scheduling.*`
  - `accounting` → `billing.*`, `crm.companies.view`, KHK
  - `it` → `platform.*` außer `trash.manage` (o. n. Bedarf)
- Der Katalog wächst mit jedem Modul-Slice; die Map wird dort ergänzt.

## Durchsetzung

- `App\Support\PermissionService::can(User $user, string $ability): bool`
  — `true` wenn eine Rolle des Users `$ability` (oder `*` / Präfix-Wildcard) gewährt.
- `Gate::before(fn (User $u) => $u->is_admin ?: null)` — nur der Bootstrap-Bypass.
- Alle Policies rufen `$user->can('<ability>')` bzw. Gate; **kein** direkter
  Rollen-Check in Policies (Rollen können sich ändern, Abilities sind stabil).
- `responsible_*_id` (D-016) → `users.id`, Auswahl = aktive User; **rein
  informativ, keine AuthZ**.
- Papierkorb-Zugriff (D-023) = Permission `platform.records.trash.manage`
  (Rollen `management`, `backoffice`).

## Interim-Auth (bis SSO)

- **Login** bleibt (Breeze `AuthenticatedSessionController`).
- **Entfernen:** `register`, `password.request/email/reset/store`,
  `verification.*` (kein Self-Service, D-032). Routen aus `routes/auth.php` /
  `web.php` streichen, zugehörige Controller/Views mit.
- **User-Anlage** = Admin-Funktion: `first_name`, `last_name`, `email`, Rollen →
  signierte Einladungs-Mail „Passwort setzen".
- Passwort-Reset durch einen Admin über dieselbe Einladungs-/Reset-Aktion.

## SSO (später, D-029) — additiv

- OIDC gegen Entra (Single-Tenant), `league/oauth2-client` + Azure-Provider.
- Callback: `entra_oid` upsert; `roles`-Claim (aus **Entra App Roles**) → `role_user` sync.
- Nächtlicher Microsoft-Graph-Sync einer Gruppe „CRM-Users" für Vor-Provisionierung
  (`responsible_*`-Dropdowns) und Auto-Deaktivierung bei Austritt.
- `password`/Login-Form entfallen dann für Mitarbeiter.
- 8 offene Azure-Admin-Punkte im `grill-log.md` (Abschnitt „Entra-Integration").

## Business-Workflow (dritte Ebene)

`AUTHORIZATION.md`: „What may you do **now**?" — Zustandsautomaten-Guards
(z. B. Wartung darf nicht von `fällig` direkt nach `abgerechnet`). Das ist **nicht**
RBAC, sondern pro Modul in der jeweiligen State-Machine. Server-seitig erzwungen,
UI bietet nur gültige nächste Aktionen an.

## Offene Punkte

| # | Punkt | Wohin |
| --- | --- | --- |
| 1 | Konkrete Permission-Strings je Modul | mit jedem Modul-Slice |
| 2 | Service innen/außen splitten? | bei Service-Spec |
| 3 | Alle 8 Entra-Azure-Admin-Fragen | vor SSO-Slice |
| 4 | Audit-Ausbau (vorher/nachher, Löschgrund — `SECURITY.md`) | eigener Plattform-Punkt |
