# Zielarchitektur

> **Kurswechsel 2026-09-14** (ADR-033–ADR-040): ein echter Monolith. Die Zwischenstufe
> „vier eigenständige Apps + geteiltes `core`-Package" (ADR-011–ADR-018) ist aufgehoben.
> Verzeichnisbaum: [`PROJECT_STRUCTURE.md`](PROJECT_STRUCTURE.md), Routing:
> [`MULTI_SUBDOMAIN.md`](MULTI_SUBDOMAIN.md), Deployment:
> [`../06-infrastructure/DOCKER.md`](../06-infrastructure/DOCKER.md).

## 1. Architekturform

Eine **Laravel-Anwendung**, eine **PostgreSQL-Datenbank**, vier **Zugriffspunkte**
(ADR-033):

- **`dormed.de`** — öffentlicher Auftritt, anonym, Produktdaten aus derselben DB (ADR-038)
- **`shop.dormed.de`** — E-Commerce
- **`erp.dormed.de`** — Mitarbeiter-Anwendung (ADR-031), inkl. Technikerflow (ADR-032)
- **`my.dormed.de`** — Kundenportal

Die vier sind **Routing**, keine Deployment-Einheiten. Es gibt kein `core`-Package, keine
App-Ordner, keine Composer-Path-Repositories und keine Kommunikation zwischen „Apps" —
es gibt keine Apps mehr, zwischen denen kommuniziert werden könnte.

Es gibt **keine** Multi-Tenancy — die vier Zugriffspunkte sind Sichten desselben
Unternehmens auf dieselben Daten, keine Mandanten.

## 2. Grundprinzip

```text
Browser / Mobile / später native App
                  |
                  v
      Reverse Proxy (Coolify, einziger TLS-Terminierungspunkt)
                  |
   dormed.de   shop.   erp.   my.        vier Hostnames
        \        |      |     /
         +-------+------+----+
                  |
          EINE Laravel-Anwendung
          app/Modules/<Modul>/            fachliche Domänen
                  |
        vier Postgres-Rollen (ADR-036)    dormed_public | _customer | _staff | _owner
                  |
                  v
             PostgreSQL              eigene Coolify-Ressource, eigener Lebenszyklus
```

Der **Zugriffspunkt bestimmt die Datenbankrolle**, nicht der Datensatz. Dieselbe
`Product`-Zeile wird über `dormed_public` gelesen, wenn die Website sie zeigt, und über
`dormed_staff`, wenn ein Mitarbeiter sie pflegt. Das ist die tragende Sicherheitsgrenze
der Architektur — siehe §6.

## 3. Warum kein Package, keine getrennten Apps

Vier Anwendungen auf einer geteilten Datenbank mit geteiltem Code-Package sind ein
**Integration-Database-Pattern**: die Kosten der Verteilung ohne deren Nutzen. ADR-026
hatte die Deployment-Unabhängigkeit bereits per Beschluss wieder abschaffen müssen, um
Schema-Integrität zu retten — übrig blieben vier `vendor/`-Bäume und trotzdem genau ein
Deploy-Artefakt.

Entscheidend war die Erkenntnis, dass keiner der vier ursprünglichen Treiber getrennte
Anwendungen verlangt (ADR-033): Domain-Trennung ist Proxy-Konfiguration, verschiedene
Zugriffspunkte sind Route-Groups, Skalierung sind Replicas desselben Images — und
Datenintegrität spricht **gegen** eine Aufteilung, weil es keine Transaktion über HTTP
hinweg gibt.

## 4. Fachliche Module

Module werden nach fachlicher Verantwortung geschnitten, nicht nach alten Tabellen. Sie
liegen unter **`app/Modules/<Modul>/`** (ADR-033, zuvor `core/src/Modules/`):

```text
Core · Identity · CRM · Communication · Sales · Service
Documents · Billing · Inventory · Platform · Integrations
```

Nicht jedes Modul muss sofort vollständig implementiert werden. Verzeichnisse entstehen,
wenn ein Slice sie füllt (ADR-010).

## 5. Architekturregel

Jedes Modul deklariert seine erlaubten Abhängigkeiten in `module.php`. Die Richtung des
Graphen wird von `tests/Architecture/ModuleBoundariesTest` erzwungen — der Test bleibt
inhaltlich unverändert gültig, er läuft nur nicht mehr in einem Package-Testlauf, sondern
im Testlauf der Anwendung.

```text
Service   -> Core, Inventory, Documents, Billing
Sales     -> Core, Inventory
Inventory -> Core
Billing   -> Core, Documents
```

Die Kanten `Service -> Inventory` und `Sales -> Inventory` stammen aus D-121: Positionen
brauchen einen Katalogbezug (`line_items.article_id`, `OpportunityItem.article_id`). Damit
der Graph azyklisch bleibt, liegt `Device` in `Inventory` (`DeviceComponent` ist mit D-131
entfallen) — der Wareneingang erzeugt sie, Service konsumiert sie. Siehe
[`../04-domain/INVENTORY.md`](../04-domain/INVENTORY.md).

