---
paths:
  - packages/core/**
  - apps/*/app/Http/Controllers/**
  - apps/*/tests/Feature/**
  - packages/core/tests/Architecture/**
---

# Monorepo: vier Apps + `packages/core` — wohin welcher Code gehört

> Kurswechsel 2026-09-12 (ADR-011–ADR-018). Ersetzt das frühere Ein-App-`app/Modules/**`-
> Modell vollständig. Vollständige Struktur: `docs/01-architecture/PROJECT_STRUCTURE.md`.

## Zwei Ebenen

- **`packages/core/src/Modules/<Modul>/`** = die Domäne. Namespace
  `Dormed\Core\Modules\<Modul>\`. **Alle** Models, Migrations, domänenübergreifenden
  Services leben ausschließlich hier — nie in einer `apps/*`-App, auch nicht „nur kurz
  für einen Spezialfall" (`03-domain-boundaries-rules.md` Regel 1). Modul-intern
  geschichtet (`Models/`, `Services/`, `Data/`, `Policies/`) — Ordner entstehen mit den
  Klassen, nicht auf Vorrat (ADR-010).
- **`apps/<app>/app/`** = App-spezifischer Code für genau eine Domäne (`website`, `shop`,
  `crm`, `portal`). `Http/Controllers/`, `Middleware/`, `Requests/`. Standard-Laravel,
  eigenständiges Projekt.

## Regeln

- **Controller sind dünn.** `apps/<app>/app/Http/Controllers/` übersetzt HTTP ↔
  Core-Modul-Aufruf. Keine Geschäftslogik, keine Eloquent-Queries dort — die leben im
  Modul (Action / Model-Methode / Query-Objekt in `packages/core`).
- **Schreibzugriffe laufen über Core-Services, nicht über rohes Eloquent.** Eine App ruft
  `Order::create(...)` nicht unreflektiert selbst auf, sondern über einen Domain-Service
  aus `packages/core/src/Modules/<Modul>/Services`. Lesezugriffe (Queries, Relationships)
  dürfen direkt über die Core-Models erfolgen. Grund: Business-Regeln (Validierung,
  Events, Statusübergänge) müssen zentral durchlaufen werden (`03-domain-boundaries-
  rules.md` Regel 2).
- **Keine direkte Kommunikation zwischen den vier Apps** — weder HTTP noch API-Call. Die
  einzige Integrationsschicht ist die gemeinsame Datenbank über Core-Models/-Services,
  oder ein Event aus `packages/core/src/Events` (Regel 2a).
- **Abhängigkeiten nur über `module.php`.** Jedes Modul in `packages/core` hat
  `src/Modules/<Modul>/module.php` → `['name' => …, 'description' => …, 'depends_on' =>
  [...]]`. Ein Modul darf `Dormed\Core\Modules\X` nur referenzieren, wenn `X` in seinem
  `depends_on` steht. Richtung folgt ADR-005, Graph azyklisch. Eine `apps/*`-App ist
  **kein** Knoten in diesem Graphen — sie referenziert `packages/core` als Ganzes.
- **Kein `App\Http\…`/App-Namespace in `packages/core/src/**`.** Die Domäne kennt keine
  HTTP-Schicht und keine einzelne App.
- **Jede der vier Apps wird strukturell gleich behandelt.** Keine Sonderregel, keine
  abweichende Isolationsstufe für Shop/CRM/Portal/Website (`03-domain-boundaries-
  rules.md` Regel 3).
- **`packages/core/tests/Architecture/ModuleBoundariesTest` muss grün sein.**
- **Neues Modul:** Ordner + `module.php` in `packages/core/src/Modules/` anlegen, wenn
  ein Slice es braucht. Danach in `PROJECT_STRUCTURE.md`/`ARCHITECTURE.md` §4 nicht neu
  dokumentieren nötig — das Manifest reicht.
- **Kein Tenant-Isolationsmuster** (kein `stancl/tenancy`, kein Schema-/Database-per-
  Tenant) — die vier Apps sind fachliche Module, keine Mandanten.

## Tests

`apps/<app>/tests/Feature/` für alles über HTTP Erreichbare dieser App.
`packages/core/tests/Unit/` nur für Framework-freie Logik. Siehe
`testing-best-practices`-Skill.
