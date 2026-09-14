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
- **Keine Passkeys** — ersatzlos gestrichen, auf beiden Seiten (ADR-043).
- **TOTP-2FA optional, nie Pflicht** (ADR-043). Für Mitarbeiter bewusst als
  Wegwerf-Flanke: sobald Entra-SSO übernimmt (D-029), macht Microsoft MFA zentral per
  Richtlinie. Sie wird mitgenommen, weil Fortify sie geschenkt mitbringt — es wird
  nichts hineininvestiert.
- Autorisierung: **Rollen → Permission-Katalog** (handgerollt, kein Package, D-030).
  Permissions stecken **im Code** (`config/authorization.php`), nicht im UI (D-125).
- **Genau eine Rolle je Mitarbeiter** (D-124, revidiert D-031).
- **Kunden** (Portal/Shop) sind **nicht** hier — eigene Tabelle `customer_accounts`,
  eigenes Model `CustomerAccount`, eigener Guard `customer` (ADR-042). Dieses Dokument
  beschreibt ausschließlich `users` / Guard `staff`. Der Kundenzugang hängt fachlich am
  CRM-Kontakt (`customer_accounts.person_id` → `people`, ADR-037) und wird vom ERP aus
  verwaltet — das Recht dazu ist eine eigene Ability im Vokabular aus D-136.

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

Strings im Schema **`<modul>.<ressource>.<aktion>`** (D-030), z. B.
`crm.companies.update`, `service.cases.close`.

### Aktionen (D-136)

| Aktion | Bedeutung |
| --- | --- |
| `view` · `create` · `update` | Basis |
| `delete` | **Soft Delete** — in den Papierkorb (D-018), **nicht** endgültig |
| `restore` | aus dem Papierkorb zurückholen |
| benannte Workflow-Abilities | jeder Zustandsübergang mit fachlicher Wirkung |

