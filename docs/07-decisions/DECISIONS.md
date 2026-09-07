# Architecture Decisions

## ADR-001 — PostgreSQL statt Supabase als Application Platform

Status: Accepted

PostgreSQL ist die primäre Datenbank. Supabase ist keine notwendige Laufzeitplattform.

Begründung:

- Laravel soll zentraler Application Layer sein.
- direkter Laravel -> PostgreSQL Zugriff ist gewünscht.
- Vendor Lock-in soll reduziert werden.
- PostgreSQL bietet die benötigten relationalen Fähigkeiten einschließlich nativer RLS-Funktionalität.

## ADR-002 — Modular Monolith

Status: Accepted

CRM, Portal, Shop und später Inventory werden innerhalb einer Laravel-Anwendung entwickelt.

## ADR-003 — Multi-Subdomain

Status: Accepted

Mehrere Subdomains bedienen dieselbe Laravel-Codebasis.

## ADR-004 — Legacy nicht als Zielschema

Status: Accepted

Legacy-XMLs dienen der Ist-Analyse.

Universalobjekte werden nicht automatisch übernommen.

## ADR-005 — Workflow statt freie Feldbearbeitung

Status: Accepted

Fachliche Prozesse werden als kontrollierte Zustands-/Aktionsmodelle umgesetzt.

## ADR-006 — Structured Data as Source of Truth

Status: Accepted

Strukturierte Fachdaten sind primär. PDFs und andere Darstellungen sind Repräsentationen, sofern kein rechtlicher/operativer Grund für die Datei als unveränderliche Primärrepräsentation besteht.

## ADR-007 — PostgreSQL als zusätzliche Security Boundary

Status: Accepted in principle

RLS/Constraints/Trigger können zusätzliche technische Sicherheit und Integrität liefern.

Die konkrete RLS-Architektur wird vor produktiver Aktivierung spezifiziert.

## ADR-008 — Device und ServiceContract getrennt

Status: Accepted

Ein ServiceContract ist nicht gleichzeitig das vollständige Device Master Data Objekt.

## ADR-009 — Company / Person / Contact / Location getrennt

Status: Accepted

Die Legacy-Universaladresse wird nicht als Zielmodell übernommen.

## ADR-010 — Keine künstliche Vollständigkeit

Status: Accepted

Offene fachliche Fragen werden erst entschieden, wenn reale Prozesse und UI sie konkret machen.
