# Dokumentation

Diese Dokumentation beschreibt Zielarchitektur, Projektstruktur, Entwicklungsregeln und fachliche Leitplanken.

## Lesereihenfolge für Agents

> **Stand 2026-09-12** — aktualisiert nach ADR-011–025 und dem Billing-/
> Datenbank-Grill-Durchlauf. Siehe zuerst `08-implementation/IMPLEMENTATION_SEQUENCE.md`
> für den aktuellen Bau-Fokus (`packages/core` + `apps/crm`, ADR-020).

1. `../AGENTS.md`
2. `01-architecture/ARCHITECTURE.md`
3. `01-architecture/PROJECT_STRUCTURE.md`
4. `01-architecture/MULTI_SUBDOMAIN.md`
5. `02-development/LOCAL_DEVELOPMENT.md`
6. `02-development/TESTING_AND_SEEDING.md`
7. `03-security/SECURITY.md`
8. `03-security/AUTHORIZATION.md`
9. `03-security/IDENTITY_RBAC.md`
10. `04-database/DATABASE.md`
11. `04-domain/DOMAIN.md` (Statusübersicht + Discovery für noch offene Bereiche)
12. `04-domain/CORE.md` · `SERVICE.md` · `SCHEDULING.md` · `SALES.md` · `BILLING.md`
    (fertig spezifizierte Bereiche)
13. `04-domain/LEGACY_MAPPING.md`
14. `05-modules/CRM.md` · `SERVICE.md` · `PORTAL.md` · `SHOP.md` · `INVENTORY.md`
    (Modul-Prinzipien, ergänzend zu 04-domain)
15. `06-infrastructure/DOCKER.md` · `REALTIME.md` · `STORAGE.md`
16. `06-integrations/INTEGRATIONS.md`
17. `07-decisions/DECISIONS.md` (ADRs)
18. `07-decisions/grill-log.md` (chronologisches Entscheidungsprotokoll, D-001–)
19. `08-implementation/IMPLEMENTATION_SEQUENCE.md` · `AGENT_WORKFLOW.md`
20. `00-legacy/LEGACY_REFERENCE.md` (nur bei Migrations-/Altdaten-Fragen)

## Dokumentationsprinzip

Die Dokumentation trennt bewusst:

- Zielarchitektur
- Entwicklungs-/Betriebsregeln
- fachliche Erkenntnisse
- Legacy-Ist-Zustand
- noch offene Entscheidungen

Nicht entschiedene Punkte werden als offen markiert. Agents dürfen offene Punkte nicht als endgültige Fachentscheidung behandeln.
