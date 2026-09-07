# Docker

## Lokaler Zielzustand

Minimal:

```text
compose.yaml
├── app
└── postgres
```

Optional später:

```text
├── redis
├── reverb
├── minio
└── mailpit
```

Nur hinzufügen, wenn der jeweilige Entwicklungsbedarf besteht.

## App Container

Der App-Container enthält die Laravel-Laufzeit.

Abhängig vom gewählten Laravel-Stack können PHP-FPM und Webserver getrennt werden. Für das erste lokale Fundament ist jedoch eine möglichst einfache, reproduzierbare Struktur vorzuziehen.

## PostgreSQL

PostgreSQL läuft als eigener Container mit persistentem Volume.

Die Datenbank darf bei `docker compose down` nicht unbeabsichtigt gelöscht werden.

## Production

Production ist infrastrukturell unabhängig vom lokalen Compose-Setup.

Das Compose-Setup ist keine Aussage darüber, wie Production zwingend betrieben werden muss.

## Migration

Production erhält eine neue PostgreSQL-Instanz.

Die Anwendung wird anschließend über einen definierten Import-/Migration-Prozess mit Live-Daten gefüllt.

Es gibt keinen automatischen Transfer lokaler Demo-Daten in Production.
