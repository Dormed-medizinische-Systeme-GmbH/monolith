# UI — Navigation & Cockpit

Autoritative deklarative Spec. Entscheidungen **D-123 – D-127**
([`../07-decisions/grill-log.md`](../07-decisions/grill-log.md)).
Rollen/Berechtigungen: [`../03-security/IDENTITY_RBAC.md`](../03-security/IDENTITY_RBAC.md).

Gilt zunächst für `apps/crm` (aktueller Bau-Fokus, ADR-020). Die Verarbeitung —
Permission-Prüfung, Cockpit-Definitionen — liegt in `packages/core`; die App
enthält nur Views/Routen (ADR-011).

> **Bildreferenz:** `nav-referenz.png` in diesem Ordner (vom Nutzer beizulegen).
> Zeigt eine schmale vertikale Leiste, Icon über kurzem Label, kompakte Zeilen,
> `•••`-Overflow („More") am Ende der Liste.

---

## Grundsatz — Sichtbarkeit **ist** Berechtigung (D-123)

Jeder Nav-Eintrag deklariert die Permission, die er braucht. Gerendert wird über
`PermissionService::can()` (D-030). Es gibt **keine** zweite, parallele
Rolle→Blöcke-Konfiguration.

**Warum das der entscheidende Punkt ist:** „Nur Backoffice und Management
bekommen die Warenwirtschaft" wird nicht als Menü-Regel gepflegt, sondern **fällt
aus dem Permission-Katalog heraus**. Damit können Menü und tatsächliche Rechte
nicht auseinanderlaufen, und ein Direktlink auf eine nicht sichtbare Route liefert
konsequent **403** — statt einer voll funktionsfähigen Seite, die nur im Menü fehlt.

Ein Block wird **ausgeblendet, wenn keiner seiner Einträge sichtbar ist** —
Blöcke werden nicht separat konfiguriert, sie ergeben sich.

---

## Aufbau der Seitenleiste

```text
┌──────────────┐
│   Cockpit    │  ← immer, für alle (D-123)
│   Kalender   │  ← immer, für alle
├──────────────┤  ← Separator
│   CRM        │
│   …          │
├──────────────┤  ← Separator
│  fachliche   │
│  Blöcke je   │  ← dynamisch nach Abteilung
│  Abteilung   │
├──────────────┤  ← Separator
│     •••      │  ← Overflow, wenn die Liste zu lang wird
│              │
│      ⚙       │  ← immer unten, für alle (D-123)
└──────────────┘
```

| Position | Einträge | Sichtbar für |
| --- | --- | --- |
| **oben, fix** | **Cockpit**, **Kalender** | **alle Abteilungen** |
| Mitte | fachliche Blöcke, durch **Separatoren** getrennt | je Abteilung, permission-gefiltert |
| **unten, fix** | **⚙ Einstellungen** | **alle Abteilungen** |

Cockpit und Kalender sind für **jede** Abteilung sichtbar — das Cockpit ist
inhaltlich aber je Rolle verschieden (D-126). Die Einstellungen sind bewusst
**nicht weiter spezifiziert** (Nutzer).

### Visuelle Form

Nach der Bildreferenz: schmale vertikale Leiste, **Icon über kurzem Label**,
kompakte Zeilenhöhe, dezente Separatorlinien zwischen den Blöcken. Wird die Liste
für die Höhe des Viewports zu lang, klappt der Rest hinter einen
**`•••`-Overflow**, statt die Leiste scrollen zu lassen.

---

## Blöcke

Die Zuordnung folgt den fünf Rollen aus D-125. `geschaeftsfuehrung` sieht per
`['*']` alles.

| Block | Einträge | Abteilungen |
| --- | --- | --- |
| *(fix, oben)* | Cockpit · Kalender | **alle** |
| **CRM** | Firmen · Kontakte · Standorte | backoffice · sales · service · management |
| **Vertrieb** | Verkaufschancen | sales · management |
| **Service** | Servicefälle · Wartungen · Geräte · Serviceverträge | service · management |
| **Warenwirtschaft** | **Wareneingang · Inventur · Artikel · Leistungen · Items** | **backoffice · management** (Nutzervorgabe) |
| **Billing** | Rechnungen · Zahlungen · Mahnwesen | backoffice · management (war `accounting`, D-125) |
| *(fix, unten)* | ⚙ Einstellungen | **alle** |

Die Einträge des Warenwirtschaftsblocks sind **wörtlich die Nutzervorgabe**.
Weitere Kandidaten aus `INVENTORY.md` — **Umbuchung** (D-114), **Lieferanten**
(D-103), **Reservierungen** (D-106) — sind bewusst **noch nicht** eingeordnet;
siehe Offene Punkte.

### Techniker: kein Warenwirtschaftsblock (D-127)

Der Konflikt: D-113 verlangt, dass der **Techniker die Inventur seines eigenen
Lagers einreicht** — er bekommt aber keinen Warenwirtschaftsblock.

**Auflösung:** Sein Zugang ist **ausschließlich kontextuell**, nie als Menüpunkt:

1. **Offene Inventur** → **Aufgabe/Kachel im Cockpit** (D-126).
2. **Sein Bestand** → ausschließlich im **Positionsmodal** beim Einsatz
   (D-109, `INVENTORY.md`).

Die zugehörigen Permissions sind dadurch **feiner geschnitten** als ein pauschales
`inventory.*` — ein Techniker darf seinen Bestand lesen und seinen Zählauftrag
einreichen, ohne die administrativen Warenwirtschaftsansichten zu bekommen. Die
genaue Aufteilung fällt in die offene Permission-Definition (D-125).

---

## Cockpit (D-126)

Für **jede** Abteilung vorhanden, **inhaltlich je Rolle verschieden**. Besteht
hauptsächlich aus **Listen und Kennzahlen**.

- **Definiert im Code, je Rolle** — ein festes Kachel-Set pro Abteilung, keine
  UI-Konfigurierbarkeit. Änderung = Deployment. Bewusst **nicht** der Weg des
  benutzerdefinierten Feldkatalogs (D-100/D-111): ein Kachel-Baukasten wäre ein
  eigenes Teilsystem und würde den Bau erheblich verzögern.
- **Befüllt mit den Daten des Users**, über Filter — „meine offenen Servicefälle",
  „meine Termine", „meine Verkaufschancen".

> **Abgrenzung, die wichtig ist:** Die Nutzerfilterung im Cockpit ist eine
> **Anzeigeentscheidung**, keine Autorisierung. Sie ist konsistent mit **D-016**
> (`responsible_*_id` ist „rein informativ, keine AuthZ"): ein Vertriebler *sieht*
> im Cockpit seine Verkaufschancen, *darf* aber weiterhin alle sehen. Das Cockpit
> ist eine Abkürzung in den Alltag, keine Sichtbarkeitsgrenze.

Da jeder Mitarbeiter genau **eine** Rolle hat (D-124), ist die Cockpit-Auswahl
eindeutig — es gibt keinen Umschalter und keine Zusammenführung.

---

## Abhängigkeit: genau eine Rolle je Mitarbeiter (D-124)

Diese Spec setzt voraus, dass ein User **genau eine** Rolle hat. Das **revidiert**
`IDENTITY_RBAC.md`, wo Rollen als `belongsToMany` über `role_user` mit
`is_primary` geführt wurden:

| vorher (D-031) | jetzt (D-124) |
| --- | --- |
| `roles()` `belongsToMany` über `role_user` | `role()` `belongsTo` |
| `role_user.is_primary` als Anzeige-Rolle | entfällt ersatzlos |
| mehrere Rollen möglich | `users.role_id`, **NOT NULL** |

---

## Offene Punkte

| # | Punkt | Wohin |
| --- | --- | --- |
| 1 | **Genaue Permissions je Abteilung** — Nutzer: „müssen später nochmal definiert werden". Bestimmt zugleich die endgültige Blockzuordnung oben | eigene Runde (D-125) |
| 2 | Einordnung von **Umbuchung**, **Lieferanten**, **Reservierungen** in den Warenwirtschaftsblock — Nutzervorgabe nennt nur fünf Einträge | mit #1 (D-114/D-103/D-106) |
| 3 | Abgrenzung **Geschäftsführung ↔ Management**: GF hat `['*']`, Management ist operative Leitung ohne Systemadministration und ohne sensible Auswertungen — was genau „sensibel" ist, fehlt | mit #1 (D-125) |
| 4 | Inhalt der **Einstellungen** (⚙) | vom Nutzer bewusst offen gelassen |
| 5 | Konkrete **Kachel-Sets je Cockpit** (welche Listen, welche Kennzahlen) | bei Umsetzung je Rolle (D-126) |
| 6 | **Bildreferenz `nav-referenz.png`** ablegen | Nutzer |
| 7 | SSO-Folge: Entra-`roles`-Claim-Sync muss auf **ein** Rollen-Mapping umgestellt werden; mehrere App-Rollen je Nutzer werden Fehlerfall | vor SSO-Slice (D-029/D-124) |
