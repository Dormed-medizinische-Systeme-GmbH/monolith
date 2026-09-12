# Zielarchitektur

> **Kurswechsel 2026-09-12** (ADR-011–ADR-018): kein Ein-App-Modular-Monolith mehr.
> Monorepo mit vier eigenständigen Laravel-Apps + geteiltem `packages/core`. Dieses
> Dokument beschreibt den **neuen** Zielzustand; Details zum Verzeichnisbaum:
> [`PROJECT_STRUCTURE.md`](PROJECT_STRUCTURE.md), zum Routing:
> [`MULTI_SUBDOMAIN.md`](MULTI_SUBDOMAIN.md), zum Deployment:
> [`../06-infrastructure/DOCKER.md`](../06-infrastructure/DOCKER.md).

## 1. Architekturform

Ein **Monorepo mit vier eigenständigen Laravel-Projekten** (ADR-011):

- **`apps/website`** — öffentlicher Auftritt (ADR-017)
- **`apps/shop`** — E-Commerce
- **`apps/crm`** — internes Vertriebs-/Kundenmanagement (Mitarbeiter)
- **`apps/portal`** — Kundenportal

Jede App ist ein vollwertiges, eigenständiges Laravel-Projekt: eigenes `composer.json`,
eigenes Dockerfile, eigener Deployment-Container, eigener Deploy-Zyklus. Gemeinsamer
Code — alle Eloquent-Models, alle Migrations, domänenübergreifende Business-Logik — liegt
**einmalig** in `packages/core` und wird von allen vier Apps als lokales
Composer-Path-Repository eingebunden (ADR-011).

Es gibt **keine** Multi-Tenancy — die vier Apps sind fachliche Module desselben
Unternehmens, keine Mandanten (ADR-011, `01-architecture-overview.md` §„Nicht
verwechseln mit Multi-Tenancy").

## 2. Grundprinzip

```text
Browser / Mobile
       |
       v
  Reverse Proxy (Coolify, einziger TLS-Terminierungspunkt, ADR-014)
       |
  +----+-------+-------+----------+
  |    |       |       |          |
Website Shop  CRM   Portal    (je eigener Container, eigenes Laravel-Projekt)
  |    |       |       |
  +----+-------+-------+
            |
            v
    packages/core (Models, Migrations, Domain-Services — SSOT, ADR-013)
            |
            v
       PostgreSQL (eine Instanz, eine DB, geteilt von allen vier Apps)
```

Alle vier Apps greifen ausschließlich **über `packages/core`** auf PostgreSQL zu — nie mit
rohem Eloquent an Core-Models vorbei für Schreibzugriffe (`03-domain-boundaries-rules.md`
Regel 2). Es gibt **keine** direkte HTTP-Kommunikation zwischen den vier Apps; Integration
läuft ausschließlich implizit über die gemeinsame Datenbank/Core-Services bzw. Events aus
`packages/core/src/Events` (Regel 2a).

## 3. Gemeinsame Plattform

Gemeinsam genutzt, über `packages/core` bzw. gemeinsame Infrastruktur:

- Models, Migrations, domänenübergreifende Domain-Services (SSOT, ADR-011/013)
- Datenbank (eine PostgreSQL-Instanz)
- Events (`packages/core/src/Events`)
- Queues, Notifications, Audit, Dokumenten-/Dateiinfrastruktur, Integrationsinfrastruktur
  — sofern domänenübergreifend, sonst App-lokal

**Nicht mehr geteilt:** Authentifizierung/Session über alle vier Apps hinweg — siehe §3a.
UI-Komponenten/Design-Tokens sind pro App lokal (kein gemeinsames `resources/`-Verzeichnis
mehr, da jede App ihr eigenes Laravel-Projekt ist); ein gemeinsames Theme kann trotzdem
App-übergreifend abgestimmt sein, das ist eine Design-, keine Code-Teilungsfrage.

## 3a. Login/Identität über App-Grenzen (ADR-016)

- **`apps/crm`** (Mitarbeiter) hat einen eigenen, von den anderen drei Apps komplett
  getrennten Login (D-027/D-029, `../03-security/IDENTITY_RBAC.md`).
- **`apps/portal`** und **`apps/shop`** teilen sich einen Kundenlogin — derselbe Kunde
  bewegt sich mit einem Account/einer Session in beiden Apps.
- **`apps/website`** ist überwiegend anonym.
- Technischer Mechanismus (Vorschlag, bei Umsetzung zu bestätigen): gleiche
  `SESSION_DOMAIN` + gleicher `APP_KEY` + eine gemeinsame `sessions`-Tabelle in Postgres,
  genutzt nur von Portal + Shop. Kein Cross-App-Mechanismus für `crm`/`website`.

## 4. Fachliche Module

Module werden nach fachlicher Verantwortung geschnitten, nicht nach alten Tabellen. Sie
leben **innerhalb von `packages/core/src/Modules/<Modul>/`** (ADR-013) — nicht in einer
einzelnen App.

Vorgesehene Bereiche:

```text
Core
Identity
CRM
Communication
Sales
Service
Documents
Billing
Inventory
Platform
Integrations
```

(„Portal" und „Shop" sind jetzt Apps, keine fachlichen Module mehr — sie *konsumieren*
fachliche Module aus `packages/core`, analog zu CRM/Website.)

Nicht jedes Modul muss sofort vollständig implementiert werden.

## 5. Architekturregel

Ein Modul in `packages/core` darf seine eigenen fachlichen Modelle und Services besitzen.
Fachliche Abhängigkeiten müssen bewusst sein.

Jedes Modul liegt unter `packages/core/src/Modules/<Modul>/` und deklariert seine
erlaubten Abhängigkeiten in `module.php`. Die Richtung des Graphen wird von
`tests/Architecture/ModuleBoundariesTest` (im `packages/core`-Testlauf) erzwungen.
Struktur: [`PROJECT_STRUCTURE.md`](PROJECT_STRUCTURE.md).

Beispiel:

```text
Service -> Core
Service -> Documents
Service -> Billing

Billing -> Core
Billing -> Documents

Shop-App -> packages/core (nutzt Core, Billing, Inventory, …)
```

Keine zyklischen Modulabhängigkeiten ohne dokumentierte Begründung. Eine `apps/*`-App
selbst ist **kein** Knoten in diesem Graphen — sie referenziert `packages/core` als Ganzes
und nutzt daraus, was sie braucht (ADR-013).

## 6. Keine Vorwegnahme des vollständigen Datenmodells

Die initiale technische Basis wird bewusst ohne vollständiges CRM-Schema gebaut. Die
Datenstruktur wird iterativ anhand realer Prozesse entwickelt (ADR-010).

## 7. Zukunft

Mehrere Unternehmen/Mandanten könnten langfristig unterstützt werden. Dies ist derzeit
kein primärer Architekturtreiber — und mit ADR-011 explizit **kein** Multi-Tenancy-Muster
(kein `stancl/tenancy`, kein Schema-/Database-per-Tenant). Zentrale Identitäts-, Scope- und
Autorisierungsentscheidungen sollen spätere Erweiterungen dennoch nicht unnötig verhindern.
