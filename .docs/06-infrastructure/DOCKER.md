# Docker

> ## ⚠️ Überholt durch ADR-033/034 (2026-09-14) — wird mit dem Scaffold neu geschrieben
>
> Dieses Dokument beschreibt den Vier-App-Stack (`docker/<app>/Dockerfile`, ein Container
> je App, Octane/FrankenPHP). Beides gilt nicht mehr:
>
> - **Eine** Anwendung, **ein** Dockerfile, ein App-Service (ADR-033)
> - **nginx + php-fpm**, kein Octane (ADR-034)
> - Postgres und MinIO bleiben eigene Coolify-Ressourcen — diese Begründung aus ADR-026
>   (Backups, eigener Lebenszyklus) trägt unverändert weiter
>
> Der konkrete Stack entsteht erst mit dem Laravel-Scaffold; bis dahin wäre jede
> Beschreibung hier erfunden. **Was unten steht, ist bis dahin nur als Hintergrund zu
> lesen, nicht als Anleitung.**


> **Kurswechsel 2026-09-12** (ADR-011, ADR-014, ADR-015, ADR-022–ADR-025). Vier
> App-Container statt einem, Coolify bleibt die Plattform (kein eigenständiges Docker
> Swarm), Migrations laufen als separater Schritt, App-Server ist Octane/FrankenPHP,
> plus MinIO-Service.
>
> **Nachtrag 2026-09-13 (ADR-026/ADR-027).** Zwei Änderungen: die vier Apps sind in
> Prod **eine** Coolify-Anwendung aus **einer** `docker-compose.prod.yaml`, während
> **Postgres als Coolify-eigene Datenbank-Ressource außerhalb** davon läuft. Und
> **Reverb ist vertagt** — es gibt vorerst keinen `<app>-reverb`-Service.

## Container-Layout

Ein Container pro App (Octane/FrankenPHP, ADR-022), plus Postgres, plus MinIO — alle
vier Apps nach identischem Muster, sobald aktiv gebaut (aktuell nur `erp`, ADR-020).
Dev und Prod sind seit ADR-026 unterschiedlich geschnitten:

**Dev** (`docker-compose.yaml`) — ein `docker compose up` startet alles, inklusive Datenbank:

```text
docker-compose.yaml
├── website          (Platzhalter, noch nicht aktiv gebaut)
├── shop             (Platzhalter, noch nicht aktiv gebaut)
├── crm              (Octane/FrankenPHP, aktueller Bau-Fokus)
├── portal           (Platzhalter, noch nicht aktiv gebaut)
├── migrate          (Einmal-Container, fährt core-Migrations, ADR-015)
├── pgsql            (Postgres 17)
└── minio            (S3-kompatibel, ADR-025 — vorbereitend, ohne aktuelle Nutzung)
```

**Prod** (ADR-026) — die Apps in **einer** Coolify-Anwendung, Zustandsbehaftetes
daneben als **eigene** Coolify-Ressourcen:

```text
Coolify Application (docker-compose.prod.yaml, eine .env)
├── website · shop · crm · portal      ein Deploy, alle Container auf einmal ersetzt
└── Pre-Deploy: migrate --force --isolated   (ADR-015)

Coolify Database   →  postgres         eigener Lebenszyklus, Backups aus der UI
Coolify Service    →  minio            sobald ADR-025 genutzt wird
```

Der Grund für den Schnitt steht in ADR-026: die vier Apps teilen sich Postgres und
`core` und sind bei einem Core-Update deshalb **nicht** unabhängig — vier
getrennte Deploys erzeugten ein Fenster, in dem eine App neuen und eine andere alten
Core-Code gegen dasselbe Schema fährt.

Optional lokal später: `redis`, `mailpit` — nur hinzufügen, wenn Entwicklungsbedarf
besteht.

**Orchestrierung: Coolify** (ADR-014). Coolify verwaltet die vier Container + die
TLS-Terminierung selbst — **kein** eigenständiger `docker stack deploy`/Swarm-Betrieb,
kein separat gepflegter Proxy-Container außerhalb von Coolifys eigenem Traefik.

## Netzwerk

- Ein gemeinsames Docker-Network verbindet alle vier App-Container mit `postgres`
  (lokal: Compose-Bridge-Network; Prod: was Coolify vorgibt).
