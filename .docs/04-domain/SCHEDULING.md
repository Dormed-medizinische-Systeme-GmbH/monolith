# Domäne — Scheduling (Termine)

Autoritative deklarative Spec. Entscheidungen **D-046 – D-050**
([`../07-decisions/grill-log.md`](../07-decisions/grill-log.md)). Feld-Herkunft:
[`../00-legacy/mapping/Termine-Felder.md`](../00-legacy/mapping/Termine-Felder.md) (25 F.).

Modul: `packages/core/src/Modules/Scheduling/` (Namespace `Dormed\Core\Modules\Scheduling\`, ADR-013).

## Grundsatz

Ein `Appointment` ist ein **Kalendereintrag**, getrennt vom fachlichen Vorgang
(LEGACY_MAPPING). Wartung/Servicefall haben ihren eigenen Workflow (D-039); der
Termin sagt nur *wann* jemand *wo* ist. Ein Vorgang kann **mehrere** Termine
haben (Diagnose-Besuch, Reparatur-Besuch).

## Appointment

| Feld | Typ | Null | Notiz |
| --- | --- | :-: | --- |
| `schedulable_type` / `schedulable_id` | morph | ✓ | `Maintenance` · `ServiceCase` · `Opportunity` · `null` (freier Termin) |
| `title` | string | – | |
| `type` | enum `kundenbesuch` \| `interne_besprechung` \| `sonstiges` | – | D-088 — nur für **freie** Termine relevant (`schedulable = null`); bei gesetztem `schedulable` durch den Vorgang bestimmt |
| `status` | enum `vorlaeufig` \| `fixiert` \| `storniert` | – | D-090. Kein eigener „durchgeführt"-Zustand — trägt der fachliche Vorgang |
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
| 1 | ~~`type`- und `status`-Enum-Werte~~ | ✅ gelöst (D-088/D-090) |
| 2 | Serien-Umsetzung (Master/Occurrences vs. Expansion) | Slice-Detail |
| 3 | Outlook/Graph-Zwei-Wege-Sync | ROADMAP-Slice (mit SSO, D-049) |
| 4 | Volles Reminder-/Benachrichtigungssystem | Plattform, später |
| 5 | Konflikt-/Doppelbuchungs-Prüfung je Techniker | Slice-Detail |

## Bezug Service

Löst die Service-Restfrage „Terminplanung für Maintenance/ServiceCase": beide
werden über polymorphe `Appointment`s geplant (`schedulable`), nicht über eigene
Terminfelder.
