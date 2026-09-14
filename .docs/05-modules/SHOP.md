# Shop

> **Kurswechsel 2026-09-12** (ADR-011, ADR-019, ADR-020): Der Shop ist **kein**
> Anwendungskontext mehr innerhalb einer gemeinsamen Laravel-Anwendung, sondern die
> eigenständige App `shop.dormed.de` — bereits **live**, Inertia.js + Svelte, aktuell in
> einem separaten Repo außerhalb dieses Monorepos. Wird später hereinmigriert
> (Code + Composer-Wiring nach `core`). Aktuell **kein** aktiver Aufbau — nur
> architektonisch vorbereitet (Platzhalter unter `shop.dormed.de`, Domain `shop.dormed.de`,
> ADR-021). Bau-Fokus liegt auf `core` + `erp.dormed.de` (ADR-020).

## Ziel

Der Shop ist eine eigenständige Laravel-App (`shop.dormed.de`, ADR-011).

Er soll auf gemeinsame Identität (soweit mit Portal geteilt, ADR-016) und gemeinsame
Kunden-/Unternehmensdaten aus `core` zugreifen können.

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
Billing-Infrastruktur aus `core` verwenden (ADR-033).

Er bekommt keine direkte Datenbankzugriffsschicht außerhalb `core`.
