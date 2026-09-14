# Implementierungsreihenfolge

> **Kurswechsel 2026-09-14** (ADR-033–ADR-041). Ersetzt die Vier-App-Reihenfolge
> vollständig. Ziel dieses Dokuments: ein frischer Agent kann jede Phase **ohne
> Rückfrage** abarbeiten, weil Struktur (`.docs/01-architecture/*`) und Fachlogik
> (`.docs/04-domain/*`, `grill-log.md`) bereits vollständig deklariert sind.

## Phase 0 — Status quo (erledigt)

- **Architektur beschlossen:** ein Monolith, eine Datenbank, vier Zugriffspunkte über
  Domain-Routing (ADR-033/038). Kein `core`-Package, keine App-Ordner.
- **Laravel-Template initialisiert** im Wurzelverzeichnis: Laravel 13, Inertia 3 +
  Svelte 5, Fortify, Wayfinder, Pest 5, Boost 2.2. Unverändert — die Architektur ist
  noch nicht hineingebaut.
- **Altprojekte** liegen als Referenz unter `.legacy/`, ungetrackt (ADR-035/040).
- **Dev-Stack läuft:** `docker-compose.yaml` mit flüchtigem Postgres und MinIO
  (ADR-041).
- **Fachlich spezifiziert** (autoritativ, keine offenen Grundsatzfragen):
  `CORE.md` (D-001–025, D-078, D-092), `IDENTITY_RBAC.md` (D-026–033),
  `SERVICE.md` (D-034–045, D-059–065, D-079–085, D-091), `SCHEDULING.md`
  (D-046–050, D-088, D-090), `SALES.md` (D-051–055, D-086–087),
  `BILLING.md` (D-056–077), `INVENTORY.md` (D-098–121, D-131), Documents (D-141–145).

## Phase 1 — Doku-Durchgang (aktuell)

**Bevor das Template angefasst wird**, gehen Spezifikation und Entscheidungen iterativ
durch — die Architektur hat sich zweimal gedreht, und die Fachdokumente sollen den
Endstand tragen, nicht die Zwischenstufen.

Offene Punkte, die dabei zu schließen sind, stehen in §„Offene Entscheidungen" unten.

## Phase 2 — Fundament in der Anwendung (gebaut 2026-09-14)

**Fertig — fachunabhängiges Gerüst:**

```text
config/domains.php                        vier Hostnames aus der .env
routes/{website,erp,portal,shop}.php      je Zugriffspunkt eine Datei
bootstrap/app.php                         ->domain()-Groups + Verbindungs-Middleware
app/Http/Middleware/UseStaffConnection     dormed_staff
app/Http/Middleware/UsePublicConnection   dormed_public
app/Http/Middleware/UseCustomerConnection dormed_customer + app.company_id
app/Http/Controllers/{Website,Erp,Portal,Shop}/HomeController
app/Support/AccessPoint.php               zeigt Host, Verbindung und DB-Rolle
resources/views/website/home.blade.php    Blade (ADR-039)
resources/js/pages/{erp,portal,shop}/Home.svelte
database/migrations/…_create_database_roles.php
tests/Architecture/                       Rollen-, RLS- und Routing-Beweise
Dockerfile                                Produktions-Image, Konfiguration inline
docker-compose.yaml                   Dev-Stack + Init der Testdatenbank
```

**Verifiziert, nicht behauptet** (2026-09-14): 64 Tests grün gegen PostgreSQL, darunter
25 Architektur-Beweise — Rollen entschärft, `dormed_public` kann `users` nicht lesen,
RLS fail-closed, Kunde A sieht Kunde B nicht, jeder Host trifft seine Rolle.

**Noch offen in dieser Phase:**

### Phase 2 — Reste

