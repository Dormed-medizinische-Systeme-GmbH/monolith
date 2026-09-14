# Domäne — Documents (Belegerzeugung)

Autoritative deklarative Spec. Entscheidungen **D-141 – D-145**
([`../07-decisions/grill-log.md`](../07-decisions/grill-log.md)).
Speicher-Prinzipien: [`../06-infrastructure/STORAGE.md`](../06-infrastructure/STORAGE.md).

Modul: `app/Modules/Documents/` (Namespace `App\Modules\Documents\`,
ADR-033).

---

## Der überraschendste Teil zuerst: es gibt keine `documents`-Tabelle

Zwei Entscheidungen zusammengenommen führen dorthin:

- **D-143:** Ein „Dokument" ist ausschließlich ein **erzeugter Beleg** — Fotos und
  eingehende Dateien sind keine Dokumente.
- **D-142:** Erzeugte Belege werden **nicht gespeichert**, sondern bei Bedarf aus den
  strukturierten Daten gerendert (ADR-006).

**Ein Dokument ist damit ein Renderziel, kein Datensatz.** Der Bereich besteht aus
genau drei Dingen:

```text
1. Belegarten-Katalog   (im Code, D-144)
2. Renderer             Daten + Template ──> HTML ──> PDF
3. Attachment           Fotos/Uploads — eigenes Modell, NICHT „Dokument"
```

---

## Rendering-Pipeline (D-141)

```text
Daten aus core ─────────┐
   (bei Belegen mit     ├──> Renderer ──> HTML ──> PDF
    eingefrorenem       │                       └─> Mailanhang
    Zustand: genau die) │
Template ───────────────┘
   jetzt:   Blade-Datei im Repo
   später:  Definition aus dem Designer (versioniert in der DB)
```

**Jetzt: Blade.** Jede Belegart ist ein Blade-Template, gerendert nach HTML und von
dort nach PDF. Layoutänderung = Deployment. Bei rund einem Dutzend Belegarten mit
seltenen Layoutänderungen ist das angemessen — und für den MVP (ADR-032) der
schnellste Weg.

**Später: AnkaReports.** Ein visueller Designer bleibt als Ausbaustufe vorgemerkt —
[AnkaReports](https://github.com/ankareport/ankareport), **nicht**
[NextReports](https://github.com/nextreport/engine).

> **Warum nicht NextReports:** Es ist eine **Java**-Engine und bräuchte eine JVM neben
> PHP auf demselben Host — zweiter Technologie-Stack, zusätzlicher Speicher, eigene
> Betriebssorge, für denselben Zweck. AnkaReports ist JavaScript und einbettbar.

**Die Pipeline wird deshalb so geschnitten, dass die Template-Quelle austauschbar
ist.** Renderer und Datenbeschaffung bleiben gleich; nur woher das Template kommt,
ändert sich. Der spätere Wechsel ist additiv, kein Umbau.

---

## Belegarten (D-144)

| Beleg | Quelle | Entscheidung |
| --- | --- | --- |
| Angebot | `Opportunity` + Positionen | D-052, `SALES.md` #6 |
| Rechnung | `Invoice` + Positionen | D-057/D-067 |
| Gutschrift / Storno | `Invoice` mit `type = gutschrift` | D-069 |
| e-Rechnung (XRechnung) | dieselben Daten, **XML statt PDF** | D-074 |
| Mahnung | Mahnlauf | `BILLING.md` |
| Wartungsbericht | `MaintenanceReport` + Prüfpunkte | D-044/D-045 |
| Messprotokoll | `MeasurementProtocol` | D-038 |
| Servicebericht | `ServiceCase` | `SERVICE.md` |
| Abholbeleg | `PickupNote` | D-130 |
| Reservierungsschein | `Reservation` | D-106 |
| Rückgabebeleg | `ReservationReturn` | D-106 |
| Umbuchungsbeleg | `StockTransfer` | D-114 |
| Bestellung | `PurchaseOrder` | D-128 |

**Nicht enthalten:** der **Zählauftrag** (D-113) — eine Arbeitsliste in der
Oberfläche, kein Beleg zum Versenden. Wird er später doch gedruckt, kommt er als
eigene D-NNN dazu.

**Briefanrede** wird bei der Erzeugung **generiert**, nicht gespeichert (D-015).

---

## GoBD: keine Archivkopie, kein Template-Pin (D-142)

Auch die **gestellte Rechnung** wird bei Bedarf neu gerendert. Es wird **kein** PDF
archiviert und **keine** Template-Version am Beleg gepinnt.

Die Frage wurde ausdrücklich gestellt — ändert sich das Template, sieht eine neu
gerenderte Rechnung anders aus als die, die der Kunde erhalten hat — und bewusst so
entschieden. Was die Entscheidung trägt:

- Die **GoBD verlangt Unveränderbarkeit der gebuchten Daten**, kein eingefrorenes
  Layout. Umsatzsteuerlich zählen die Pflichtangaben nach **§14 UStG** — Inhalt, nicht
  Schriftart.
- **Die Daten sind eingefroren** (D-093: Summen, Empfänger-Snapshot, Unveränderlichkeit
  ab `gestellt`). Der Inhalt ist also reproduzierbar, unabhängig vom Layout.
- **Bei der e-Rechnung ist die Datenstruktur ohnehin der Beleg.** XRechnung ist XML aus
  denselben Daten — dort ist „die Daten sind die Rechnung" keine Auslegung.
- In einer Betriebsprüfung stören abweichende Beträge oder fehlende Pflichtangaben,
  nicht ein verschobenes Logo.

> **Falls es später anders bewertet wird**, ist die Nachrüstung klein: entweder eine
> Template-Versionsspalte am Beleg (analog `checklist_template_version`, D-038) oder
> eine Archivkopie beim Übergang auf `gestellt`. **ADR-006** sieht den Fall in seiner
> eigenen Ausnahmeklausel bereits vor („sofern kein rechtlicher Grund für die Datei
> als Primärrepräsentation besteht").

---

## `Attachment` — Fotos und Uploads (D-143)

**Fotos sind keine Dokumente.** Die bisherige Formulierung in `SERVICE.md`
(„Fotos/Nachweise → `documents`, polymorph am Report") war falsch und ist korrigiert.

| Feld | Typ | Null | Notiz |
| --- | --- | :-: | --- |
| `attachable_type` / `attachable_id` | morph | – | `MaintenanceReport`, `ServiceCase`, … |
| `path` | string | – | relativ zum Volume, `storage/app/private/...` (ADR-029) |
| `original_name` | string | – | |
| `mime_type` | string | – | |
| `size_bytes` | integer | – | |
| `caption` | string | ✓ | die Aufnahmeanleitung, unter der das Foto entstand (ADR-032) |
| `captured_at` | datetime | ✓ | |

`SoftDeletes` (D-018), `TracksBlame`.

**Ablage:** gemountetes Volume im ERP-Projekt unter `storage/app/private/...`,
ausgeliefert über eine Policy-geprüfte Route — **nie** direkt, und **nie** im
öffentlichen Object-Storage-Bucket (ADR-028/ADR-029).

**Später erweiterbar:** Kundenseitige Uploads (Portal, `ContactRequest`) können
dasselbe Modell nutzen — dann mit eigener Zugriffsprüfung, weil sie nicht öffentlich
sind.

---

## Abgrenzung zum Object Storage

| Was | Wohin | Warum |
| --- | --- | --- |
| Erzeugte Belege | **nirgends** — JIT gerendert | ADR-006, D-142 |
| Einsatzfotos (`Attachment`) | gemountetes Volume im ERP | ADR-029 — brauchen später eine Aufräumregel, getrennt vom S3 |
| Unterschriften | **Datenbank** (`bytea`) | ADR-028 — klein, atomar am Bericht, vom DB-Backup erfasst |
| Produktbilder, Prospekte | **öffentlicher** MinIO-Bucket | ADR-028 — dauerhafte Marketing-Assets, Cloudflare davor |

---

## Offene Punkte

| # | Punkt | Wohin |
| --- | --- | --- |
| 1 | **Aufräumregel für Einsatzfotos** — Auslöser und Frist. Bis dahin wird nichts automatisch gelöscht, das Volume wächst ungebremst | vertagt (D-145/ADR-029) |
| 2 | **Sicherung des Foto-Volumes** — Coolify sichert nur Datenbank-Ressourcen | offen (ADR-029) |
| 3 | Konkrete **PDF-Engine** (Browsershot/Chromium, dompdf o. a.) — Auswahl bei der Umsetzung; Kriterium ist CSS-Treue bei mehrseitigen Belegen mit Kopf-/Fußzeile | bei Umsetzung |
| 4 | **Zustellung** — welche Belege gehen automatisch per Mail raus, welche nur auf Knopfdruck | mit dem Plattform-Benachrichtigungssystem (`SCHEDULING.md` #4) |
| 5 | **Qualifizierte e-Signatur** für Berichte | späterer Slice (`SERVICE.md` #8) |
