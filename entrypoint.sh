#!/bin/sh
set -e

php artisan config:cache
php artisan route:cache
php artisan event:cache
php artisan view:cache

# ==========================================================================
# ⚠ ÜBERGANGSLÖSUNG — VOR DEM ECHTEN PRODUKTIVGANG ENTFERNEN
# ==========================================================================
#
# Migrationen und Seed laufen hier im Container-Start. Das ist fuer die
# Testumgebung auf Coolify gedacht, damit ein Deploy ohne Handgriffe eine
# benutzbare Anwendung hinterlaesst.
#
# Warum das in Produktion NICHT bleiben darf (ADR-015):
#
#   1. Bei mehreren Replicas starten mehrere Container gleichzeitig und
#      migrieren gegeneinander. `--isolated` entschaerft das, loest es aber
#      nicht — der Lock liegt im Cache, und der ist pro Container.
#   2. Eine fehlgeschlagene Migration tarnt sich als Startschleife: der
#      Container stirbt und wird neu gestartet, statt den Deploy scheitern zu
#      lassen. Man sucht dann am falschen Ende.
#   3. `--seed` faehrt bei JEDEM Start. Die Seeder sind idempotent, aber
#      `DevelopmentAccountSeeder` legt Zugaenge mit dem Passwort `password` an.
#      Er ueberspringt sich in Produktion (APP_ENV=production) — das ist die
#      einzige Sicherung, und sie haengt an einer Umgebungsvariable.
#
# Richtig ist ein Pre-Deploy-Command in Coolify:
#
#     php artisan migrate --force --database=pgsql_owner
#
# Dann diese drei Zeilen hier loeschen.
#
# `--database=pgsql_owner`: die Anwendung verbindet sich als `dormed_staff`,
# und die Rolle darf kein Schema aendern (ADR-036).
# ==========================================================================
php artisan migrate --force --seed --database=pgsql_owner

exec supervisord -c /etc/supervisord.conf
