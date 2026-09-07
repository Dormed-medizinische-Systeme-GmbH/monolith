---
paths:
  - docker-compose.yml
---

# General

## Zwei getrennte .env-Dateien: Laravel-App vs. Supabase-Compose-Stack
Das Repo hat ZWEI unabhängige Env-Konfigurationen im selben Verzeichnis:
- `.env` / `.env.example` → Laravel-App (APP_KEY, DB_URL, DB_SCHEMA, ...)
- `.env.supabase` / `.env.supabase.example` → docker-compose.yml (self-hosted Supabase Stack)

Niemals die Compose-Variablen in `.env`/`.env.example` mischen oder die Datei `.env.supabase`
in `.env` umbenennen — Docker Compose lädt automatisch eine Datei namens `.env` im selben
Verzeichnis, was mit Laravels `.env` kollidieren würde. Lokales `docker compose up` braucht
`--env-file .env.supabase`. `.env.supabase` ist in .gitignore, niemals committen.

Die meisten SERVICE_*-Variablen in docker-compose.yml werden von Coolify automatisch generiert
(Magic-Var-Syntax SERVICE_<TYPE>_<NAME>: FQDN/URL/USER/PASSWORD/BASE64...) — nicht manuell setzen.

Bekannter vermuteter Bug im Compose-Template (realtime-dev, ~Zeile 315):
`SECRET_KEY_BASE=${SECRET_PASSWORD_REALTIME}` — der Name beginnt mit SECRET_ statt SERVICE_,
matcht also NICHT Coolifys Magic-Var-Muster und hat keinen Fallback-Default. Muss manuell in
`.env.supabase` gesetzt werden (z. B. `openssl rand -base64 48`), sonst startet realtime-dev
vermutlich mit leerem SECRET_KEY_BASE und crasht.
