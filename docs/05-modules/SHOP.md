# Shop

## Ziel

Der Shop ist ein weiterer Anwendungskontext innerhalb derselben Laravel-Anwendung.

Er soll langfristig auf gemeinsame Identität und gemeinsame Kunden-/Unternehmensdaten zugreifen können.

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

Der Shop darf gemeinsame Identitäts-, Company-, Contact-, Document- und Billing-Infrastruktur verwenden.

Er bekommt keine direkte Datenbankzugriffsschicht außerhalb Laravel.
