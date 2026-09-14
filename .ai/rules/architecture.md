---
paths:
  - app/**
  - tests/**
---

# Monolith: wohin welcher Code gehört

> Kurswechsel 2026-09-14 (ADR-033–ADR-040). Ersetzt das `core`-Package-Modell
> vollständig. Vollständige Struktur: `.docs/01-architecture/PROJECT_STRUCTURE.md`.

## Zwei Achsen, die nicht verwechselt werden dürfen

- **Modul** = fachliche Domäne. `app/Modules/<Modul>/`, Namespace
  `App\Modules\<Modul>\`. Models, Services, DTOs, Policies, Events. Modul-intern
  geschichtet — Ordner entstehen mit den Klassen, nicht auf Vorrat (ADR-010).
- **Zugriffspunkt** = Domain. `app/Http/Controllers/<Website|Shop|Erp|Portal>/`, eine
  Route-Datei, ein Frontend-Stack, eine Datenbankrolle.

Ein Zugriffspunkt benutzt viele Module. Ein Modul wird von mehreren Zugriffspunkten
benutzt. **Ein Zugriffspunkt ist kein Modul** und taucht in keinem `depends_on` auf.

## Regeln

- **Models und Migrations gehören ins Modul**, nie in `app/Http/` und nie in einen
  Controller-Ordner.
- **Controller sind dünn.** Auth, Input, Service aufrufen, Response. Keine
  Geschäftslogik, keine mehrzeiligen Eloquent-Ketten.
- **Schreibzugriffe laufen über Modul-Services, nicht über rohes Eloquent.** Nicht
  `Order::create(...)` im Controller, sondern der Domain-Service aus
  `app/Modules/<Modul>/Services`. Lesezugriffe über Models sind frei. Grund:
  Validierung, Events und Statusübergänge müssen zentral durchlaufen werden.
- **Abhängigkeiten nur über `module.php`.** `['name' => …, 'description' => …,
  'depends_on' => [...]]`. Ein Modul darf `App\Modules\X` nur referenzieren, wenn `X`
  in seinem `depends_on` steht. Graph azyklisch (ADR-005).
- **Kein `App\Http\…` in `app/Modules/**`.** Die Domäne kennt keine HTTP-Schicht.
  **Diese Regel trägt jetzt mehr Gewicht als früher:** vorher trennte eine
  Package-Grenze die beiden Welten physisch, jetzt liegen sie im selben `app/`-Baum
  und nur `ModuleBoundariesTest` hält sie auseinander.
- **`tests/Architecture/ModuleBoundariesTest` muss grün sein.**
- **Neues Modul:** Ordner + `module.php` anlegen, wenn ein Slice es braucht. Das
  Manifest reicht, keine Extra-Dokumentation nötig.
- **Kein Tenant-Isolationsmuster** (kein `stancl/tenancy`, kein Schema-per-Tenant) —
  die vier Zugriffspunkte sind Sichten, keine Mandanten.
- **Nie aus `.legacy/` importieren, autoloaden oder testen.** Das sind ungetrackte
  Fremdprojekte und nur Lesestoff (ADR-040).

## Datenbankrolle ist Teil der Architektur, nicht der Konfiguration

Vier Postgres-Rollen, die Verbindung folgt dem **Zugriffspunkt** (ADR-036):
`dormed_public` (Website, anonym) · `dormed_customer` (Portal/Shop, eingeloggt) ·
`dormed_staff` (ERP) · `dormed_owner` (nur Migrations).

- **Nie ein `$connection` auf einem Model setzen.** Die Verbindung gehört an die
  Route-Group — dasselbe `Product` wird je nach Aufrufer über eine andere Rolle gelesen.
- **Die Anwendung verbindet sich nie als `dormed_owner`.** Postgres wendet RLS auf den
  Schema-Eigentümer nicht an, und zwar still und ohne Fehlermeldung.
- **RLS ersetzt keine Policy.** Die Permission-Matrix der fünf Abteilungen
  (D-125/D-136/D-137) bleibt in Laravel-Policies und Gates. RLS ist Zeileneigentum für
  Kunden und wirkt als Netz darunter.

## Listenansichten: die Mechanik liegt zentral

Jede Liste im ERP benutzt `resources/js/components/data-table` und
`App\Support\DataTable\DataTable`. Eine Fläche bringt **nur Spalten und
Datensatz** mit — Suche, Sortierung, Seitenaufteilung und Erscheinungsbild
kommen von dort.

- **Serverseitig**, nicht im Browser. Abweichend von der shadcn-Anleitung, die
  alle Zeilen lädt und clientseitig filtert: der Adressstamm hat rund 50.000
  Personen (`.docs/06-infrastructure/HOSTING.md`).
- **Eine Zeile ist ein Datensatz der Basistabelle — nichts anderes.** Gejoint
  wird nur, was höchstens einmal vorkommt (Sitzadresse, Fachrichtung). Eine
  Zu-vielen-Beziehung wird **gezählt** (`withCount`), nie gejoint: ein
  `LEFT JOIN` auf `company_contacts` macht aus einer Praxis mit sieben
  Kontakten sieben Zeilen, `meta.total` zählt dann Beziehungen statt Firmen,
  und derselbe Schlüssel erscheint mehrfach. Die Gegenseite gehört in die
  Detailansicht, wo der Datensatz den Rahmen bildet.
- **Verlinkt wird über `rowHref`**, nicht über eine eigene Spalte je Fläche.
  Die ganze Zeile wird damit anklickbar; die Leitspalte trägt zusätzlich einen
  `DataTableLink`, damit mittlere Maustaste, „in neuem Tab öffnen" und Tastatur
  funktionieren.
- **Sortierung nur über die Freigabeliste** im Controller. Der Spaltenname kommt
  aus der URL und landet in `ORDER BY`; ohne Prüfung ließe sich damit nach
  beliebigen Spalten ordnen, auch aus fremden Tabellen.
- **`addSelect()` mit `'tabelle.spalte as alias'`**, nicht mit
  `alias => spalte` — die Schlüssel-Form erwartet Unterabfragen und liefert bei
  Spaltennamen stillschweigend nichts.

## Kein Dark Mode (ADR-044)

Eine Farbpalette, an Light angelehnt. **Keine `dark:`-Utilities, keine `.dark`-Klasse,
keine `prefers-color-scheme`-Abfrage** — das ist ersatzlos entfernt, nicht abgeschaltet.

## Kein Octane (ADR-034)

Klassisches php-fpm. Kein `octane:*`, keine Annahmen über langlebige Worker, kein
absichtlich gehaltener Zustand zwischen Requests.

**Queue-Regel:** Alles, was nicht zuverlässig unter ~200 ms bleibt, geht in die Queue —
PDF-Erzeugung (D-038/D-044), Rechnungsläufe, DATEV-Export, DHL-/PayPal-Aufrufe.

## Tests

`tests/Feature/` für alles über HTTP Erreichbare, je Zugriffspunkt gruppiert.
`tests/Unit/` nur für Framework-freie Logik. `tests/Architecture/` für Modulgrenzen und
die Beweise zu ADR-033/036. Siehe `testing-best-practices`-Skill.

**Die Suite läuft gegen PostgreSQL** (`dormed_test`), nicht gegen SQLite — Rollen, RLS und
CHECK-Constraints existieren dort nicht, ein grüner SQLite-Lauf bewiese nichts. Der
Dev-Stack muss dafür laufen. Treiberspezifische Migrationen prüfen den Treiber und werden
sonst zum No-Op.

**Pflichttest für RLS:** dass Kunde A die Zeilen von Kunde B nicht lesen kann, geprüft
über die **echte Kundenverbindung** — nicht über eine Laravel-Policy, die man dabei
umgeht.