- Die vier App-Container kommunizieren **nicht direkt miteinander**
  (`../01-architecture/ARCHITECTURE.md` §2, `03-domain-boundaries-rules.md` Regel 2a) —
  Integration ausschließlich über die gemeinsame Postgres-Instanz via `core`.
- TLS nur am Coolify-Proxy-Rand. Intern (App ↔ Postgres, App ↔ App-Netzwerk) unverschlüsselt
  — das ist gewollt, kein Sicherheitsversehen.

## Docker-Build pro App

Jedes `*dormed.de`-Verzeichnis hat ein eigenes Dockerfile mit Multi-Stage-Build. Wichtig für
Cache-Effizienz und den gewünschten Effekt „nur betroffene Container neubauen":

```dockerfile
# Stage 1: Core-Package-Dependencies (ändert sich seltener als App-Code)
FROM composer:2 AS core-deps
WORKDIR /core
COPY core /core
RUN composer install --no-dev --optimize-autoloader

# Stage 2: App-spezifischer Build (ändert sich häufig)
FROM composer:2 AS app-build
WORKDIR /app
COPY erp.dormed.de /app
COPY --from=core-deps /core /core
RUN composer install --no-dev --optimize-autoloader

# Stage 3: Runtime — FrankenPHP (Octane, ADR-022)
FROM dunglas/frankenphp:php8.4 AS runtime
COPY --from=app-build /app /var/www/html
COPY --from=app-build /core /var/www/core
CMD ["php", "artisan", "octane:frankenphp"]
```

Effekt: Ändert sich nur App-Code (Stage 2), bleibt der Core-Layer (Stage 1) im
Docker-Cache erhalten → schnellerer Rebuild. Ändert sich `core`, wird die
Cache-Invalidierung über `COPY core` korrekt ausgelöst und **alle vier** Apps
müssen neu gebaut werden (ADR-011/ARCHITECTURE.md §1).

## Wann wird was neu gebaut?

**Build-Granularität** — was der Docker-Cache neu baut:

| Änderung an | Neu gebaut wird |
| --- | --- |
| `shop.dormed.de/**` (Views, Controller, Routen, Frontend) | nur das `shop`-Image |
| `erp.dormed.de/**` | nur das `erp`-Image |
| `dormed.de/**`, `my.dormed.de/**` | jeweils nur das eigene Image |
| `core/**` (Models, Migrations, Domain-Services) | **alle vier Images** (Cache-Invalidierung über `COPY core`, ADR-011) |
| Coolify-Proxy-/Domain-Konfiguration | nichts, nur die Coolify-UI |

**Deploy-Granularität in Prod: es gibt keine** (ADR-026). Egal was sich geändert hat —
ein Deploy ersetzt **alle vier** App-Container in einem Vorgang. Eine Änderung nur an
`dormed.de` baut zwar nur ein Image neu, startet aber trotzdem auch `erp`, `shop`
und `portal` neu. Das ist der bewusst getragene Gegenwert für die Schema-Integrität.

Die Postgres-Ressource ist davon **nicht** betroffen und läuft durch.

## PostgreSQL

Eine Instanz, eine Datenbank, geteilt von allen vier Apps.

**Prod (ADR-026):** **Coolify-eigene Datenbank-Ressource**, nicht als Service in der
App-Compose. Coolify sichert automatisiert nur seine eigenen Datenbank-Ressourcen —
ein selbst in der Compose definierter `postgres`-Container bekommt weder geplante
Backups noch Restore aus der UI. Zusätzlicher Effekt: die Datenbank überlebt jedes
App-Deployment unberührt.

**Dev:** `pgsql`-Service in `docker-compose.yaml` mit persistentem Volume. Die Datenbank darf
bei `docker compose down` nicht unbeabsichtigt gelöscht werden.

## Reverb — vertagt (ADR-027)

**Es gibt vorerst keinen Reverb-Service.** Kein Anwendungsfall im aktuell
spezifizierten Umfang erzwingt Push (Begründung in ADR-027). Nachrüstbar als
additiver Schritt, ohne Umbau am Skelett.

Falls Realtime kommt, gilt aus ADR-024 weiter: ein **eigener** Prozess/Container pro
App — nicht im selben Container-Command wie der HTTP-Server, da ein Container genau
ein `CMD` hat und zwei Daemons sonst einen Supervisor bräuchten — und **kein** von
allen vier Apps geteilter Reverb-Prozess.

