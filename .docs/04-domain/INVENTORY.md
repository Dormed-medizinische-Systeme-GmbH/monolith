# Domäne — Inventory (Warenwirtschaft / Katalog / Lager)

Autoritative deklarative Spec. Entscheidungen **D-099 – D-121**, **D-128 – D-131**
([`../07-decisions/grill-log.md`](../07-decisions/grill-log.md)).
Prinzipien: [`../05-modules/INVENTORY.md`](../05-modules/INVENTORY.md).

Modul: `packages/core/src/Modules/Inventory/` (Namespace `Dormed\Core\Modules\Inventory\`,
`depends_on: [Core]`, ADR-013).

> **Keine Legacy-Vorlage — und es wird auch keine geben (D-108).** Für diesen
> Bereich existiert **kein** Export unter `00-legacy/`: der Artikelstamm liegt in
> Sage/KHK, das mit D-068 ersatzlos abgelöst wird, und der Nutzer kann ihn
> **aktuell nicht beschaffen**. `INVENTORY.md` ist damit die einzige Domänen-Spec
> **ohne** Ist-Absicherung — Core, Service, Scheduling, Sales und Billing konnten
> jeweils gegen ein `*.xml`/`*.csv` geprüft werden, Inventory nicht.
>
> Zwei Konsequenzen, die bewusst getragen werden: die breit angelegte
> `Article`-Feldliste (D-115) muss **aus der Nutzung heraus** gekürzt werden statt
> aus dem Abgleich, und der benutzerdefinierte Feldkatalog (D-100/D-111/D-119)
> wird damit vom Komfort- zum **Sicherheitsnetz** — was hier fehlt, lässt sich
> ohne Migration nachtragen.

---

## ⭐ Leitmechanik — das Technikerlager **ist** die Positionsauswahl (D-109)

**Dies ist das tragende Konzept der Domäne. Alles andere ordnet sich ihm unter.**

Lagerabgang und Rechnungsposition sind **ein einziger Vorgang**, nicht zwei. Der
Techniker erfasst nicht getrennt „was ich abrechne" und „was ich verbraucht habe"
— er erfasst **einmal**, was er benutzt hat, und daraus entsteht beides. Bestand
und Abrechnung können damit konstruktionsbedingt nicht auseinanderlaufen.

```text
1. Übergabe     Büro/Lagerist bucht 3 Netzkabel
                Zentrallager ──> Technikerlager        (StockTransfer, D-114)
                                 │
                (Tage bis Wochen vergehen — bewusst entkoppelt)
                                 │
2. Einsatz      Techniker beim Kunden (Maintenance | ServiceCase)
                Positionen ──> „+" ──> Modal mit Tabs   (D-116)
                                 │
                                 └─ Tab „Artikel" zeigt NUR seinen Bestand
                                    er wählt: 1 Netzkabel
                                 │
3. Ergebnis     ein Vorgang, zwei Wirkungen:
                ├─ StockMovement  Abgang Technikerlager     (D-102)
                └─ LineItem       article_id + Preis-Snapshot (D-104)
                                  ──> Billing (D-043/D-057)
```

### Die Auswahl im Tab „Artikel" ist doppelt eingeschränkt

1. **Fachlich** — nur Artikel mit `Article.is_service_item = true`. Der Techniker
   sieht nicht den ganzen Handelswarenkatalog.
2. **Besitzrechtlich** — nur der Bestand **seines eigenen Lagers**. Das Lager wird
   **aus dem eingeloggten Nutzer abgeleitet** (`warehouses.responsible_user_id =
   auth()->id()`, Typ `techniker`). **Es gibt keine Lagerauswahl im
   Erfassungsdialog.** Ein Techniker kann nichts abrechnen, was er nicht hat.

### Zwei Erfassungsmodi

| `Article.is_serial_tracked` | Erfassung | Menge |
| --- | --- | --- |
| `false` | Artikel + **Menge** | frei — „Techniker kriegt 3 Kabel, benutzt hier eins, da eins" |
| `true` | **exaktes Exemplar** per eindeutigem Identifier (Seriennummer) | immer 1 |

### UI-Vorgabe (verbindlich, D-109/D-116)

**Kein klassisches Dropdown in der Positionszeile.** Ein **„+"-Button öffnet ein
Modal**, das das **Inventar des Mitarbeiters** darstellt — er wählt daraus aus,
*was* er benutzt hat und *in welcher Menge*. Die Liste ist ein **Bestandsbild**,
kein Katalog-Picker.

---

## Positionsmodal — Registerstruktur (D-116)

| Tab | Quelle | Bestandsbezug | Position bekommt |
| --- | --- | --- | --- |
| **Leistungen** | `Offering`-Katalog (D-117) | – | `offering_id` |
| **Artikel** | Bestand des **eigenen** Technikerlagers (D-109) | ✓ | `article_id` (+ `stock_movement_id`) |
| **Sonstiges** | **fest im Code** (D-118) | – | weder noch — freie Position |

Die Einschränkung „nur eigener Lagerbestand" gilt **ausschließlich für den
Artikel-Tab**. Leistungen sind nicht bestandsgeführt; „Sonstiges" ist bewusst
unbeschränkt. Genau deshalb drei Tabs statt einer Liste.

Tab-Liste ist erweiterbar („für den Anfang", D-116) — jede Erweiterung = eigene D-NNN.

### „Sonstiges" (D-118)

Fest im Code definiert, **keine** Katalog-Datensätze. Für den Anfang **ein**
Eintrag:

| Schlüssel | Verhalten |
| --- | --- |
| `diverse` | freie Position: Preis, Menge und Beschreibung frei eingebbar |

Eine so erfasste Position hat **weder** `article_id` **noch** `offering_id` — genau
der Fall, für den `line_items.article_id` nullable bleiben muss.

### Nicht berechnete Positionen (D-129)

Ein Teil wird verbaut, aber nicht berechnet — Garantie, Kulanz
(`ServiceCase.goodwill`) oder abgedeckt durch `contract_type = full_service` (D-079):

1. Es entsteht eine **normale Position** mit `article_id` und **echtem
   Lagerabgang**. Das Teil ist verbraucht, egal wer es zahlt.
2. Die Position trägt `is_chargeable = false` plus `non_charge_reason`
   (`garantie` · `kulanz` · `vertrag`) — **auswertbar**, nicht nur ein Preis von 0.
3. Die Position wird **in die Rechnung übernommen und dort als „nicht berechnet"
   ausgewiesen** (Nutzerergänzung) — sie verschwindet nicht.

Punkt 3 ist der Kern: der Kunde sieht **auf der Rechnung**, welche Leistung er
erhalten und was sie ihn nicht gekostet hat. Punkt 2 beantwortet die Frage, was
Kulanz und Garantie im Jahr gekostet haben — mit `unit_price = 0` ginge das nicht.

Folge für `BILLING.md`: `InvoiceItem` trägt dieselben zwei Felder; `net_amount`
und `tax_amount` sind `0`, die Position zählt **nicht** in die Summen, bleibt aber
als Zeile erhalten.

---

## Katalog — Artikel

### `ArticleGroup` (Artikelgruppe)

**Hierarchisch, ohne Feldvererbung** (D-110). Der Baum dient **ausschließlich**
Navigation und Filterung; das Feldset kommt **nur** aus der Gruppe, in der der
Artikel tatsächlich liegt. Obergruppen vererben **nichts**.

| Feld | Typ | Null | Notiz |
| --- | --- | :-: | --- |
| `parent_id` | FK → `article_groups` | ✓ | Baum, nur Navigation (D-110) |
| `name` | string | – | |
| `description` | text | ✓ | |
| `position` | smallint | ✓ | Sortierung unter dem Elternknoten |
| `is_active` | boolean | – | |

Bewusst in Kauf genommen: gemeinsame Felder müssen je Gruppe erneut angelegt
werden (`Netzkabel` und `USB-Kabel` brauchen beide „Länge"). Gegenwert: das
effektive Feldset wird **abgelesen**, nicht über den Baum **berechnet**.

### `Article` (Artikel)

Feste Felder, die **jeder** Artikel hat — unabhängig von der Gruppe, nicht
löschbar (D-115). Bewusst breit angelegt; **Reduktion ist vorgesehen**, jede
Streichung wird als D-NNN vermerkt.

| Feld | Typ | Null | Notiz |
| --- | --- | :-: | --- |
| `article_group_id` | FK → `article_groups` | – | bestimmt den Feldkatalog (D-100) |
| `article_number` | string | – | **unique**. Gehört zum Artikel, **nicht** zum Exemplar (D-099) |
| `name` | string | – | Bezeichnung (Nutzer) |
| `description` | text | ✓ | |
| `is_serial_tracked` | boolean | – | Checkbox „seriennummernpflichtig" (Nutzer). Steuert, ob Exemplare existieren |
| `is_service_item` | boolean | – | servicerelevant ⇒ erscheint im Artikel-Tab des Technikers (D-109) |
| `is_active` | boolean | – | |
| `unit` | string | – | Stk / m / kg / Pauschale — fließt in `line_items.unit` |
| `cost_center` | string | ✓ | Kostenstelle (Nutzer). Anknüpfungspunkt für `BILLING.md` #2 (Erlöskonto/DATEV) |
| `tax_category` | enum | – | `standard_19` · `reverse_charge` · `export_tax_free` · `other_tax_free` — Vorbelegung, Werte aus D-074 |
| `tax_rate` | decimal(5,2) | – | z. B. `19.00` — Vorbelegung |
| `sale_price` | decimal(12,2) | – | Listenverkaufspreis netto (D-104) |
| `purchase_price` | decimal(12,2) | ✓ | Einkaufspreis (D-104), speist `OpportunityItem.unit_cost` |
| `manufacturer` | string | ✓ | ← vom `Device` herübergezogen (D-099) |
| `model_name` | string | ✓ | ← vom `Device` herübergezogen (D-099) |
| `manufacturer_article_number` | string | ✓ | Herstellerartikelnummer, ≠ eigene `article_number` |
| `ean` | string | ✓ | |
| `weight_kg` | decimal(10,3) | ✓ | |
| `min_stock` | decimal(10,2) | ✓ | Mindestbestand — Auslöser für Nachbestellung/Meldung |
| `notes` | text | ✓ | |

`SoftDeletes` (D-018), `TracksBlame`.

**Beziehungen:** `group()` `belongsTo`, `items()` `hasMany` `Device` (nur bei
`is_serial_tracked`), `movements()` `hasMany` `StockMovement`.

**Preisverhalten (D-104):** Keine Preislisten-Tabelle, keine Gültigkeitszeiträume,
keine Kunden-/Mengenstaffel. Beim Einfügen in eine Position wird der Preis
**gesnapshottet** — exakt das Muster aus D-065 und den Invoice-Snapshots (D-093).
Eine spätere Preisänderung wirkt **nie** rückwirkend.

`service_prices` (Wartungspauschale, Stundensatz — D-062/D-065) und `travel_zones`
bleiben davon **unberührt**: Dienstleistungspreise mit Tarifstufen und
Vertragsfixierung, keine Artikelpreise.

### Benutzerdefinierter Feldkatalog (D-100 / D-111 / D-119)

Die Feldliste einer Artikelgruppe ist **im UI pflegbar**: Feld hinzufügen,
benennen, Typ wählen, Optionen setzen. Alle Artikel/Exemplare der Gruppe erben es.

> **Bewusste Ausnahme zu D-094** (VARCHAR + CHECK, Wertelistenänderung = Migration).
> Benutzerdefinierte Felder können per Definition keine Migration je Änderung
> haben. Die Ausnahme ist **eng begrenzt** auf diesen Feldkatalog — alle fachlich
> festen Enums (Status, Bewegungsarten, Lagertypen …) bleiben unter D-094.

| Feld der Definition | Typ | Notiz |
| --- | --- | --- |
| `article_group_id` | FK | |
| `key` / `label` | string | technischer Schlüssel + Anzeigename |
| `type` | enum | siehe Typtabelle |
| `scope` | enum `article` \| `item` | **auf welcher Stufe das Feld lebt** (D-119) |
| `is_mandatory` | boolean | Pflichtfeld |
| `validation_regex` | string | ✓ optionales Muster (D-111) |
| `options` | json | ✓ nur bei `type = select`: die Werteliste |
| `position` | smallint | Reihenfolge in der Maske |

**Typen (D-111):** `string` · `text` · `integer` · `decimal` · `date` ·
`boolean` · `select` (feste Werteliste, beim Anlegen des Feldes selbst definiert).

**`scope` (D-119)** — die Nutzerbeispiele fallen auseinander:

| Beispiel | `scope` | Warum |
| --- | --- | --- |
| MAC-Adresse, Ausstattung | `item` | je Einzelstück verschieden, erfasst **beim Wareneingang je Stück** |
| Länge, Gewicht, Anzahl Kanäle | `article` | alle Exemplare identisch — Erfassung je Stück wäre stumpfe Wiederholung |

`scope = item` ist nur zulässig, wenn `Article.is_serial_tracked = true` — sonst
gibt es keine Exemplare, die den Wert tragen könnten (DB-CHECK + Validierung).

**Regex als Integritätsgrenze (D-111):** Das optionale Muster wird **als
Postgres-CHECK-Constraint** durchgesetzt. Das ist der Bogen zurück zu **ADR-007**
(„Postgres als zusätzliche Integrity Boundary") und zum Geist von D-094: der
Nutzer bekommt die *Definition* der Felder in die Hand, die Datenbank behält die
*Durchsetzung* — ein benutzerdefiniertes Feld bekommt einen benutzerdefinierten
Constraint statt gar keinen.

---

## Katalog — Leistungen (D-117)

Leistungen sind **nicht** Artikel mit einem „nicht physisch"-Häkchen, sondern ein
**eigener, paralleler Katalog**. Aufbau analog, aber **eine Stufe kürzer**:

```text
Artikel:    ArticleGroup  →  Article  →  Exemplar (Device)    3 Stufen (D-099)
Leistung:   OfferingGroup →  Offering                         2 Stufen
```

Die dritte Stufe entfällt zwangsläufig — eine Leistung hat kein physisches
Einzelstück. Die `OfferingGroup` definiert den Feldkatalog daher **direkt für die
Leistungen** darunter: dieselbe Mechanik wie D-100/D-111 (Typvorgabe,
`mandatory`, optionales Regex), nur mit **einem** möglichen Ziel statt zweien —
`scope` entfällt hier.

**Benennung:** `Offering` / `OfferingGroup` statt `Service`, weil `Modules\Service\`
bereits das Servicemodul (Wartung/Servicefall) ist.

| `Offering` | Typ | Null | Notiz |
| --- | --- | :-: | --- |
| `offering_group_id` | FK → `offering_groups` | – | bestimmt den Feldkatalog |
| `number` | string | – | unique |
| `name` | string | – | |
| `description` | text | ✓ | |
| `unit` | string | – | Std / Pauschale / km |
| `cost_center` | string | ✓ | analog `Article` |
| `tax_category` / `tax_rate` | enum / decimal(5,2) | – | Vorbelegung, D-074 |
| `sale_price` | decimal(12,2) | – | Snapshot in die Position (D-104) |
| `is_active` | boolean | – | |

`OfferingGroup` ist **flach oder hierarchisch analog `ArticleGroup`** (D-110 gilt
sinngemäß). `SoftDeletes`, `TracksBlame`.

---

## Exemplar — `Device` (D-099 / D-121)

**Das Exemplar ist `Device`.** Es gibt **kein** separates `InventoryItem`: der
Nutzer unterscheidet fachlich nicht zwischen „Gerät im Lager" und „Device beim
Kunden" — es ist **ein** Datensatz mit Lebenszyklus vom Wareneingang bis zur
Verschrottung. Die Seriennummer existiert damit genau **einmal** im System.

> **Modulumzug (D-121):** `Device` zieht von `Modules\Service\` nach
> `Modules\Inventory\` (`DeviceComponent` entfällt mit D-131 ganz). Sonst entstünde ein zyklischer
> Modulgraph (`Service → Inventory` über `line_items.article_id`,
> `Inventory → Service` über den Wareneingang), was `ARCHITECTURE.md` §5 verbietet.
> Service verliert dabei keine Fachlichkeit — nur der Datensatz des physischen
> Geräts liegt eine Etage tiefer.

### Änderungen an der bestehenden `Device`-Feldliste

| Feld | Änderung | Grund |
| --- | --- | --- |
| `article_id` | **neu**, FK → `articles` | Katalogbezug (D-099) |
| `article_number` | **entfällt** | gehört zum Artikel (D-099, Nutzer explizit) |
| `manufacturer` | **entfällt** | → `Article` (Modelleigenschaft) |
| `model_name` | **entfällt** | → `Article` (Modelleigenschaft) |
| `location_id` | **wird nullable** | ein Gerät im Lager hat keinen Kundenstandort |
| `warehouse_id` | **neu**, FK → `warehouses`, nullable | Gegenstück zu `location_id` |
| `form_factor` | **bleibt** | D-105 bestätigt D-063/D-080 ausdrücklich |
| `imaging_type` | **bleibt** | D-105 |

| `parent_device_id` | **neu (D-131)**, FK → `devices`, nullable | Elternexemplar, wenn die Komponente verbaut ist |
| `position` | **neu (D-131)**, smallint, nullable | Sortierung unter dem Elternexemplar (ersetzt `DeviceComponent.position`, „Sonde 1..5") |

**DB-CHECK (ADR-007 / D-094), dreiwertig (D-131):** genau **eines** von
`location_id` / `warehouse_id` / `parent_device_id` ist gesetzt. Ein Exemplar steht
im Lager, beim Kunden, **oder** ist an einem anderen Exemplar verbaut — nie
mehreres, nie nichts.

### Komponenten sind selbst Exemplare (D-131)

`DeviceComponent` aus D-034 **entfällt ersatzlos**. Eine Sonde ist kein Attribut
eines Geräts, sondern ein physisches Einzelstück mit eigenem Lebenszyklus: sie
liegt im Lager, wird verkauft, angebaut, abgebaut, ersetzt, eingeschickt.

```text
Ultraschallsystem   parent = null,   location_id = Praxis
  ├─ Sonde 1        parent = System
  ├─ Sonde 2        parent = System
  └─ Drucker        parent = System
```

**Anbau = Umbuchung** (Lager → Gerät), **Ausbau = Umbuchung** (Gerät → Lager oder
Reparaturlager via D-130). Keine Sonderlogik, dieselben Ledger-Zeilen wie alles
andere. Der effektive Standort einer verbauten Komponente ergibt sich über die
Elternkette.

Die alten `DeviceComponent`-Felder finden alle ein neues Zuhause:

| `DeviceComponent`-Feld (D-034) | neues Zuhause |
| --- | --- |
| `type` (`probe`/`printer`/`cart`/`gdt`/`other`) | **`ArticleGroup`** — Katalogklassifikation (D-099/D-110) |
| `article_number` | **`Article.article_number`** |
| `description` | **`Article.name`** |
| `serial_number` | **`Device.serial_number`** |
| `license` (nur `gdt`) | **benutzerdefiniertes Feld**, `scope = item`, an der Artikelgruppe „SonoGDT" (D-111/D-119) |
| `position` (Sonde 1..5) | **`Device.position`** |

Der `license`-Fall zeigt, warum der Feldkatalog trägt: ein Feld, das nur für **eine**
Komponentenart existiert, war vorher eine dauerhaft leere Spalte für alle anderen.

**Warum `form_factor` am Exemplar bleibt (D-105):** Die Preisfindung für die
Wartungspauschale (`service_prices.form_factor`) bleibt so unabhängig davon, ob
ein gewartetes Gerät überhaupt einen Artikel-Datensatz hat (→ D-107) — und sie
greift nie auf ein im UI frei definierbares Feld zu.

Die benutzerdefinierten Felder mit `scope = item` (MAC-Adresse, Ausstattung, …)
hängen am Exemplar und werden **beim Wareneingang je Stück** erfasst.

---

## Läger und Bestand

### `Warehouse` (D-101)

| Feld | Typ | Null | Notiz |
| --- | --- | :-: | --- |
| `name` | string | – | |
| `type` | enum `zentral` \| `techniker` \| `leihpool` \| `reparatur` | – | D-094: VARCHAR + CHECK |
| `responsible_user_id` | FK → `users` | ✓ | **gesetzt bei `type = techniker`** — Grundlage der Ableitung in D-109 |
| `is_active` | boolean | – | |

| Typ | Bedeutung |
| --- | --- |
| `zentral` | physisches Hauptlager am Firmenstandort |
| `techniker` | je Techniker ein Lager. **Hängt am `User`, nicht am Fahrzeug** (Nutzerkorrektur) — der Techniker hat sein Lager unabhängig davon, in welchem Auto er sitzt |
| `leihpool` | Leih-/Austauschgeräte (→ `ServiceCase.loan_device_required`) |
| `reparatur` | physisch da, aber nicht frei verfügbar: Kundengeräte in Reparatur, Retouren, Defektbestand |

Ein `Warehouse` ist **kein** `Location`: `locations` sind Kundenstandorte (D-007),
Läger sind Dormed-intern.

### `StockMovement` — der Ledger (D-102)

**Der Bestand ist keine Spalte.** Er ist die Summe über einen unveränderlichen
Bewegungs-Ledger. Direkte Folge aus **D-093**, das denormalisierte Cache-Spalten
ausdrücklich streicht („bei Performance-Bedarf später eine Materialized View,
keine denormalisierte Spalte") — dieselbe Begründung wie bei
`ServiceContract.next_due_at`.

| Feld | Typ | Null | Notiz |
| --- | --- | :-: | --- |
| `article_id` | FK → `articles` | – | |
| `device_id` | FK → `devices` | ✓ | gesetzt bei seriennummernpflichtigen Artikeln |
| `warehouse_id` | FK → `warehouses` | – | betroffenes Lager |
| `quantity` | decimal(10,2) | – | vorzeichenbehaftet: Zugang `+`, Abgang `−`. Bei Exemplaren immer `±1` |
| `type` | enum | – | `wareneingang` · `umbuchung` · `entnahme` · `ruecknahme` · `reservierung` · `inventurdifferenz` · `verschrottung` |
| `sourceable_type` / `sourceable_id` | morph | – | der auslösende Beleg (GoodsReceipt, StockTransfer, LineItem, Reservation, ReservationReturn, InventoryCountSheet) |
| `negative_override` | boolean | – | bewusste Übersteuerung eines Minusbestands (D-112) |
| `booked_at` | datetime | – | |

`TracksBlame` — **kein** `SoftDeletes`: ein Ledger wird nicht gelöscht, sondern
gegengebucht.

Jeder bestandsverändernde Vorgang erzeugt Ledger-Zeilen und ändert **nie** eine
Bestandszahl direkt. Eine Umbuchung erzeugt `2n` Zeilen (Abgang + Zugang).

### Minusbestand (D-112)

Eine Buchung, die den Bestand unter null drücken würde, wird **blockiert** — aber
mit **bewusstem Übersteuerungsschritt**: Warnung, explizite Bestätigung, Vermerk
in `negative_override` samt auslösendem User (`TracksBlame`), Meldung.

Damit wird der Techniker beim Kunden nie hart blockiert, der Bestand bildet
trotzdem die Realität ab, und jeder Minusfall ist namentlich nachvollziehbar statt
still. Fällt zusätzlich spätestens bei der Monatsinventur auf.

---

## Vorgänge — alles ist ein Beleg

Durchgängiges Prinzip dieser Domäne, übernommen aus Billing (D-069–D-074, Storno
als eigenes Dokument statt Mutation): **jeder bestandsverändernde Vorgang ist ein
eigener Belegdatensatz mit eigener Nummer**, der Ledger-Zeilen erzeugt. Kein
Vorgang mutiert einen anderen.

### `GoodsReceipt` — Wareneingang

Bringt Artikel ins Lager. Kopf mit Lieferantenbezug, Datum, Ziellager;
`n` Positionen.

| Erfassung | bei `is_serial_tracked = false` | bei `is_serial_tracked = true` |
| --- | --- | --- |
| Eingabe | Artikel + **Menge** | **je Stück ein Exemplar** anlegen |
| zusätzlich | – | alle Felder mit `scope = item` (MAC-Adresse, Ausstattung …, D-119), Pflichtfelder erzwungen |
| Ergebnis | eine Ledger-Zeile mit Menge | je Stück ein `Device` + eine Ledger-Zeile `±1` |

Es wird **immer ein bestimmtes Lager bebucht** (Nutzer explizit).

**Der Wareneingang bucht gegen offene Bestellpositionen ab** (D-128): er
referenziert eine `PurchaseOrder`, seine Positionen verweisen auf
Bestellpositionen. Ein Wareneingang ohne Bestellbezug bleibt für Sonderfälle
möglich (Rücklieferung, Direktbezug).

### `Supplier` — Lieferantenstamm (D-103, gültig)

**Eigenständige Tabelle**, ausdrücklich **kein** `type`-Feld auf `Company`.
Begründung: strikte Trennung zwischen **Kreditoren** (Lieferanten) und
**Debitoren** (Kunden). Das lässt **D-002** („Company ist der zentrale
Ankerpunkt, aktuell nur Kunden") unangetastet, statt es durch einen
Typ-Diskriminator aufzuweichen.

Eine Firma, die beides ist, existiert damit bewusst zweimal — Dublettenrisiko wird
gegen die klare Trennung eingetauscht.

### `PurchaseOrder` — Bestellung (D-128)

Der Nutzer hat sich gegen die empfohlene leichtgewichtige Variante und **für das
volle Bestellwesen** entschieden.

| Feld | Typ | Notiz |
| --- | --- | --- |
| `number` | string | Nummernkreis |
| `supplier_id` | FK → `suppliers` | |
| `ordered_at` | date | |
| `expected_at` | date | erwarteter Liefertermin — Grundlage der Terminverfolgung |
| `status` | enum `offen` \| `teilweise_geliefert` \| `geliefert` \| `storniert` | |
| `n` Positionen | | Artikel, Menge, vereinbarter EK |

Umfang: **Teillieferungen** (Bestellung bleibt offen, bis vollständig geliefert),
**Lieferterminverfolgung** über `expected_at`, **Rechnungsprüfung gegen die
Bestellung** (erwarteter vs. berechneter Preis).

> **Offene Restmenge wird berechnet**, nicht gespeichert:
> `bestellt − Σ eingegangen` je Bestellposition. D-093/D-102 gelten unverändert —
> das volle Bestellwesen ändert daran nichts.

### `StockTransfer` — Umbuchung (D-114)

Zwischen **beliebigen** Lägern, ausdrücklich auch Technikerlager → Technikerlager.

| Feld | Typ | Notiz |
| --- | --- | --- |
| `number` | string | Nummernkreis |
| `from_warehouse_id` / `to_warehouse_id` | FK → `warehouses` | |
| `transferred_at` | datetime | |
| `n` Positionen | | Artikel + Menge bzw. Exemplar |

Erzeugt `2n` Ledger-Zeilen. Ziel laut Nutzer: „Lagerbewegungen kleinlichst
nachvollziehen können, wenn man das wollte."

Dieser Beleg ist zugleich **Schritt 1 der Leitmechanik D-109** (Übergabe Büro →
Techniker).

**UI-Vorgabe (Nutzer):** Umbuchung funktioniert als **Massenaktion in der
Tabellenansicht** — Mehrfachmarkierung mehrerer Zeilen **und** Einzelsatz-Aktion,
nicht nur ein separates Formular.

### `PickupNote` — Abholbeleg (D-130)

Ein defektes Teil wird beim Kunden ausgebaut und mitgenommen. Das erzeugt einen
**Zugang im Reparaturlager** (`Warehouse.type = reparatur`) über einen eigenen Beleg.

- wird **vom Techniker beim Kunden erstellt**,
- wird **vor Ort unterschrieben** — Signaturfelder analog `MaintenanceReport`
  (D-045): `signature_image`, `signer_name`, `signed_at`,
- wird **mitgenommen** (Ausdruck/PDF für den Kunden),
- bucht die Positionen **automatisch auf das Reparaturlager**.

> **Nur Exemplare sind abholfähig.** Nutzer wörtlich: „um sie vom Kunden
> mitzunehmen, müssen diese beim Kunden existieren. Das heißt, es sind nur Geräte
> und Ausstattung davon betroffen, keine weiteren Artikel." Ein Abholbeleg kann
> ausschließlich `Device`-Exemplare referenzieren — nicht bestandsgeführte
> Kleinteile am Kundenstandort haben dort nie als Datensatz existiert und sind
> daher nicht abholfähig.

**Das greift mit D-131 ineinander:** weil Komponenten (Sonden, Drucker, Wagen) dort
selbst zu Exemplaren werden, ist „Gerät **und Ausstattung**" automatisch genau die
Menge der Datensätze, die beim Kunden existieren. Ohne D-131 wäre die Ausstattung
nicht abholfähig gewesen.

**Buchungswirkung:** das Exemplar wechselt von `location_id` auf `warehouse_id`
(Reparaturlager) — dieselbe Mechanik wie jede andere Bewegung, mit
`StockMovement.sourceable` auf den Abholbeleg.

### `Reservation` + `ReservationReturn` — Leihgeräte (D-106)

Ein Leihgerät beim Kunden wird **nicht** über einen Status am Exemplar geführt,
sondern über eine **Reservierung**: ein eigener Vorgang, der das Exemplar aus dem
verfügbaren Bestand nimmt und es dem Kunden/Standort zuordnet.

```text
Reservation                       ReservationReturn
  number                            number
  company_id / location_id          reservation_id  ──> Reservation
  service_case_id (nullable)        returned_at
  reserved_at                       n Positionen
  expected_return_at
  n Positionen                      1 : n
  (Status abgeleitet)
```

**Die Rückgabe ist ein eigener Beleg**, kein Feld am Reservierungsobjekt:

1. **Teilrückgaben.** Umfasst eine Reservierung mehrere Exemplare (Gerät + Sonde +
   Wagen), kann ein `zurueck_am`-Feld „zwei von drei zurück" nicht abbilden.
2. **Belegprinzip.** Nur ein eigener Beleg hat eine eigene Nummer, die im Dokument
   referenziert und unterschrieben werden kann.
3. **Ledger-Konsistenz.** Ausgabe und Rückgabe sind ohnehin je eine Bewegung; der
   Beleg gibt der Rückgabebewegung eine saubere Referenz, ein Feld-Update hätte keine.

Der Status (`offen` / `teilweise_zurueck` / `erledigt`) ist **abgeleitet**, nicht
gespeichert — konsistent zu D-093/D-102.

### `InventoryCount` + `InventoryCountSheet` — Monatsinventur (D-113)

```text
InventoryCount (Inventurlauf)          ← monatlich, Scheduler, zum Stichtag
   └── n InventoryCountSheet (Zählauftrag je Lager)
          ├─ warehouse_id
          ├─ assigned_user_id   Techniker für sein Lager,
          │                     backoffice fürs Zentrallager (D-125:
          │                     Rolle accounting ersatzlos gestrichen)
          ├─ status: offen → eingereicht → gebucht
          └─ n Zählpositionen (Artikel bzw. Exemplar, Ist-Menge)
                 │
                 └── beim Buchen je Differenz:
                     StockMovement type = inventurdifferenz
                     sourceable = das Sheet
```

- Ein **Laravel-Scheduler** erzeugt den Inventurlauf monatlich zum Stichtag und
  darunter je aktivem Lager einen Zählauftrag mit Zuständigem (Nutzer: „definitiv
  Scheduler").
- Der Zuständige erfasst Ist-Mengen (bzw. bestätigt/vermisst Exemplare) und reicht
  ein. **Der Techniker bekommt dafür keinen Nav-Eintrag** — seine offene Inventur
  erscheint als Aufgabe/Kachel im Cockpit (D-127,
  [`../09-ui/NAVIGATION.md`](../09-ui/NAVIGATION.md)).
- Beim Buchen entsteht **je Differenz eine Ledger-Zeile** mit Referenz auf den
  Zählauftrag. Die geforderte Nachvollziehbarkeit — „wer bucht was minus in den
  monatlichen Inventuren" — ist damit vollständig **aus dem Ledger** auswertbar;
  es entsteht **kein zweites Protokoll** neben `stock_movements`.
- Der Zählauftrag ist der **Beleg** — dasselbe Prinzip wie Rückgabebeleg und
  Billing-Storno.
- Auswertung je Lager und Monat läuft über die Zählaufträge.

> **Benachrichtigungen:** Ein volles Reminder-/Benachrichtigungssystem ist laut
> `SCHEDULING.md` #4 bewusst als Plattform-Thema **vertagt**. Die Inventur- und
> Minusbestandsmeldung darf **kein paralleles Zweitsystem** aufmachen, sondern
> hängt sich dort an, sobald es existiert.

---

## Auswirkungen auf bestehende Specs

Diese Runde revidiert bereits abgeschlossene Bereiche. Die betroffenen Dokumente
sind entsprechend angepasst:

| Dokument | Änderung | Entscheidung |
| --- | --- | --- |
| `SERVICE.md` — `Device` | `article_id` neu; `article_number`/`manufacturer`/`model_name` entfallen; `location_id` nullable + `warehouse_id`; Modul → Inventory | D-099, D-121 |
| `SERVICE.md` — `line_items` | `article_id`, `offering_id`, `stock_movement_id` neu (alle nullable) | D-109, D-116, D-118 |
| `SERVICE.md` — Offene Punkte #7 | gelöst | D-109 |
| `SALES.md` — `OpportunityItem` | `product_id` → `article_id` (+ `offering_id`); Katalog existiert jetzt | D-099, D-117 |
| `SALES.md` — Offene Punkte #5 | gelöst | D-099 |
| `SERVICE.md` — `DeviceComponent` | **entfällt ersatzlos** — Komponenten sind selbst Exemplare mit `parent_device_id` | D-131 |
| `SERVICE.md` — `line_items` | `is_chargeable`, `non_charge_reason` neu | D-129 |
| `BILLING.md` — `InvoiceItem` | `is_chargeable`, `non_charge_reason` neu; Position mit 0 zählt nicht in die Summen | D-129 |
| `05-modules/INVENTORY.md` | Status nicht mehr „spätere Ausbaustufe" | D-099 ff. |
| `ARCHITECTURE.md` §5 | Modulgraph: `Service → Inventory`, `Sales → Inventory` | D-121 |

---

## Offene Punkte

| # | Punkt | Wohin |
| --- | --- | --- |
| 1 | **Fremdgeräte ohne Artikelstamm** — ist `Device.article_id` `NOT NULL` oder nullable? Dormed wartet Geräte, die es nie verkauft hat | **eigener Detaildurchgang**, Nutzer braucht Vorlauf. Ohne Schema-Abgleich zu beantworten (D-108 entfällt), migrationsrelevant (D-107) |
| 2 | ~~Sage/KHK-Artikelstamm-Export als Ist-Referenz~~ | ⛔ **nicht beschaffbar** (D-108). Abgleich nach D-098-Verfahren entfällt auf unbestimmte Zeit |
| 3 | ~~Bestellwesen ja/nein~~ | ✅ **volles Bestellwesen** (D-128, revidiert D-120) |
| 4 | ~~Garantie-/Kulanzteile, verbaut aber nicht abgerechnet~~ | ✅ gelöst (D-129) |
| 4a | ~~Ausgebaute Defektteile~~ | ✅ gelöst — Abholbeleg ins Reparaturlager (D-130) |
| 5 | ~~`DeviceComponent` vs. serialisiertes Exemplar~~ | ✅ gelöst — Komponenten **sind** Exemplare (D-131) |
| 6 | **UI/UX-Verpackung der Inventur** — geführter Fenster-Flow, Aufgabenzuweisung, Meldeweg | Nutzer: „die Frage ist nur, wie man das am Ende im UI und per UX verpackt" (D-113) |
| 7 | **Reduktion der `Article`-Feldliste** — bewusst breit angelegt, Streichungen folgen | laufend, je Streichung eine D-NNN (D-115) |
| 8 | Nummernkreise für die neuen Belege (GoodsReceipt, StockTransfer, Reservation, …) | Format analog D-067 bei Umsetzung |
