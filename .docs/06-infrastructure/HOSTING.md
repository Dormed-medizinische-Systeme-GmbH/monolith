# Hosting, Kapazität und Kosten

> Grundlage der Monolith-Entscheidung (ADR-033) und der Absage an Octane (ADR-034).
> Die Zahlen stammen vom Nutzer (2026-09-14); die Rechnungen sind Plausibilitäts-
> schätzungen, **keine Messwerte** — siehe §7.

## 1. Bemessungsgrundlage

| Größe | Wert |
| --- | --- |
| Mitarbeiter im ERP | **20** |
| Kunden, die das Portal nutzen könnten | **1.600**, Tendenz steigend |
| Shop-Kunden | anonym + eingeloggt, B2B-Medizintechnik |
| Adressstamm (Legacy) | **50.000** Datensätze — Firmen **und** Ansprechpartner zusammen |
| Termine, Tickets | jeweils ähnliche Größenordnung |

Im Zielschema wird der Adressstamm auf `companies` + `people` + `addresses` +
`contact_channels` aufgeteilt — grob 200.000 Zeilen. **Gesamt über alle Tabellen unter
1 Mio Zeilen, 1–3 GB mit Indizes.**

Das ist eine kleine Datenbank. Bei 32 GB RAM liegt sie vollständig im Postgres-Cache —
der größte Performance-Hebel überhaupt, und er kostet nichts.

## 2. Lastrechnung

„Gleichzeitige Nutzer" ist die falsche Einheit. Die richtige ist **Requests/Sekunde**,
der Umrechnungsfaktor ist die Denkzeit. Ein ERP-Mitarbeiter löst beim aktiven Arbeiten
alle 8–20 Sekunden einen Request aus → 0,05–0,125 req/s pro Kopf.

```text
20 Mitarbeiter × 0,125 req/s                        =   2,5 req/s
1600 Portalkunden, 5 % gleichzeitig (pessimistisch)  =   4   req/s
Shop (B2B, ueberwiegend Cache-Hits)                  =   5   req/s
                                                       ------
realistische Spitze                                    ~10–20 req/s
pessimistisch mit 10-fachem Wachstum                   ~100–200 req/s
```

Die 5 % beim Portal sind bewusst absurd hoch gegriffen — ein Kundenportal für
Medizintechnik wird ein paar Mal im Jahr pro Kunde benutzt, nicht täglich.

## 3. Kapazität

Ohne Octane, klassisch php-fpm mit OPcache (ADR-034):

```text
Laravel-Bootstrap ~20 ms + Seite ~50 ms   =  ~70 ms/Request
6 Kerne × (1000 / 70)                     = ~85 req/s bei Volllast
mit 50 % Reserve fuer Spitzen             = ~40 req/s komfortabel
```

**Faktor 15–30 Reserve auf die realistische Spitze.** Bei 2,5 req/s Mitarbeiterlast
läuft die Maschine im einstelligen Prozentbereich.

Daraus folgt ADR-034: Octane spart ~20 ms Bootstrap auf einer Maschine, die zu 97 % idle
ist. Der Gewinn ist nicht messbar, die State-Leak-Bugklasse und die feste Worker-Zahl
sind es.

## 4. Was heute 700 €/Monat kostet — und was davon stirbt

Die 700 € sind **reine Server- und SQL-Kosten**. CAS-Lizenzen und Wartungsvertrag kommen
obendrauf. Der Server ist auf drei VMs aufgeteilt, und alle drei sind ein Artefakt der
Thick-Client-Architektur:

| VM | Warum sie existiert | Nach der Ablösung |
| --- | --- | --- |
| SQL Server | CAS braucht MSSQL | → PostgreSQL, Lizenz entfällt |
| RDP-Host | CAS genesisWorld ist eine Windows-Desktop-Anwendung | → Browser, entfällt |
| Domain Controller | AD-Anmeldung der RDP-Sitzungen | **bleibt vorerst** (siehe unten) |

Sage/KHK ist mit D-068 bereits abgelöst, echte Buchhaltung bleibt extern beim
Steuerberater (nur DATEV-Export, `../04-domain/BILLING.md`). Nach dem Rollout läuft auf
dem RDP-Host **nichts Fachliches mehr**.

**Domain Controller:** wird für den Anfang weiter gebraucht (Nutzer). Die Entscheidung
fällt „im großen Stil" später — sie betrifft Windows-Arbeitsplätze, Dateifreigaben,
Drucker und GPO und ist damit ein **IT-Infrastrukturthema, kein ERP-Kostenblock**. Er
gehört nicht in das Budget unten und darf auf die kleinstmögliche VM.

