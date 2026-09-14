# Storage

## Prinzip

Strukturierte Fachinformationen gehören in PostgreSQL.

Große Binärdaten gehören perspektivisch in S3-kompatiblen Object Storage.

## Der Bucket ist vollständig öffentlich (ADR-028)

**MinIO** auf demselben Host, **ein** Bucket, **100 % öffentlich** — keine ACLs je
Objekt, keine signierten URLs. **Cloudflare davor als Cache**, damit der Origin nur
noch Cache-Misses ausliefert.

**Nur das ERP schreibt**, Website und Shop lesen. Die fachliche Zuordnung lebt in
Postgres via `core` — das ist dieselbe Integrationsform wie bei der
gemeinsamen Datenbank und **kein** Verstoß gegen ADR-011.

## Beispiele

**Object Storage — ausschließlich öffentliche Assets:**

- Produktbilder (Website, Shop, ERP-Produktverwaltung)
- Hersteller-Prospekte

**PostgreSQL:**

- Metadaten
- Zuordnung
- Version
- Hash
- Erstellungszeitpunkt
- fachlicher Kontext
- Zugriffsregeln
- **Unterschriften** (`bytea`) — klein (5–30 KB), atomar am Bericht, vom
  Datenbank-Backup erfasst, über die bestehende Autorisierung geschützt (ADR-028)

## Was **nicht** in den Bucket gehört

Weil der Bucket vollständig öffentlich ist, ist jede Datei darin für jeden
erreichbar, der die URL kennt. Eine Zugriffsprüfung lässt sich später **nicht**
nachrüsten.

- **Unterschriften** → Datenbank (s. o.).
- **Einsatzfotos** (Wartung/Servicefall) → **gemountetes Volume im ERP-Projekt**
  (ADR-029), `erp.dormed.de` unter `storage/app/private/...`, ausgeliefert über eine Route
  mit Policy-Prüfung. Sie sind eine andere Größenklasse als Unterschriften: 1–5 MB je
  Stück, mehrere je Bericht — als `bytea` würden sie Datenbank und Backup aufblähen.

  **Getrennt vom Object Storage, weil die Lebensdauern verschieden sind:** der Bucket
  trägt dauerhafte Marketing-Assets, die Fotos sind zeitlich begrenzte Nachweise und
  bekommen später eine Aufräumregel.

  > **Nicht `storage/app/public`** — das wird per `storage:link` nach `public/`
  > symlinkt und wäre ohne jede Prüfung aus dem Netz erreichbar.
  >
  > **Das Volume ist nicht im Backup.** Coolify sichert nur Datenbank-Ressourcen
  > (ADR-026) — ein eigener Sicherungsweg fehlt noch (ADR-029, offen).

## Documents

Ein Dokument ist ein fachliches Objekt.

Eine Datei ist eine Repräsentation/Version eines Dokuments.

Nicht jede PDF-Datei ist automatisch die Source of Truth.

**Konsequenz daraus (ADR-006 / D-142):** PDFs werden **just-in-time aus den
strukturierten Daten erzeugt** und z. B. direkt in eine Mail gepackt — sie werden
**nicht** roh als Datei abgelegt. Deshalb trägt der Object Storage auch keine
generierten Dokumente. Das gilt **auch für die gestellte Rechnung**.

**Und es gibt keine `documents`-Tabelle** (D-143): ein Dokument ist ein Renderziel,
kein Datensatz. Fotos und Uploads sind ein **eigenes** Modell (`Attachment`) und
ausdrücklich keine Dokumente. Rendering-Weg und Belegarten:
[`../04-domain/DOCUMENTS.md`](../04-domain/DOCUMENTS.md).
