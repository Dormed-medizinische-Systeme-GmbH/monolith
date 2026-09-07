# Security Architecture

## Grundsatz

Sicherheit wird mehrschichtig umgesetzt.

```text
Browser/Mobile
    |
    v
Laravel Authentication
    |
    v
Laravel Authorization / Policies
    |
    v
Business Rules
    |
    v
PostgreSQL
    |
    v
optional RLS / Constraints / Triggers
```

## Customer Data Isolation

Ein Kunde darf niemals Daten eines anderen Kunden sehen.

Diese Regel darf nicht ausschließlich durch UI-Filter abgesichert werden.

Mindestens:

- Laravel Authorization
- Query Scoping
- fachliche Policies
- Datenbank-Fremdschlüssel
- ggf. PostgreSQL RLS als zusätzliche Grenze

## PostgreSQL RLS

RLS ist native PostgreSQL-Funktionalität.

Wenn RLS als Sicherheitsgrenze verwendet wird, muss Laravel den relevanten Benutzer-/Unternehmenskontext technisch korrekt an PostgreSQL übergeben.

Nicht zulässig:

```text
RLS aktivieren
+
Laravel verwendet einen privilegierten BYPASSRLS-User
+
Annehmen, dass RLS trotzdem schützt
```

## Audit

Relevante Änderungen müssen nachvollziehbar sein:

- wer
- wann
- was
- vorher
- nachher
- warum, sofern fachlich erforderlich
- Löschgrund
- Kontext

Technische Audit-Erfassung kann teilweise durch PostgreSQL-Trigger abgesichert werden.

Komplexe fachliche Workflows gehören weiterhin in Laravel.

## Datenschutz

Personenbezogene Daten müssen bewusst modelliert werden.

Keine unnötigen Kopien personenbezogener Daten.

Logs dürfen keine unnötigen personenbezogenen oder geheimen Informationen enthalten.

## Secrets

Secrets gehören ausschließlich in sichere Environment-/Secret-Mechanismen.

Keine API-Keys, Passwörter oder Tokens im Repository.

## Subdomains

Subdomains sind kein Trust Boundary.

Jede geschützte Aktion benötigt eine eigene Autorisierungsprüfung.