**Parallelbetrieb einplanen.** Während Datenmigration und Einarbeitung läuft CAS weiter.
3–6 Monate Doppelkosten sind real und gehören ins Budget.

## 5. Zielbudget

Zielvorgabe des Nutzers: **200 €/Monat**. Das ist mit Faktor 2 erreichbar — aber nicht,
indem man eine große Kiste kauft:

```text
App-Host        4 dedicated vCPU, 16 GB           ~35 €
PostgreSQL      eigene Coolify-Ressource, PITR    ~30 €
Staging                                           ~10 €
Backup offsite  Storage Box                       ~10 €
Monitoring                                        ~0–20 €
                                                  -------
                                                  ~85–105 €
```

Größenordnungen (Hetzner, Stand Frühjahr 2026 — Tagespreise prüfen): 8 dedicated vCPU
mit 32 GB liegen bei ~65 €; ein dedizierter Ryzen 7 mit 8 echten Kernen und 64 GB bei
~70–90 €.

> **Die Differenz gehört nicht in mehr Kerne.** Sie gehört in ein echtes Staging (es wird
> ein System abgelöst, an dem der Betrieb hängt), in Point-in-Time-Recovery statt
> nächtlicher Dumps, und in die Parallelphase. Bei 700 € ruft jemand anderes um 3 Uhr
> nachts zurück; bei 70 € ist man das selbst — dafür ist der Rest des Budgets da.

## 6. Was tatsächlich wächst

Nicht die Datenbank — die **Einsatzfotos** (ADR-029):

```text
20 Einsaetze/Tag × 5 Fotos × 3 MB ≈ 300 MB/Tag ≈ 100 GB/Jahr
```

Speicherplatz ist billig (~3–4 € pro 100 GB/Monat). Die Aufmerksamkeit gehört woanders
hin: ADR-029 notiert selbst, dass die Fotos auf einem gemounteten Volume **der einzige
Teil der Anwendung ohne Backup** wären.

> **Bewusst getragene Folge (Nutzer, 2026-09-14):** Die Fotos bleiben vorerst auf
> `storage/`. Damit ist der App-Container zustandsbehaftet und **nicht frei auf N
> Replicas skalierbar**, ohne das Volume zu teilen. Bei der Last aus §2 wird nie eine
> zweite Replica gebraucht — der horizontale Hebel aus ADR-033 ist also vorhanden, aber
> nicht kostenlos abrufbar. Der Umzug in einen privaten Bucket ist später separat zu
> machen.

## 7. Was die Obergrenze wirklich setzt — nie die CPU

In dieser Reihenfolge:

1. **Postgres-Queries.** Bei 50.000 Zeilen ist ein fehlender Index ein Sequential Scan
   über wenige Megabyte. Das kostet **Latenz** (400 ms statt 80 ms), ist bei 20 Nutzern
   aber **kein Kapazitätsproblem**. Sauber indizieren bleibt richtig — es ist hier
   Komfort, nicht Überleben.
2. **Lang laufende Requests.** Der wahrscheinlichste Weg gegen die Wand. Konkret:
   PDF-Erzeugung für Wartungs- und Messprotokolle (D-038/D-044), Rechnungsläufe,
   DATEV-Export, DHL-/PayPal-Aufrufe. **Regel ohne Ausnahme: alles, was nicht
   zuverlässig unter ~200 ms bleibt, geht in die Queue.** Das ist eine Designbedingung,
   keine Optimierung.
3. **Schreiblast auf Postgres.** Das ist die einzige Grenze, die aus der SOT-Anforderung
   folgt und nicht aus dem Monolithen — jede Architektur mit einer Wahrheit hat sie.
   Auf NVMe liegt sie im vierstelligen Bereich pro Sekunde. Weit weg.

**Der Monolith hebt keine Obergrenze an oder ab.** Eine Aufteilung in vier Dienste spart
keine CPU-Zyklen, sie kostet welche (vier Bootstraps, Serialisierung, HTTP-Hops). Und
skaliert wird bei Bedarf horizontal: N Replicas desselben Images.

## 8. Offen — vor dem Produktivgang messen

Die Zahlen oben sind geschätzt. Sobald die drei schwersten Listenansichten stehen:
eine Datenbank in realistischer Größe seeden (200k Companies, 1M Tickets, 50k Verträge)
und mit k6 oder vegeta dagegen fahren. Das ersetzt die Schätzung durch einen Messwert.

**Früh machen** — ein Schema, das sich nicht indizieren lässt, ist jetzt billig zu ändern
und in zwei Jahren teuer.
