# Zielarchitektur

## 1. Architekturform

Das System ist ein **Laravel Modular Monolith**.

Eine Laravel-Anwendung enthält mehrere fachliche Anwendungskontexte:

- CRM / interne Anwendung
- Customer Portal
- Shop
- später Warenwirtschaft / Inventory / Warehouse

Es gibt zunächst keine separaten Backend-Services für diese Bereiche.

## 2. Grundprinzip

```text
Browser / Mobile
       |
       v
   DNS / Webserver
       |
       v
     Laravel
       |
  +----+----------------+
  |    |       |        |
 CRM Portal   Shop   Shared
  |     |      |       |
  +-----+------+-------+
            |
            v
       PostgreSQL
```

Laravel ist die einzige Applikationsschicht, die auf PostgreSQL zugreift.

## 3. Gemeinsame Plattform

Gemeinsam genutzt werden:

- Authentifizierung
- Benutzer-/Identitätsmodell
- Autorisierung
- UI-Komponenten
- Design Tokens / Themes
- Assets
- Domain-/Subdomain-Erkennung
- Datenbank
- Queues
- Events
- Notifications
- Audit
- Dokumenten-/Dateiinfrastruktur
- Integrationsinfrastruktur

## 4. Fachliche Module

Module werden nach fachlicher Verantwortung geschnitten, nicht nach alten Tabellen.

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
Portal
Shop
Inventory
Platform
Integrations
```

Nicht jedes Modul muss sofort vollständig implementiert werden.

## 5. Architekturregel

Ein Modul darf seine eigenen fachlichen Modelle und Services besitzen. Gemeinsame technische Infrastruktur darf zentral liegen.

Fachliche Abhängigkeiten müssen bewusst sein.

Jedes Modul liegt unter `app/Modules/<Modul>/` (Namespace `App\Modules\<Modul>\`)
und deklariert seine erlaubten Abhängigkeiten in `module.php`. Die Richtung des
Graphen wird von `tests/Architecture/ModuleBoundariesTest` erzwungen. Struktur:
`docs/01-architecture/PROJECT_STRUCTURE.md`.

Beispiel:

```text
Service -> Core
Service -> Documents
Service -> Billing

Billing -> Core
Billing -> Documents

Shop -> Core
Shop -> Billing
```

Keine zyklischen Modulabhängigkeiten ohne dokumentierte Begründung.

## 6. Keine Vorwegnahme des vollständigen Datenmodells

Die initiale technische Basis wird bewusst ohne vollständiges CRM-Schema gebaut.

Die Datenstruktur wird iterativ anhand realer Prozesse entwickelt.

## 7. Zukunft

Mehrere Unternehmen/Mandanten könnten langfristig unterstützt werden. Dies ist derzeit kein primärer Architekturtreiber.

Die Anwendung darf deshalb nicht unnötig als vollständiges Multi-Tenant-SaaS gebaut werden.

Gleichzeitig sollen zentrale Identitäts-, Scope- und Autorisierungsentscheidungen spätere Erweiterungen nicht unnötig verhindern.