## MinIO (ADR-025)

Ein Service `minio` (Image `quay.io/minio/minio`, auf einen `RELEASE.`-Tag gepinnt),
S3-kompatibel, mit persistentem Volume. **Nicht** `minio/minio` von Docker Hub — dort
gibt es das Repository nicht mehr, der Pull scheitert mit `pull access denied`.
Aktuell **ohne konkreten Verwendungszweck** — Zugangsdaten/Bucket-Konvention werden
festgelegt, sobald ein fachlicher Bereich (Inventory: Produktbilder; Documents:
Service-/Wartungsfotos) ihn tatsächlich braucht. Laravels `s3`-Filesystem-Disk zeigt
lokal auf den `minio`-Servicenamen statt auf AWS.

## Persistente Volumes (ADR-029)

`erp.dormed.de` braucht ein **gemountetes Volume** für `storage/app/private/...`
(Einsatzfotos, ADR-029). Zwei Dinge sind dabei nicht optional:

- **In Coolify als Persistent Storage deklarieren.** Nach ADR-026 ersetzt jeder
  Deploy alle App-Container — ein nicht deklariertes Volume wäre jedes Mal weg.
- **Eigener Sicherungsweg.** Coolify sichert automatisiert nur seine
  Datenbank-Ressourcen. Dieses Volume ist damit der einzige Teil der Anwendung ohne
  Backup, solange nichts eingerichtet ist (**offen**, ADR-029).

Der Plattenplatz ist zu beobachten: Fotos sind 1–5 MB, mehrere je Bericht, und sie
sammeln sich, bis die Aufräumregel existiert (Bereich Documents).

## Migrations im Deployment (ADR-015 / ADR-026)

Migrations laufen **nicht** als Teil des Boot-Vorgangs eines der vier App-Container
(bisher: `migrate --force` im Start-CMD von `docker-compose.prod.yaml` — das entfällt).

**Prod:** `php artisan migrate --force --isolated` als **Pre-Deploy-Command der einen
Coolify-Anwendung** (ADR-026). Weil es genau eine Anwendungsressource gibt, die alle
vier Apps umfasst, stellt sich die Frage nicht, an welcher App der Schritt hängt.
`--isolated` nimmt einen Cache-Lock und macht einen versehentlichen Doppelstart
folgenlos.

**Dev:** ein eigener `migrate`-Compose-Service mit `restart: "no"`, der einmalig
läuft und exitet, **vor** dem Start der App-Services.

> **Expand/Contract für destruktive Migrationen.** Beim Rollout laufen alte Container
> kurz gegen das bereits migrierte Schema. Additive Migrationen sind unkritisch; eine
> Spalte umzubenennen oder zu löschen reißt die noch nicht ersetzten Container ab.
> Destruktive Änderungen daher zweistufig: erst hinzufügen und ausrollen, in einem
> **späteren** Deploy entfernen.

## Production

Production ist infrastrukturell unabhängig vom lokalen Compose-Setup (weiterhin gültig).
Das lokale Compose-Setup ist keine Aussage darüber, wie Production zwingend betrieben
werden muss — Coolify übernimmt dort die Orchestrierung.

## Migration (Daten)

Production erhält eine neue PostgreSQL-Instanz. Die Anwendung wird anschließend über
einen definierten Import-/Migration-Prozess mit Live-Daten gefüllt. Es gibt keinen
automatischen Transfer lokaler Demo-Daten in Production.

## Offene Punkte, die vor der Umsetzung geklärt werden sollten

- ~~Reihenfolge beim Rollout eines Core-Updates in Coolify~~ ✅ geklärt (ADR-026):
  Pre-Deploy-Migration, danach ersetzt Coolify alle App-Container in einem Vorgang.
  Kein Blue-Green pro App — das würde die Schema-Integrität gerade aufgeben.
- Healthchecks pro App-Service in Coolify — analog zum bisherigen `docker-compose.prod.yaml`
  (`/up`-Endpoint), für alle vier Apps zu wiederholen.
- Genauer Mechanismus für den geteilten Portal/Shop-Login (siehe
  `../01-architecture/MULTI_SUBDOMAIN.md`) — Session-Tabelle, `APP_KEY`-Sharing.
