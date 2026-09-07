#!/usr/bin/env bash
# Generates a local .env.supabase (not committed) with real dev-only secrets,
# including the signed anon/service_role JWTs that docker-compose.yml expects.
#
# Coolify auto-generates the SERVICE_* magic vars in production; plain
# `docker compose` does not, so this replicates that step for local dev.
#
# Usage: ./volumes/generate-dev-env.sh
# Safe to re-run: refuses to overwrite an existing .env.supabase.

set -euo pipefail
cd "$(dirname "$0")/.."

if [ -f .env.supabase ]; then
    echo ".env.supabase already exists — not overwriting. Delete it first if you want to regenerate." >&2
    exit 1
fi

php "$(dirname "$0")/generate-dev-env.php" > .env.supabase
echo "Wrote .env.supabase with fresh dev secrets."
