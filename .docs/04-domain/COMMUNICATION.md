# Domäne — Communication (Kontaktanfragen)

Autoritative deklarative Spec. Entscheidungen **D-132 – D-133**
([`../07-decisions/grill-log.md`](../07-decisions/grill-log.md)), offener
Folgeteil **D-122**.

Modul: `app/Modules/Communication/` (Namespace
`App\Modules\Communication\`, `depends_on: [Core]`, ADR-033).

> **Teilspezifiziert.** Diese Runde deckt **nur** den Kontaktformular-Eingang ab.
> Die Sales-Aktivitäten-Timeline (Anrufe/Mails/Notizen, `SALES.md` #7) ist noch
> nicht gegrillt.

## Ist-Referenz

Anders als bei Inventory (D-108) gibt es hier eine **echte, lebende Quelle**: das
produktive Website-Projekt liegt unter `dormed.de/` im Repo-Root.

> **Nur lesen, nicht anfassen.** Das Projekt ist nicht Teil dieses Repos und wird
> später eigenständig hereinmigriert (`dormed.de`). Nichts darin wird für die
> spätere Projektstruktur verändert.

| Datei | Was daraus stammt |
| --- | --- |
| `app/Http/Requests/ContactFormRequest.php` | Feldliste + Validierungsregeln |
| `app/Http/Controllers/ContactFormController.php` | Ablauf nach dem Absenden |
| `app/Jobs/CreateInquiryInCas.php` | Zielfelder im Altsystem (`Inquiries`) |
| `resources/views/kontakt.blade.php` | Auswahlliste Fachgebiet, Pflichtfelder im Markup |

---

## Grundsatz — die Anfrage ist ein eigener Datensatz

Ein Formulareingang wird als **eigenständige `ContactRequest`** gespeichert. Dabei
entsteht **keine** `Person` und **keine** `Company`.

**Das ist die Auflösung eines sonst harten Konflikts:** Nach **D-002** existiert
eine `Person` nie eigenständig, sondern immer über `CompanyContact` an mindestens
einer `Company`. Ein Kontaktformular liefert aber zuerst eine Person — und
`praxis` ist im realen Formular **optional**, eine Anfrage ohne Firmenangabe also
der Normalfall. Die Anfrage als Zwischenstufe löst das, **ohne** dass D-002 eine
Ausnahme braucht.

**Keine API-Anbindung.** Das Altsystem schickt die Anfrage per `CreateInquiryInCas`
an ein CAS-`Inquiries`-Objekt. Diese Brücke ist im Zielsystem **hinfällig** — der
Monolith ist selbst das CRM. Übernommen wird die **Feldlogik**, nicht der
Übertragungsweg.

Dass das Altsystem bereits ein eigenständiges `Inquiries`-Objekt kennt, bestätigt
den Zuschnitt: die Entität ist kein neues Konstrukt.

---

## Zwei Stufen (D-122 / D-132)

| | Umfang | Status |
| --- | --- | --- |
| **Stufe 1** | Anfrage speichern, **manuell** verknüpfen | **entschieden** (D-132) |
| **Stufe 2** | Dublettenabgleich, automatische Verknüpfung, Auto-Anlage eines Kundenstamms | **vertagt** (D-122) |

Nutzer: „Sollte ja easy extendable sein, die Logik eines abgesendeten
Kontaktformulars abzulegen und dann später das Verlinken dranzuhängen. Fürs erste
gibt es dann nur eine Anfrage, die dann verknüpft werden muss."

**„Easy extendable" heißt hier nicht schemalos.** Jedes Formularfeld bekommt eine
eigene, typisierte Spalte; kommt ein Feld hinzu, ist das eine Migration und eine
eigene `D-NNN`. Ein JSON-Auffangfeld wäre ein Widerspruch zu **D-093**
(höchstmögliche Normalisierung) und **D-094** (typisierte Spalte + CHECK, Änderung
als bewusster Reibungspunkt) — und anders als beim Artikel-Feldkatalog (D-100)
definiert hier niemand im UI Felder: das Formular ändert ohnehin nur ein Entwickler.

Erweiterbar ist die Struktur durch den **Zuschnitt**, nicht durch ein Schlupfloch:
die Anfrage steht als eigener Datensatz da und bekommt in Stufe 2 lediglich die
Verknüpfungslogik davorgeschaltet.

---

## `ContactRequest`

| Feld | Typ | Null | Notiz |
| --- | --- | :-: | --- |
| `name` | string(128) | – | ← `name` (required) |
| `email` | string(64) | – | ← `email` (required, validiert) |
| `phone` | string(32) | ✓ | ← `telefon` |
| `postal_code` | string(5) | – | ← `plz` — **Pflicht, genau 5 Ziffern** |
| `company_name` | string(64) | ✓ | ← `praxis` — **optional**, reiner Text, **keine** FK |
| `message` | text | ✓ | ← `nachricht` (max 5000) |
| `medical_specialty_id` | FK → `medical_specialties` | ✓ | ← `fachgebiet` (D-133) |
| `callback_requested` | boolean | – | ← `rueckruf` |
| `callback_date` | date | ✓ | ← `rueckruf_datum` |
| `status` | enum `neu` \| `verknuepft` \| `verworfen` | – | D-094: VARCHAR + CHECK |
| `company_id` | FK → `companies` | ✓ | gesetzt beim Verknüpfen |
| `company_contact_id` | FK → `company_contacts` | ✓ | gesetzt beim Verknüpfen |
| `assigned_user_id` | FK → `users` | ✓ | **automatisch aus `sales_territories`** per `postal_code` (D-084) |
| `consent_text_version` | string | – | Stand der Datenschutzerklärung bei Absenden |
| `consent_given_at` | datetime | – | ← `datenschutz` (required) |
| `submitted_at` | datetime | – | |

`SoftDeletes` (D-018), `TracksBlame`.

**Beziehungen:** `company()` / `companyContact()` `belongsTo` (beide nullable),
`medicalSpecialty()` `belongsTo`, `assignedUser()` `belongsTo` `User`.

### Zwei Ableitungen aus den realen Feldern

**1. `plz` ist Pflicht — Zuständigkeit ohne Matching.**
Weil die Postleitzahl garantiert vorliegt, kann `assigned_user_id` schon in
Stufe 1 über `sales_territories` (D-084) vorbelegt werden. Die Anfrage landet beim
richtigen Vertriebler, auch wenn sie noch keinem Kunden zugeordnet ist.

> Das ist **keine** vorgezogene Stufe-2-Automatik: es wird eine **Zuständigkeit**
> gesetzt, **kein Datensatz verknüpft**. Analog zu D-016/D-084 ist die Zuordnung
> ein Vorschlag, manuell überschreibbar, ohne Autorisierungswirkung.

**2. `datenschutz` ist die DSGVO-Einwilligung.**
Sie wird an der Anfrage festgehalten (Zeitpunkt **und** Textversion) und beim
späteren Anlegen eines Kundenstamms in einen `Consent` (D-013, historisiert,
kanalweise) überführt. Ohne das entstünde ein Datensatz ohne Rechtsgrundlage für
die Kontaktaufnahme.

---

## Workflow

```text
Formular abgesendet
   └─> ContactRequest  status = neu
         │             assigned_user_id aus PLZ vorbelegt (D-084)
         │             consent_given_at gesetzt
         │
         ├─ Bearbeiter verknüpft ──> status = verknuepft
         │     company_id / company_contact_id gesetzt
         │     Anfrage BLEIBT bestehen, hängt an der Company
         │
         └─ Spam / Irrläufer ──────> status = verworfen  (Endzustand)
```

**Nach dem Verknüpfen bleibt die Anfrage stehen** (Nutzerentscheidung). Sie ist
der Ursprungsbeleg. Was daraus wird — Verkaufschance, Servicefall, nichts —
entscheidet der Bearbeiter **separat**; es wird **nichts automatisch erzeugt**,
konsistent mit **D-053** („gewonnen erzeugt nichts automatisch").

`Opportunity.lead_source = website_anfrage` (D-086) existiert bereits und ist der
vorgesehene Wert, wenn der Bearbeiter eine Verkaufschance anlegt.

**Zuständig:** `backoffice` und `management` (D-125).

### „Keine verwaisten Anfragen" — ohne Automatik

Die Kernanforderung aus D-122 bleibt bestehen. In Stufe 1 wird sie nicht durch
Automatik erreicht, sondern durch **Unübersehbarkeit** — **beides** (Nutzerwahl):

1. **Nav-Eintrag „Anfragen" mit Zähler** der unverknüpften Anfragen
   ([`../09-ui/NAVIGATION.md`](../09-ui/NAVIGATION.md), D-123). Dauerhaft sichtbar,
   auch wenn niemand ins Cockpit schaut — der Zähler geht erst weg, wenn
   abgearbeitet wurde.
2. **Cockpit-Kachel** im Cockpit von `backoffice` und `management` (D-126).

---

## Fachgebiete — eine Liste (D-133)

Die Auswahlliste im Formular und der `MedicalSpecialty`-Seed aus D-019 waren zwei
unterschiedliche Listen (8 gegen 21 Werte, abweichende Benennung,
„Orthopädie / Sportmedizin" im CRM getrennt).

**Es gibt künftig nur eine:** `MedicalSpecialty` ist die Wahrheit, **das Formular
zieht seine Auswahl aus der Tabelle** statt aus einem hartcodierten Array. Der
D-019-Seed wird um die fehlenden Werte erweitert — siehe
[`CORE.md`](CORE.md#medicalspecialty-lookup).

`MedicalSpecialty` ist laut `CORE.md` ohnehin eine **vom Nutzer pflegbare** Liste;
die Erweiterung ist der vorgesehene Weg, kein Bruch.

---

## Offene Punkte

| # | Punkt | Wohin |
| --- | --- | --- |
| 1 | **Stufe 2** — Matching-Kriterien, Mehrfachtrefferverhalten, automatische Anlage eines Kundenstamms. Gematcht wird auf den typisierten Spalten (`email`, `phone`, `postal_code`, `name`, `company_name`) | vertagt bis das System läuft (D-122) |
| 2 | **Spam-Abwehr** — in Stufe 1 entschärft (eine Spam-Anfrage erzeugt keinen Kundenstamm mehr, nur eine Zeile auf `verworfen`), vor Stufe 2 aber zwingend | mit #1 (D-122) |
| 3 | **Benennung der Fachgebiete** gemischt: Altsystem nennt Personen im Plural („Orthopäden"), Website-Werte nennen Fachgebiete („Gynäkologie"). Vereinheitlichung berührt Bestandsdaten | eigene Entscheidung (D-133) |
| 4 | **Sales-Aktivitäten-Timeline** (Anrufe/Mails/Notizen) | noch nicht gegrillt (`SALES.md` #7) |
| 5 | Bestätigungs-/Benachrichtigungsmails (`SendInquiryMails` schickt heute je eine an Kunde und Firma) | mit dem Plattform-Benachrichtigungssystem, `SCHEDULING.md` #4 |
| 6 | `callback_requested` / `callback_date` — wird daraus ein `Appointment` (D-046) oder eine Aufgabe? | offen |
