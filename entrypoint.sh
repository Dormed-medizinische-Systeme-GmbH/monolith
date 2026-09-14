#!/bin/sh
set -e

# Migrations laufen NICHT hier, sondern als Pre-Deploy-Command (ADR-015) und als
# Schema-Eigentuemer (ADR-036):
#     php artisan migrate --force --database=pgsql_owner
#
# Im Container-Boot waeren sie bei mehreren Replicas ein Rennen und wuerden einen
# fehlgeschlagenen Deploy als Startschleife tarnen.
php artisan config:cache
php artisan route:cache
php artisan event:cache
php artisan view:cache
exec supervisord -c /etc/supervisord.conf
