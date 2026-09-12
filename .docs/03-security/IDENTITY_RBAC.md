# Identität & Autorisierung

Autoritative deklarative Spec. Entscheidungen **D-026 – D-033**, revidiert durch
**D-124/D-125** ([`../07-decisions/grill-log.md`](../07-decisions/grill-log.md)).
Konkretisiert [`AUTHORIZATION.md`](AUTHORIZATION.md).
Navigations-/Cockpit-Wirkung: [`../09-ui/NAVIGATION.md`](../09-ui/NAVIGATION.md).

## Grundsatz

- **Mitarbeiter = User** (ein Modell, D-026). Kein separates `Employee`.
- **Zielbild: SSO-only über Microsoft Entra ID** (D-027) — kommt als eigener
  ROADMAP-Slice „oben drauf".
- **Interim** (bis SSO): selbstverwaltete `users` mit lokaler Auth, geschlossen
  (keine Registrierung, kein Self-Service-Passwort-Reset, D-032).
- Autorisierung: **Rollen → Permission-Katalog** (handgerollt, kein Package, D-030).
  Permissions stecken **im Code** (`config/authorization.php`), nicht im UI (D-125).
- **Genau eine Rolle je Mitarbeiter** (D-124, revidiert D-031).
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
| `role_id` | FK → `roles` | – | **NOT NULL (D-124)** — genau eine Rolle je Mitarbeiter |
| `last_login_at` | datetime | ✓ | |

`SoftDeletes` (D-018). **Kein** Personalnummer/Kostenstelle/HR-Datum (D-033). **Kein**
`name`-Feld — `getNameAttribute()`-Accessor (`first_name . ' ' . last_name`), keine
Spalte (D-093; ursprünglicher Breeze-Kompat-Grund entfällt mit ADR-023/Fortify).

**Beziehungen:** `role()` `belongsTo` (D-124, war `belongsToMany`).

## `roles`

`roles`: `key` (string, unique), `name` (string), `is_active` (bool).

> **Revidiert (D-124).** Der Pivot `role_user` und das Feld `is_primary`
> **entfallen ersatzlos**. Jeder aktive User hat **genau eine** Rolle über
> `users.role_id`. Nutzer: „anpassen auf eine Rolle pro Mitarbeiter, das ist nach
> heutigem Stand falsch mit mehreren Rollen."

**Seed (D-125, revidiert D-031)** — genau diese 5, hart definiert ohne Dynamik:

| key | Abteilung | Zweck |
| --- | --- | --- |
| `geschaeftsfuehrung` | Geschäftsführung | **Vollzugriff** (`['*']` über Katalog, **nicht** `is_admin`) |
| `management` | Management | operative Leitungsebene: fachlicher Vollzugriff, **ohne** Systemadministration und ohne sensible Auswertungen |
| `backoffice` | Backoffice | Stammdaten, **Warenwirtschaft**, **Billing**, Papierkorb (D-023) |
| `sales` | Vertrieb | Companies, Kontakte, Verkaufschancen |
| `service` | Service | Companies (lesen), Servicefälle, Wartungen, Termine |

**Gestrichen gegenüber D-031 (ersatzlos, D-125):**

- **`accounting`** → `billing.*` wandert zu `backoffice`. Betrifft auch **D-113**:
  der Inventur-Zählauftrag fürs Zentrallager, dort der „Buchhaltung" zugewiesen,
  geht künftig an `backoffice`.
- **`it`** → ersetzt durch den `is_admin`-Bootstrap-Bypass (D-028).

Service-Split (Innendienst/Außendienst-Techniker) = mögliche spätere Verfeinerung.

## Permission-Katalog

Strings im Schema **`<modul>.<ressource>.<aktion>`**, z. B. `crm.companies.update`,
`service.cases.close`, `platform.records.trash.manage`.

- Jedes Modul steuert seine Permissions zum Katalog bei (`config/authorization.php`
  → `permissions`, plus je Modul eine Teil-Liste).
- **Rolle → Permissions**-Map in `config/authorization.php` → `roles`
  (Zwischenstand, **die genauen Permissions je Abteilung sind offen**, D-125):
  - `geschaeftsfuehrung` → `['*']`
  - `management` → fachlich weitgehend `*`, ohne `platform.*`-Administration
  - `backoffice` → Stammdaten-`*`, `inventory.*`, `billing.*`,
    `platform.records.trash.manage`
  - `sales` → `crm.companies.*`, `crm.contacts.*`, `crm.people.*`, `sales.*`
  - `service` → `crm.companies.view`, `crm.people.view`, `service.*`,
    `scheduling.*`, plus **fein geschnittene** Inventory-Rechte für den eigenen
    Bestand und den eigenen Zählauftrag (D-127) — **nicht** `inventory.*`
- Der Katalog wächst mit jedem Modul-Slice; die Map wird dort ergänzt.
- **Die Navigation wird aus genau diesem Katalog abgeleitet** (D-123) — es gibt
  keine zweite Rolle→Menü-Konfiguration. Siehe
  [`../09-ui/NAVIGATION.md`](../09-ui/NAVIGATION.md).

## Durchsetzung

- `App\Support\PermissionService::can(User $user, string $ability): bool`
  — `true` wenn **die** Rolle des Users `$ability` (oder `*` / Präfix-Wildcard) gewährt (D-124).
- `Gate::before(fn (User $u) => $u->is_admin ?: null)` — nur der Bootstrap-Bypass.
- Alle Policies rufen `$user->can('<ability>')` bzw. Gate; **kein** direkter
  Rollen-Check in Policies (Rollen können sich ändern, Abilities sind stabil).
- `responsible_*_id` (D-016) → `users.id`, Auswahl = aktive User; **rein
  informativ, keine AuthZ**.
- Papierkorb-Zugriff (D-023) = Permission `platform.records.trash.manage`
  (Rollen `geschaeftsfuehrung`, `management`, `backoffice` — D-125).

## Interim-Auth (bis SSO)

- **Login** bleibt (Breeze `AuthenticatedSessionController`).
- **Entfernen:** `register`, `password.request/email/reset/store`,
  `verification.*` (kein Self-Service, D-032). Routen aus `routes/auth.php` /
  `web.php` streichen, zugehörige Controller/Views mit.
- **User-Anlage** = Admin-Funktion: `first_name`, `last_name`, `email`, **Rolle**
  (genau eine, D-124) → signierte Einladungs-Mail „Passwort setzen".
- Passwort-Reset durch einen Admin über dieselbe Einladungs-/Reset-Aktion.

## SSO (später, D-029) — additiv

- OIDC gegen Entra (Single-Tenant), `league/oauth2-client` + Azure-Provider.
- Callback: `entra_oid` upsert; `roles`-Claim (aus **Entra App Roles**) → `users.role_id`.
  **Achtung (D-124):** das Mapping ist jetzt **einwertig** — mehrere App-Rollen für
  einen Nutzer sind ein **Fehlerfall**, kein Normalfall. Vor dem SSO-Slice zu klären.
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
| 2a | **Genaue Permissions je Abteilung** — Nutzer: „müssen später nochmal definiert werden" | eigene Runde (D-125) |
| 2b | Abgrenzung Geschäftsführung ↔ Management: was genau ist „sensible Auswertung"? | mit 2a (D-125) |
| 3 | Alle 8 Entra-Azure-Admin-Fragen | vor SSO-Slice |
| 4 | Audit-Ausbau (vorher/nachher, Löschgrund — `SECURITY.md`) | eigener Plattform-Punkt |
