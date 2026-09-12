# Warenwirtschaft / Inventory

## Status

**Spezifiziert** (Grill-Runde 2026-09-12/13, D-099 – D-121). Autoritative Spec mit
Feldform, Enums und Regeln: [`../04-domain/INVENTORY.md`](../04-domain/INVENTORY.md).

Dieses Dokument hält nur die **Prinzipien** fest — die Feldebene steht in der Spec.

> **Kurswechsel gegenüber dem vorherigen Stand.** Inventory war als „spätere
> Ausbaustufe" markiert, mit dem Grundsatz „keine Vorab-Übermodellierung". Der
> Grundsatz bleibt richtig, greift hier aber nicht: die Ist-Situation ist bereits
> eine vollständig genutzte Warenwirtschaft (Artikelgruppen, Artikel, Bestand,
> seriennummernpflichtige Exemplare, mehrere Läger, Umbuchung, Wareneingang). Es
> wird nichts auf Vorrat modelliert, sondern ein produktiver Prozess abgebildet.
> Ausgelöst hat die Runde, dass zwei fertige Specs hängende Referenzen hierher
> hatten (`SALES.md` #5, `SERVICE.md` #7).

## Architektur

Inventory bleibt Bestandteil des Modular Monoliths:
`packages/core/src/Modules/Inventory/`, `depends_on: [Core]` (ADR-013).

**Der Modulgraph hat sich durch D-121 gedreht:**

```text
Inventory ──> Core
Service   ──> Inventory, Core
Sales     ──> Inventory, Core
```

`Device` lebt seit D-121 **hier**, nicht mehr im Servicemodul; `DeviceComponent`
ist mit D-131 ganz entfallen (Komponenten sind selbst Exemplare). Sonst entstünde ein Zyklus: `line_items` braucht einen Artikelbezug
(Service → Inventory), und der Wareneingang erzeugt Geräte (Inventory → Service).
Das verbietet `ARCHITECTURE.md` §5.

## Leitprinzip — ein Vorgang, nicht zwei

**Lagerabgang und Rechnungsposition entstehen gemeinsam.** Der Techniker erfasst
einmal, was er benutzt hat; daraus entstehen die Bestandsbewegung *und* die
Position. Die Auswahl zeigt ihm ausschließlich den Bestand **seines eigenen
Lagers**, abgeleitet aus dem eingeloggten Nutzer.

Damit können Bestand und Abrechnung konstruktionsbedingt nicht auseinanderlaufen,
und niemand kann abrechnen, was er nicht hat. Details und UI-Vorgaben: D-109/D-116
in der Spec.

## Weitere Prinzipien

- **Alles ist ein Beleg.** Jeder bestandsverändernde Vorgang ist ein eigener
  Datensatz mit eigener Nummer, der Ledger-Zeilen erzeugt — Wareneingang,
  Umbuchung, Reservierung, Rückgabe, Zählauftrag. Kein Vorgang mutiert einen
  anderen. Übernommen aus Billing (Storno als eigenes Dokument, D-069–D-074).
- **Der Bestand ist keine Spalte**, sondern die Summe über einen unveränderlichen
  Bewegungs-Ledger (D-102, Folge aus D-093).
- **Der Nutzer definiert Felder, die Datenbank erzwingt sie.** Der Feldkatalog je
  Artikelgruppe ist im UI pflegbar (bewusste Ausnahme zu D-094), das optionale
  Regex wird trotzdem als Postgres-CHECK durchgesetzt (ADR-007).
- **Kreditoren ≠ Debitoren.** Lieferanten sind eine eigene Tabelle, kein
  `type`-Feld auf `Company` (D-103). `Company` bleibt kundenseitig (D-002).

> **Ohne Legacy-Ist-Referenz (D-108).** Der Sage/KHK-Artikelstamm ist nicht
> beschaffbar. Diese Domäne ist als einzige nicht gegen einen Altsystem-Export
> geprüft — Korrekturen kommen aus der Nutzung, nicht aus dem Abgleich.

## Bereiche

| Bereich | Stand |
| --- | --- |
| Artikelgruppen / Artikel / Feldkatalog | spezifiziert (D-099/D-100/D-110/D-111/D-115/D-119) |
| Leistungsgruppen / Leistungen | spezifiziert (D-117) |
| Serialisierte Exemplare (`Device`) inkl. Komponenten | spezifiziert (D-099/D-121/D-131) |
| Läger + Bestand (Ledger) | spezifiziert (D-101/D-102/D-112) |
| Wareneingang | spezifiziert, bucht gegen Bestellpositionen (D-128) |
| Umbuchung | spezifiziert (D-114) |
| Reservierung / Leihgeräte | spezifiziert (D-106) |
| Inventur | spezifiziert, UI/UX offen (D-113) |
| Abholbeleg (Rücknahme beim Kunden) | spezifiziert (D-130) |
| Lieferanten | Stamm beschlossen (D-103), Felder offen |
| Einkauf / Bestellung | spezifiziert — **volles** Bestellwesen (D-128) |
| Lieferschein / Warenausgang | noch nicht gegrillt |

## Device Integration

Ein verkauftes Gerät existiert als `Device`-Record — das war schon immer das Ziel
und ist mit D-099 eingelöst: es **ist** derselbe Datensatz, der im Wareneingang
entsteht. Danach kann ein `ServiceContract` daran angelegt bzw. aus dem
Verkaufsprozess vorbereitet werden (D-053). Das Gerät bleibt serialisiert und
individuell nachvollziehbar — über den gesamten Lebenszyklus, nicht erst ab
Auslieferung.
