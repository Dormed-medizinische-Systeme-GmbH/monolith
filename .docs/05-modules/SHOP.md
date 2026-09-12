# Shop

> **Kurswechsel 2026-09-12** (ADR-011, ADR-019, ADR-020): Der Shop ist **kein**
> Anwendungskontext mehr innerhalb einer gemeinsamen Laravel-Anwendung, sondern die
> eigenständige App `apps/shop` — bereits **live**, Inertia.js + Svelte, aktuell in
> einem separaten Repo außerhalb dieses Monorepos. Wird später hereinmigriert
> (Code + Composer-Wiring nach `packages/core`). Aktuell **kein** aktiver Aufbau — nur
> architektonisch vorbereitet (Platzhalter unter `apps/shop`, Domain `shop.dormed.de`,
> ADR-021). Bau-Fokus liegt auf `packages/core` + `apps/crm` (ADR-020).

## Ziel

Der Shop ist eine eigenständige Laravel-App (`apps/shop`, ADR-011).

Er soll auf gemeinsame Identität (soweit mit Portal geteilt, ADR-016) und gemeinsame
Kunden-/Unternehmensdaten aus `packages/core` zugreifen können.

## Keine voreilige Modellierung

Shop-spezifische Daten werden erst modelliert, wenn der Shop fachlich konkretisiert wird.

Voraussichtliche Bereiche:

- Products
- Catalog
- Cart
- Orders
- Payments
- Shipping
- Customer Account

## Shared Core

Der Shop darf gemeinsame Identitäts-, Company-, Contact-, Document- und
Billing-Infrastruktur aus `packages/core` verwenden (ADR-013).

Er bekommt keine direkte Datenbankzugriffsschicht außerhalb `packages/core`.
