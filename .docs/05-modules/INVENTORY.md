# Warenwirtschaft / Inventory

## Status

Spätere Ausbaustufe.

## Architektur

Inventory bleibt Bestandteil des Modular Monoliths.

Voraussichtliche Bereiche:

- Products
- Product Variants
- Serialised Devices
- Stock
- Warehouses
- Purchasing
- Goods Receipt
- Delivery
- Suppliers

## Device Integration

Ein verkauftes Gerät soll langfristig als Device-Record existieren.

Danach kann ein ServiceContract daran angelegt bzw. aus dem Verkaufsprozess vorbereitet werden.

Das Gerät bleibt serialisiert und individuell nachvollziehbar.

## Keine Vorab-Übermodellierung

Inventory wird erst detailliert spezifiziert, wenn CRM/Service-Prozesse stabil genug sind.