1. **Modulstruktur.** `app/Modules/Core/` und `app/Modules/Crm/`, je mit `module.php`.
   Namespace `App\Modules\<Modul>\`.
2. **`tests/Architecture/ModuleBoundariesTest`** — erzwingt `depends_on`, Azyklik und
   „kein `App\Http\…` in `app/Modules/**`". Diese Regel trägt jetzt mehr Gewicht als
   im Package-Modell: Nur der Test hält Domäne und HTTP-Schicht auseinander.
3. **Vier Route-Dateien** mit `->domain(...)` aus der Konfiguration (ADR-038,
   `.ai/rules/routing.md`) — noch ohne Inhalt außer einer Platzhalter-Seite je Host.
4. ~~**Datenbankrollen**~~ — gebaut. `migrate` läuft ab jetzt **immer** mit
   `--database=pgsql_owner`; der Standard `pgsql` zeigt auf `dormed_staff`.

> **Beim Bauen gelernt, in ADR-036 nachgetragen:** `NULLIF(current_setting(…), '')`
> in jeder Policy, sonst wirft ein leerer Kontext einen Fehler statt null Zeilen zu
> liefern. Laravel hat kein `afterConnecting` — die Variable hängt an
> `beforeExecuting` mit PDO-Identitätsvergleich. Und Session, Cache und Queue mussten
> auf `pgsql` festgenagelt werden, sonst folgen sie der Umschaltung mit.

## Phase 3 — Identität und Schema-Kern

1. Modul **`Core`**: `User`, `Role`, `PermissionService` — Felder exakt nach
   `IDENTITY_RBAC.md`. **Kein Pivot `role_user`** (D-124): `users.role_id` als
   NOT-NULL-FK. Rollen-Seed = die fünf aus D-125.
2. Modul **`Crm`**: `Company`, `Person`, `CompanyContact`, `Address`, `Location`,
   `ContactChannel`, `Consent`, `MedicalSpecialty` — Felder exakt nach `CORE.md`.
3. **Kundenzugang** (ADR-037/042): `customer_accounts` mit FK `person_id` auf `people`
   — der Zugang hängt fachlich am Kontakt, liegt aber in einer eigenen schmalen Tabelle.
   Eigenes Model `CustomerAccount`, eigener Guard `customer`.
4. **Fortify** (ADR-023) auf `users`/Guard `staff`: nur Login (D-032). Registrierung,
   Passwort-Reset-Self-Service und E-Mail-Verifizierung deaktiviert.
   Der Kundenanmeldeweg läuft über **dieselbe** Fortify-Installation:
   `Fortify::ignoreRoutes()`, Controller je Domain-Group selbst registrieren, eine
   Middleware setzt `fortify.guard` und `fortify.passwords` (ADR-043).
   Zweiter Passwort-Broker mit eigener Token-Tabelle — in `config/auth.php` nativ.
5. **Passkeys entfernen** (ADR-043): `Features::passkeys()`, Migration, Model-Trait,
   `SecurityController`, `routes/settings.php`, vier Svelte-Komponenten, `auth.ts`.
6. **TOTP-2FA optional auf beiden Guards**, nie erzwungen. Mitarbeiter-2FA ist
   Wegwerfarbeit bis Entra-SSO (D-029) — mitnehmen, nicht polieren.
7. **Standard-Guard heißt `staff`, nicht `web`** — damit ein `auth()` ohne expliziten
   Guard nicht stillschweigend auf die Mitarbeiterseite fällt. Eigener Code nennt den
   Guard **immer** explizit; die Umschaltung sitzt in der äußersten Middleware der
   Domain-Group (ADR-043).

> **Gebaut 2026-09-14:** Schema (11 Tabellen, 11 Models, 8 Enums), beide Guards,
> ERP-Login mit totem SSO-Knopf, Kunden-Login für Portal und Shop, Gate davor.
> 96 Tests grün. Offen in dieser Phase: der Permission-Katalog
> (`config/authorization.php`, D-136/D-137) und die Zugangsverwaltung am
> Kontakt (ADR-037).

## Phase 4 — Seeder als Ausgangsbasis

Der Seeder ist kein Demo-Werkzeug, sondern der Lauf, der in Produktion **einmal** die
Ausgangsbasis herstellt (ADR-041). Weil der Dev-Stack flüchtig ist, läuft er bei jedem
`up` und bleibt dadurch dauerhaft lauffähig.

- **Datenbank- und Object-Storage-Seed sind aufeinander abgestimmt.** Produktbilder und
  Prospekte landen in MinIO/S3, ihre Metadaten in Postgres. Wer das eine ändert, ändert
  das andere mit.
- Bootstrap-Admin (`is_admin`, D-028), die fünf Rollen (D-125), Fachrichtungen
  (D-019/D-133) — idempotent, ein zweiter Lauf ändert nichts.

## Phase 5 — Erster vertikaler Slice: CRM im ERP

`Company` / `Person` / `CompanyContact` / `Address` / `Location` — Liste, Detail,
Anlegen, Bearbeiten als Inertia/Svelte-Views (ADR-039), Autorisierung über
`PermissionService` (D-125/D-136/D-137), Papierkorb (D-139), Audit (`TracksBlame`),
Tests unter `tests/Feature/Erp/`.

Enthält die **Zugangsverwaltung am Kontakt** (ADR-037): Passwort zurücksetzen, sperren,
einladen — als eigene Ability im Vokabular aus D-136.

## Phase 6 — Weitere Module (Reihenfolge)

Alle fachlich spezifiziert, direkt implementierbar:

1. **Scheduling** (`SCHEDULING.md`)
2. **Service** (`SERVICE.md`) — inkl. Technikerflow als PWA im ERP (ADR-032)
3. **Inventory** (`INVENTORY.md`) — Voraussetzung für Shop und Website
4. **Sales** (`SALES.md`)
5. **Billing** (`BILLING.md`)
6. **Documents** (D-141–145)
7. **Communication**

## Phase 7 — Die übrigen Zugriffspunkte

Werden **nachgebaut**, nicht migriert (ADR-018). Der Code unter `.legacy/` ist Vorlage.

1. **`dormed.de`** — **Auftritt portiert (2026-09-14).** 78 Seiten, 4 Blade-Komponenten,
   Kontaktformular mit beiden Mails, Sitemaps, Markdown-Kurzprofile, 352 statische
   Assets. URL-Pfade unverändert. Läuft über `dormed_public` (ADR-036).
   **Offen:** Produktdaten kommen noch aus den Blade-Seiten, nicht aus der Datenbank —
   das ist der eigentliche Inhalt von ADR-038 und setzt Inventory voraus. Dazu gehört
   der Umzug der 28 Prospekt-PDFs (39 MB) von `public/assets/pdf/` in den Bucket; bis
   dahin liegen sie doppelt im Repository, und das ist Absicht (ADR-038).
2. **`shop.dormed.de`** — Inertia/Svelte. Setzt Inventory und Billing voraus.
   Die Schema-Zusammenführung mit dem Alt-Shop ist die eigentliche Projektarbeit.
3. **`my.dormed.de`** — Inertia/Svelte. Hier werden die RLS-Policies aus ADR-036 scharf
   geschaltet, inkl. Pflichttest „Kunde A sieht Kunde B nicht" über die echte
   Kundenverbindung.

## Phase 8 — Vor dem Produktivgang

- **Lasttest** gegen eine realistisch geseedete Datenbank (200k Companies, 1M Tickets).
  Ersetzt die Schätzung aus `../06-infrastructure/HOSTING.md` durch einen Messwert.
- **Prod-Dockerfile** (nginx + php-fpm, ADR-034) und — sobald der Queue-Worker real
  wird — eine `docker-compose.prod.yaml` mit `app` + `worker` (ADR-041).
- **Expand/Contract** für destruktive Migrationen etablieren (ADR-041).

## Offene Entscheidungen

| # | Frage | Blockiert |
| --- | --- | --- |
| ~~1~~ | ~~Tabellenform der Identität~~ — **entschieden in ADR-042: getrennt.** `users`/`User`/Guard `staff` für Mitarbeiter, `customer_accounts`/`CustomerAccount`/Guard `customer` für Kunden. `users.role_id` bleibt `NOT NULL`, D-124 unverändert. | — |
| 2 | Ein Kontakt an mehreren Firmen: `app.company_id` wäre nicht eindeutig. Sitzung mit aktiver Firma, oder Policy auf eine Menge? (ADR-036/037) | Phase 7.3 |
| ~~3~~ | ~~Ablage der Seed-Assets~~ — **entschieden 2026-09-14: die Dateien bleiben im Repository** (Nutzer). Kein Git LFS, keine externe Ablage. Folge: ~102 MB in jedem Clone und im Prod-Build-Kontext. Das ist bei einem Einzel-Node-Deploy unkritisch und der Preis dafür, dass der Seed-Lauf ohne Zusatzschritt funktioniert — in Dev bei jedem `up`, in Prod einmalig. `.dockerignore` schließt sie deshalb **nicht** aus. | — |
| 4 | ~~Veröffentlichungsmerkmal am Artikel~~ — **beantwortet 2026-09-15 beim Katalogbau:** drei unabhängige Boolean-Spalten auf `articles`. `is_active` (Katalogpflege im ERP) · `is_public` (Website) · `is_orderable` (Shop). Kein Status-Enum, weil die drei Fälle sich nicht ausschließen — ein Gerät kann auf der Website stehen und nur auf Anfrage erhältlich sein. **Offen bleibt der Website-INHALT:** Slug, Marketingtext, Bilder und Prospekt-PDF sind kein Flag und gehören in eine eigene Tabelle, sobald die Website ihre Produkte aus der Datenbank zieht (ADR-038). Heute liegen sie als Markdown unter `resources/views/website/`. | — |

## Keine Big-Bang-Implementierung

Nicht mehrere Module parallel bauen. Jeder Slice bleibt lokal lauffähig und testbar,
bevor der nächste beginnt.
