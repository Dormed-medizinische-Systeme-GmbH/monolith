# Domäne — Scheduling (Termine)

Autoritative deklarative Spec. Entscheidungen **D-046 – D-050**
([`../07-decisions/grill-log.md`](../07-decisions/grill-log.md)). Feld-Herkunft:
[`../00-legacy/Termine/Termine-Felder.md`](../00-legacy/Termine/Termine-Felder.md) (25 F.).

Modul: `app/Modules/Scheduling/` (Namespace `App\Modules\Scheduling\`, ADR-033).

## Grundsatz

Ein `Appointment` ist ein **Kalendereintrag**, getrennt vom fachlichen Vorgang
(LEGACY_MAPPING). Wartung/Servicefall haben ihren eigenen Workflow (D-039); der
Termin sagt nur *wann* jemand *wo* ist. Ein Vorgang kann **mehrere** Termine
haben (Diagnose-Besuch, Reparatur-Besuch).

## Appointment

| Feld | Typ | Null | Notiz |
| --- | --- | :-: | --- |
| `schedulable_type` / `schedulable_id` | morph | ✓ | `Maintenance` · `ServiceCase` · `Opportunity` · `null` (freier Termin) |
| ~~`title`~~ | — | — | **entfällt (2026-09-15)** — siehe „Bezeichnung" unten |
| `type` | enum | – | **revidiert:** `wartung` \| `service` \| `besprechung` \| `privat`. Ersetzt D-088; Liste noch nicht abschließend |
| `status` | enum, nullable | ✓ | **revidiert:** `vorlaeufig` \| `bestaetigt` \| `storniert`. **Hängt am Typ** — `besprechung` und `privat` haben keinen. Ersetzt D-090 (`fixiert` → `bestaetigt`) |
| `starts_at` | datetime | – | ← `start_dt` |
| `ends_at` | datetime | – | ← `End_dt` (Dauer abgeleitet) |
| `all_day` | boolean | – | default `false` (← `DayAppointment`) |
| `location_text` | string | ✓ | freier Ort (← `GISDescription`); bei `Maintenance` i. d. R. `device.location` |
| `assigned_technician_id` | FK → `users` | ✓ | genau einer (D-048); im Entwurf null |
| `is_online_meeting` | boolean | – | default `false` (← `ISONLINEMEETING`) |
| `meeting_url` | string | ✓ | |
| `logistics_required` | boolean | – | default `false` — Transport/Logistik nötig (← `DORMEDLOGISTIKERFORDERLICH`) |
| `reminder_minutes_before` | integer | ✓ | einfache Erinnerung; volles Reminder-System = später |
| `notes` | text | ✓ | ← `AddComment` / `Notes` |
| — Serie (D-047) — | | | |
| `recurrence_rule` | string | ✓ | RRULE/iCal; gesetzt ⇒ Serien-Master |
| `recurrence_parent_id` | FK → `appointments` | ✓ | Occurrence/Override einer Serie |
| `recurrence_ends_at` | datetime | ✓ | ← `PeriodEnd` |
| `is_cancelled_occurrence` | boolean | – | einzelne abgesagte Instanz einer Serie |

`SoftDeletes` (D-018). Audit via `TracksBlame`.

### Bezeichnung: zusammengesetzt, nicht gespeichert (2026-09-15)

Ein Termin hat **keinen Betreff**. Was im Kalender steht, entsteht aus Typ,
Status, `offsite` und der verknüpften Firma:

```
außer Haus, Firma bekannt   „{Typ} bei {Firma}, {PLZ} {Ort}"
außer Haus, ohne Firma      „{Typ} auswärts"
im Haus                     „{Typ} im Haus"

Status vorlaeufig           „[BLOCKED] " davor
Status storniert            „[STORNO] " davor
Status bestaetigt / keiner  ohne Zusatz
```

Ein eingetippter Betreff wäre eine zweite Wahrheit neben Feldern, die dasselbe
schon sagen — und genau deshalb ist `Keyword` aus CAS unter „Verworfen"
gelandet: als *gespeichertes* Feld war es falsch, als *abgeleiteter* Wert ist es
richtig.

Die **Farbe** im Kalender folgt ebenfalls dem Typ und ist keine Eingabe. Ein
frei wählbares Farbfeld hieße, dass zwei Wartungen verschieden aussehen können.

> **Die Matrix ist noch nicht entschieden** (Nutzer, 2026-09-15). Die Regel oben
> ist der erste Durchgang; Sonderfälle je Typ kommen dazu — „Privat im Haus"
> etwa liest sich schlecht. Wenn sie steht, gehört sie auf den **Server**: sie
> wird auch für Listen, Mails und PDFs gebraucht, und zweimal gepflegt läuft sie
> auseinander. Im Kalenderentwurf (`erp/Calendar.svelte`) liegt sie vorerst im
> Browser, weil dort lokal angelegte Termine sofort eine Bezeichnung brauchen.

### Serien (D-047)

- **Nur Kalender-Serien** (interne wiederkehrende Termine). Die fachliche
  Wartungs-Wiederholung ist **kein** RRULE — sie läuft über
  `ServiceContract.maintenance_interval_months` → nächste `Maintenance` → eigener
  Einzeltermin (D-037).
- Umsetzung (Master + generierte Occurrences vs. Rule-Expansion beim Lesen) →
  Detail im Slice.

## Verworfen

`CASAway` · `APP_MANDATORY` · `ISPARTOFEVENT` · `APP_ACCEPTABLEREGISTRATIONS` ·
`APP_GROUP` · `Category` · `CBStatus` · `Keyword` · `NOTES2` · `Alarm` /
`PERIODALARMDAYS` / `PERIODALARMSET` (→ `reminder_minutes_before`).
**Kein Veranstaltungs-/Event-Management** im Zielsystem.

## Offene Punkte

| # | Punkt | Wohin |
| --- | --- | --- |
| 1 | ~~`type`- und `status`-Enum-Werte~~ — ⚠️ **2026-09-15 revidiert**, siehe Feldtabelle: Typen sind `wartung`/`service`/`besprechung`/`privat`, der Status hängt am Typ | neu offen, siehe #6 |
| 6 | **Typ-/Status-Matrix und die Bezeichnungsregel** — welche Typen es gibt, welcher Typ welchen Status kennt, und wie die Bezeichnung je Typ genau lautet | eigener Durchgang, dann als D-NNN |
| 2 | Serien-Umsetzung (Master/Occurrences vs. Expansion) | Slice-Detail |
| 3 | Outlook/Graph-Zwei-Wege-Sync | ROADMAP-Slice (mit SSO, D-049) |
| 4 | Volles Reminder-/Benachrichtigungssystem | Plattform, später |
| 5 | Konflikt-/Doppelbuchungs-Prüfung je Techniker | Slice-Detail |

## Bezug Service

Löst die Service-Restfrage „Terminplanung für Maintenance/ServiceCase": beide
werden über polymorphe `Appointment`s geplant (`schedulable`), nicht über eigene
Terminfelder.
