# Storage

## Prinzip

Strukturierte Fachinformationen gehören in PostgreSQL.

Große Binärdaten gehören perspektivisch in S3-kompatiblen Object Storage.

## Kandidat

MinIO für self-hosted Infrastruktur.

## Beispiele

Object Storage:

- Fotos
- große Anhänge
- Dokumentenrepräsentationen
- generierte Dateien

PostgreSQL:

- Metadaten
- Zuordnung
- Version
- Hash
- Erstellungszeitpunkt
- fachlicher Kontext
- Zugriffsregeln

## Documents

Ein Dokument ist ein fachliches Objekt.

Eine Datei ist eine Repräsentation/Version eines Dokuments.

Nicht jede PDF-Datei ist automatisch die Source of Truth.
