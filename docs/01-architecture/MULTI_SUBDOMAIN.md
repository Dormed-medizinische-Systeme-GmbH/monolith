# Multi-Subdomain-Architektur

## Ziel

Eine Laravel-Anwendung soll mehrere Subdomains bedienen:

- CRM
- Customer Portal
- Shop

Alle Subdomains teilen sich:

- Laravel-Code
- PostgreSQL
- Authentifizierung
- gemeinsame Assets
- UI-Komponenten
- technische Services

## Wichtig

Subdomains sind keine Sicherheitsgrenze.

Ein Request auf `portal.*` darf nur deshalb keine CRM-Funktion erhalten, weil eine Route nicht registriert wurde. Autorisierung muss zusätzlich fachlich erfolgen.

## Request-Kontext

Der Hostname wird früh im Request verarbeitet.

Ein zentraler Kontext kann konzeptionell bereitstellen:

```text
ApplicationContext
- crm
- portal
- shop
```

Der konkrete technische Name und Speicherort sind noch nicht festgeschrieben.

## Routing

Routen sollen nach Kontext gruppiert werden.

```text
routes/
├── web.php
├── crm.php
├── portal.php
└── shop.php
```

Die genaue Laravel-Registrierung muss zur installierten Laravel-Version passen.

## Gemeinsame Komponenten

Bevorzugt:

```text
resources/
├── js/components
├── js/layouts
├── js/shared
└── views/components
```

Fachliche UI:

```text
resources/
├── js/crm
├── js/portal
└── js/shop
```

## Authentifizierung

Eine Identität kann perspektivisch mehrere Anwendungskontexte nutzen.

Beispiel:

```text
User
 ├── Employee
 └── CustomerContact
```

Das ist eine fachliche Zuordnung, kein Grund für getrennte Auth-Systeme.

## Lokale Entwicklung

Die lokale DNS-/Hosts-Auflösung muss alle verwendeten Subdomains auf denselben Laravel-Webserver zeigen.

Beispielhaft:

```text
crm.example.test
portal.example.test
shop.example.test
```

Die konkrete Domain ist noch offen.

## Cookies / Sessions

Wenn CRM, Portal und Shop dieselbe Browser-Session teilen sollen, müssen Cookie-Domain, SameSite-Regeln, HTTPS und Session-Konfiguration bewusst konfiguriert werden.

Dies darf nicht implizit angenommen werden.

## Do not

- Keine drei Laravel-Projekte.
- Keine drei `vendor/`-Installationen.
- Keine getrennten Datenbanken pro Subdomain.
- Keine Geschäftslogik im DNS/Webserver.
- Keine Berechtigung ausschließlich anhand des Hostnamens.