Keine zyklischen Modulabhängigkeiten ohne dokumentierte Begründung.

**Ein Zugriffspunkt ist kein Knoten in diesem Graphen.** `dormed.de` oder `erp.dormed.de`
sind Routen und Controller, keine Module — sie *benutzen* Module.

## 6. Identität, Zugriff und Datenbankrollen

### Ein Kontakt, ein Zugang (ADR-037)

Shop-Besteller, Portal-Nutzer und CRM-Ansprechpartner sind **dieselbe Entität**. Ein
Mitarbeiter kann den Zugang eines Kunden direkt aus dem Kontaktdatensatz heraus
verwalten — Passwort zurücksetzen, sperren, einladen. Das ist der eigentliche Grund für
die Single-Source-of-Truth-Entscheidung.

Mitarbeiter und Kunden bleiben **getrennte Anmeldewege** — und seit ADR-042 auch
getrennte Tabellen, Models und Guards:

| | Tabelle | Model | Guard |
| --- | --- | --- | --- |
| Mitarbeiter | `users` (`role_id NOT NULL`, D-124) | `User` | `staff` |
| Kunde | `customer_accounts` → FK `person_id` | `CustomerAccount` | `customer` |

Der Kundenzugang hängt fachlich am CRM-Kontakt, liegt aber in einer eigenen schmalen
Tabelle. Grund ist derselbe wie bei `dormed_public` unten: 1.600 Kundenkonten und 20
Mitarbeiterkonten in einer Tabelle wären nur durch ein `where` getrennt, das jemand
vergessen kann. Getrennt **kann** ein Kunde in einer Mitarbeiterabfrage nicht vorkommen.

### Vier Postgres-Rollen (ADR-036)

| Rolle | Zugriffspunkt | Rechte |
| --- | --- | --- |
| `dormed_owner` | nur Migrations | alle, Eigentümer des Schemas |
| `dormed_staff` | ERP | volle fachliche Rechte, Policies regeln den Rest |
| `dormed_customer` | Portal, Shop (eingeloggt) | RLS auf die eigene Firma |
| `dormed_public` | `dormed.de`, anonymer Katalog | SELECT auf Veröffentlichtes, INSERT nur auf Anfragen |

**RLS ersetzt die Autorisierung nicht.** Die Permission-Matrix der fünf Abteilungen
(D-125/D-136/D-137) bleibt vollständig in Laravel-Policies und Gates. RLS ist Zeileneigentum
für Kunden und wirkt als Netz darunter — es fängt die vergessene `where`-Klausel ab, nicht
die falsche Abteilung.

Die Anwendung verbindet sich **nie** als Schema-Eigentümer: Postgres wendet RLS auf den
Eigentümer nicht an, und der Fehler wäre still. Details:
[`../03-security/AUTHORIZATION.md`](../03-security/AUTHORIZATION.md), ADR-036.

## 7. Frontend (ADR-039)

| Zugriffspunkt | Stack |
| --- | --- |
| ERP, Portal, Shop | Inertia + Svelte |
| `dormed.de` | Plain Blade + Blade-Komponenten |

**Kein Dark Mode** (ADR-044): eine Farbpalette, an Light angelehnt. Die Umschaltung des
Templates ist vollständig entfernt.

Inertia ist pro Route wählbar — beide Stacks laufen aus denselben Controllern und Models.
Die Website bleibt server-gerendert, weil sie von Suchmaschinen und Erstbesuchern gelesen
wird, nicht bedient.

## 8. Betriebsgrößen

Bemessungsgrundlage aus ADR-033: 20 Mitarbeiter, 1600 potenzielle Portalkunden, 50.000
Legacy-Adressen (Firmen + Ansprechpartner zusammen), Termine und Tickets in ähnlicher
Größenordnung. Realistische Spitze ~10–20 req/s bei einer Kapazität von ~40 req/s
komfortabel auf 6 Kernen — **Faktor 15–30 Reserve**.

Daraus folgen zwei Entscheidungen: **kein Octane** (ADR-034, der Bootstrap-Gewinn ist bei
dieser Last nicht messbar, die State-Leak-Bugklasse dagegen real) und **keine
vorsorgliche Aufteilung**. Skaliert wird, wenn nötig, horizontal mit Replicas desselben
Images.

Der einzige Posten mit echtem Wachstum sind die Einsatzfotos (ADR-029), nicht die
Datenbank.

## 9. Keine Vorwegnahme des vollständigen Datenmodells

Die technische Basis wird bewusst ohne vollständiges Schema gebaut. Die Datenstruktur
entsteht iterativ anhand realer Prozesse (ADR-010).

## 10. Zukunft

- **API.** Die spätere Mobile-App bekommt eine `/api/v1`-Route-Group in **dieser**
  Anwendung — kein eigener Dienst. Herauslösen ist später ein Tagesprojekt; ein
  verteiltes System wieder zusammenzuziehen ist es nicht (ADR-033).
- **Mandanten.** Mehrere Unternehmen sind derzeit kein Architekturtreiber. Die
  Rollen- und RLS-Struktur aus ADR-036 verbaut den Weg nicht.
