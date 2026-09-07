# Projektstruktur

## Ziel

Die Projektstruktur soll für einen Laravel-Entwickler sofort verständlich sein und gleichzeitig fachliche Module sauber abgrenzen.

Laravel-Standardverzeichnisse bleiben dort erhalten, wo Laravel-Konventionen sinnvoll sind.

## Empfohlene Struktur

```text
project/
├── app/
│   ├── Console/
│   ├── Exceptions/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── CRM/
│   │   │   ├── Portal/
│   │   │   ├── Shop/
│   │   │   └── Shared/
│   │   ├── Middleware/
│   │   ├── Requests/
│   │   │   ├── CRM/
│   │   │   ├── Portal/
│   │   │   └── Shop/
│   │   └── Resources/
│   ├── Models/
│   │   ├── Core/
│   │   ├── CRM/
│   │   ├── Sales/
│   │   ├── Service/
│   │   ├── Billing/
│   │   └── Platform/
│   ├── Policies/
│   ├── Providers/
│   ├── Actions/
│   │   ├── CRM/
│   │   ├── Service/
│   │   ├── Billing/
│   │   └── ...
│   ├── Services/
│   │   ├── CRM/
│   │   ├── Service/
│   │   ├── Documents/
│   │   └── Integrations/
│   ├── Jobs/
│   ├── Events/
│   ├── Listeners/
│   ├── Notifications/
│   └── Support/
│
├── bootstrap/
├── config/
├── database/
│   ├── factories/
│   ├── migrations/
│   └── seeders/
│       ├── DatabaseSeeder.php
│       └── Demo/
│
├── docs/
│   ├── 01-architecture/
│   ├── 02-development/
│   ├── 03-security/
│   ├── 04-domain/
│   ├── 05-modules/
│   ├── 06-infrastructure/
│   ├── 06-integrations/
│   └── 07-decisions/
│
├── resources/
│   ├── css/
│   ├── js/
│   │   ├── components/
│   │   ├── layouts/
│   │   ├── shared/
│   │   ├── crm/
│   │   ├── portal/
│   │   └── shop/
│   └── views/
│       ├── components/
│       ├── layouts/
│       ├── crm/
│       ├── portal/
│       └── shop/
│
├── routes/
│   ├── web.php
│   ├── auth.php
│   ├── crm.php
│   ├── portal.php
│   ├── shop.php
│   └── channels.php
│
├── tests/
│   ├── Feature/
│   │   ├── CRM/
│   │   ├── Portal/
│   │   └── Shop/
│   ├── Unit/
│   └── Architecture/
│
├── docker/
│   ├── php/
│   └── ...
├── compose.yaml
├── .env.example
├── AGENTS.md
└── README.md
```

## Wichtige Regel

Die obige Struktur ist ein Zielbild, kein Anlass, alle Verzeichnisse sofort anzulegen.

Leere Modulordner werden nicht künstlich erzeugt.

## Subdomain-Routing

Subdomain-Routing wird zentral definiert. Es ist nicht erforderlich, für jede Subdomain eine eigene Laravel-Anwendung oder einen eigenen `public/`-Ordner anzulegen.

Beispielkonzept:

```text
crm.example.test    -> Laravel -> CRM routes
portal.example.test -> Laravel -> Portal routes
shop.example.test   -> Laravel -> Shop routes
```

Die konkrete Implementierung muss mit der verwendeten Laravel-Version und dem lokalen Webserver/Docker-Setup kompatibel sein.

## Asset-Regel

Globale UI-Assets bleiben zentral.

Fachliche Assets werden nach Anwendungskontext organisiert.

```text
shared/
crm/
portal/
shop/
```

Ein gemeinsames Theme darf von allen Kontexten genutzt werden.
