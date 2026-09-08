---
paths:
  - app/Modules/**
  - app/Http/Controllers/**
  - tests/Architecture/**
---

# Modular Monolith — wohin welcher Code gehört

Vollständige Struktur: `docs/01-architecture/PROJECT_STRUCTURE.md`. Kurzfassung:

## Zwei Ebenen

- **`app/`** = geteilte Plattform + HTTP-Shell. `Http/Controllers/{Crm,Portal,Shop}/`,
  `Middleware/`, `Requests/`, `Support/`, `Models/User.php`. Standard-Laravel.
- **`app/Modules/<Modul>/`** = Domäne. Namespace `App\Modules\<Modul>\`. Modul-intern
  geschichtet (`Models/`, `Actions/`, `Data/`, `Policies/`) — Ordner entstehen mit
  den Klassen, nicht auf Vorrat (ADR-010).

## Regeln

- **Controller sind dünn.** `Http/Controllers/<Kontext>/` übersetzt HTTP ↔ Modul.
  Keine Geschäftslogik, keine Eloquent-Queries dort — die leben im Modul
  (Action / Model-Methode / Query-Objekt).
- **Subdomain ≠ Modul.** `crm`/`portal`/`shop` sind Präsentationskontexte. Ein
  Kontext-Controller darf mehrere Module aufrufen; ein Modul weiß nichts von Kontexten.
- **Abhängigkeiten nur über `module.php`.** Jedes Modul hat
  `app/Modules/<Modul>/module.php` → `['name' => …, 'description' => …, 'depends_on' => [...]]`.
  Ein Modul darf `App\Modules\X` nur referenzieren, wenn `X` in seinem `depends_on` steht.
  Richtung folgt ADR-005 (`Service → Core/Documents/Billing`, `Billing → Core/Documents`, …),
  Graph azyklisch.
- **Kein `use App\Http\…` in `app/Modules/**`.** Die Domäne kennt die HTTP-Schicht nicht.
- **`tests/Architecture/ModuleBoundariesTest` muss grün sein** — läuft in `php artisan test` mit.
- **Neues Modul:** Ordner + `module.php` anlegen, wenn ein Slice es braucht. Danach in
  `PROJECT_STRUCTURE.md` / `ARCHITECTURE.md` §4 nicht neu dokumentieren nötig — das Manifest reicht.

## Tests

`tests/Feature/{Crm,Portal,Shop}/` für alles über HTTP Erreichbare. `tests/Unit/` nur
für Framework-freie Logik. Siehe `testing-best-practices`-Skill.