**Workflow-Abilities brauchen eigene Namen**, sonst gewährt ein pauschales `update`
implizit auch „Rechnung stellen" oder „Status auf abgerechnet setzen" — und die dritte
Ebene aus [`AUTHORIZATION.md`](AUTHORIZATION.md) („What may you do **now**?") wäre
ausgehebelt. Beispiele: `service.maintenances.complete` · `billing.invoices.issue` ·
`billing.invoices.cancel` · `sales.opportunities.close` · `inventory.counts.post` ·
`inventory.stock.override_negative` (D-112).

### Matrix je Abteilung (D-137–D-140)

`geschaeftsfuehrung` hat `['*']` und steht deshalb nicht in jeder Zeile.

| Modul / Ressource | backoffice | sales | service | management |
| --- | :-: | :-: | :-: | :-: |
| **crm** — companies, people, contacts, addresses, locations, channels, consents | C·U·V | C·U·V | C·U·V | C·U·V·**D·R** |
| **crm** — medical_specialties (Lookup, D-133) | C·U·V | V | V | C·U·V·D·R |
| **sales** — opportunities, items | V | **C·U·V** | – | C·U·V·D·R |
| **sales** — sales_territories | V | V | – | C·U·V |
| **service** — contracts, maintenances, cases, reports, measurements | V | V | **C·U·V** + Workflow | C·U·V·D·R |
| **service** — checklist_templates | C·U·V | – | V | C·U·V·D·R |
| **service** — service_prices, travel_zones | V | V | V | **C·U·V** (D-140) |
| **service** — service_territories | C·U·V | – | V | C·U·V |
| **scheduling** — appointments | C·U·V | C·U·V | C·U·V | C·U·V·D·R |
| **billing** — invoices, items | **C·U·V** + `issue`/`cancel` | – | – | C·U·V·D·R |
| **billing** — payments, bank_imports | C·U·V | – | – | V |
| **billing** — dunning (Mahnwesen) | C·U·V | – | – | **– (nur GF, D-138)** |
| **inventory** — article_groups, articles, offerings, Feldkatalog | C·U·V **ohne** `sale_price` | V | **V** | C·U·V (inkl. Preise) |
| **inventory** — devices (Exemplare) | C·U·V | V | V | C·U·V·D·R |
| **inventory** — warehouses, transfers, goods_receipts, purchase_orders, suppliers | C·U·V | – | **–** | C·U·V·D·R |
| **inventory** — stock | V (alle Läger) | – | **nur `.own`** (D-137) | V |
| **inventory** — counts (Inventur) | C·U·V + `post` | – | **nur `submit.own`** | C·U·V + `post` |
| **inventory** — pickup_notes | C·U·V | – | **`create`** (D-130/D-137) | C·U·V |
| **inventory** — reservations, returns | C·U·V | V | V | C·U·V·D·R |
| **communication** — contact_requests | **C·U·V** (D-132) | – | – | **C·U·V** (D-132) |
| **identity** — users, roles | – | – | – | **C·U·V** (D-138) |

`C` create · `U` update · `V` view · `D` delete (Papierkorb) · `R` restore

### Löschmodell (D-139) — drei getrennte Ebenen

| Ebene | Wer |
| --- | --- |
| anlegen / ändern | alle fünf Rollen (je nach Ressource oben) |
| **löschen / wiederherstellen** (Papierkorb, D-018) | **nur** `management` · `geschaeftsfuehrung` |
| **endgültig entfernen** | **niemand manuell** — `model:prune` nach **30 Tagen** (Frist seit D-023 fix) |

> **Revidiert D-023 und D-125 in zwei Punkten:** Backoffice darf **nicht** mehr
> löschen, und `forceDelete` wird **niemandem** mehr als Ability erteilt. Der
> Prune-Job und die 30-Tage-Frist selbst standen schon in D-023 — neu ist nur, dass
> es daneben keinen manuellen Weg mehr gibt. Eine „endgültig löschen"-Schaltfläche
> wäre die einzige Stelle im System, an der ein Klick Daten unwiederbringlich
> vernichtet.
>
> **Ausgenommen vom Job:** rechtlich aufbewahrungspflichtige Belege, mindestens
> `Invoice` samt Positionen (GoBD, D-093) und alles daran Hängende. Ein Job, der das
> nicht respektiert, wäre ein Rechtsverstoß per Cron.

### Preishoheit (D-140)

Schreibrechte auf `service_prices`, `travel_zones` und `Article.sale_price` /
`Offering.sale_price` haben **nur** `geschaeftsfuehrung` und `management`. Backoffice
legt Artikel an und pflegt sie, **bepreist** sie aber nicht.

`discount_percent` an der Angebotsposition (D-052) ist davon **unberührt** — das ist
kein Preisstamm, sondern Teil des Angebots, und gehört zu `sales.*`.

### Geschäftsführung vs. Management (D-138)

Einzige Einschränkung für `management`: **Mahnwesen und Zahlungsverhalten**
(`billing.dunning.*`, offene Posten und Verzug je Kunde) sind GF-exklusiv.

Einkaufspreise und Deckungsbeitrag sind für Management **frei** — wer Verkaufspreise
setzt (D-140), muss den Einstandspreis kennen, sonst bietet er unbemerkt unter Kosten
an. Und der Deckungsbeitrag ist nach D-052 aus `unit_price` und `unit_cost`
**abgeleitet**: wer beide sieht, kennt die Marge ohnehin — ihn zu verbergen wäre
Schein-Sicherheit.

### Navigation

**Die Seitenleiste wird aus genau diesem Katalog abgeleitet** (D-123) — es gibt keine
zweite Rolle→Menü-Konfiguration. Siehe [`../09-ui/NAVIGATION.md`](../09-ui/NAVIGATION.md).

## Durchsetzung

- `App\Support\PermissionService::can(User $user, string $ability): bool`
  — `true` wenn **die** Rolle des Users `$ability` (oder `*` / Präfix-Wildcard) gewährt (D-124).
- `Gate::before(fn (User $u) => $u->is_admin ?: null)` — nur der Bootstrap-Bypass.
- Alle Policies rufen `$user->can('<ability>')` bzw. Gate; **kein** direkter
  Rollen-Check in Policies (Rollen können sich ändern, Abilities sind stabil).
- `responsible_*_id` (D-016) → `users.id`, Auswahl = aktive User; **rein
  informativ, keine AuthZ**.
- **Papierkorb (D-139, revidiert D-023):** `delete`/`restore` je Ressource, **nur**
  `management` und `geschaeftsfuehrung`. Die Ability
  `platform.records.trash.manage` zum endgültigen Entfernen **entfällt** — das macht
  ein Scheduler-Job, kein Mensch.

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
- Nächtlicher Microsoft-Graph-Sync einer Gruppe „ERP-Users" für Vor-Provisionierung
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
| 2a | ~~Genaue Permissions je Abteilung~~ | ✅ gelöst — Matrix oben (D-136–D-140) |
| 2b | ~~Abgrenzung Geschäftsführung ↔ Management~~ | ✅ gelöst — nur Mahnwesen ist GF-exklusiv (D-138) |
| 2c | **Ausnahmeliste des Prune-Jobs** (D-139) — welche Entitäten neben `Invoice` sind aufbewahrungspflichtig? | bei Umsetzung, mit Steuerberater (analog `BILLING.md` #2) |
| 3 | Alle 8 Entra-Azure-Admin-Fragen | vor SSO-Slice |
| 4 | Audit-Ausbau (vorher/nachher, Löschgrund — `SECURITY.md`) | eigener Plattform-Punkt |
