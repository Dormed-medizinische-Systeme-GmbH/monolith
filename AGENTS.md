# AGENTS.md — Projektregeln

## Zweck

Dieses Repository ist eine zentrale Laravel-Modular-Monolith-Anwendung. Die Anwendung bedient mehrere Subdomains aus einer gemeinsamen Codebasis.

Dieses Dokument ist verbindlich für Coding Agents.

## Prioritäten

1. Bestehende fachliche Entscheidungen aus `.docs/` befolgen.
2. Keine Architekturentscheidung stillschweigend ändern.
3. Bei Widersprüchen zwischen bestehendem Code und `.docs/` zuerst den dokumentierten Zielzustand bewerten und die Abweichung sichtbar machen.
4. Bestehende Legacy-Strukturen nicht automatisch als Zielmodell übernehmen.
5. Fachliche Logik gehört in Laravel; PostgreSQL dient zusätzlich als Sicherheits- und Integritätsgrenze.
6. Keine direkte Datenbankkommunikation aus Browser, Mobile App oder Shop/Portal-Frontend.
7. Keine Microservices einführen, sofern keine konkrete fachliche oder technische Notwendigkeit dokumentiert ist.

## Verbindliche Architektur

- Laravel ist der zentrale Application Layer.
- PostgreSQL ist die primäre relationale Datenbank.
- Laravel verbindet sich direkt mit PostgreSQL.
- Supabase ist keine Laufzeitabhängigkeit.
- Docker Compose stellt die lokale Entwicklungsumgebung bereit.
- Die Anwendung ist ein Modular Monolith.
- Mehrere Subdomains verwenden dieselbe Laravel-Anwendung und dieselbe Codebasis.
- Gemeinsame Assets, UI-Komponenten, Authentifizierung, Policies und Infrastruktur werden zentral gehalten.
- Fachliche Module dürfen intern sauber getrennt werden, dürfen aber nicht zu eigenständigen Deployments werden, solange dies nicht ausdrücklich beschlossen wird.
- Authentifizierung und Autorisierung erfolgen zunächst in Laravel.
- PostgreSQL RLS kann als Defense-in-Depth eingesetzt werden; RLS darf nicht blind aktiviert werden, ohne den Request-/User-Kontext technisch korrekt an PostgreSQL zu übergeben.
- Realtime ist für spätere Ausbaustufen mit Laravel Reverb vorgesehen.
- Hintergrundverarbeitung erfolgt über Laravel Queues/Jobs.
- Binäre Dateien werden perspektivisch über S3-kompatiblen Object Storage (z. B. MinIO) behandelt.
- Jede fachlich relevante Änderung muss auditierbar sein.

## Multi-Subdomain

Die Subdomains sind Anwendungskontexte, keine getrennten Laravel-Projekte.

Beispiel:

- `crm.<base-domain>`
- `portal.<base-domain>`
- `shop.<base-domain>`

Alle greifen auf dieselbe Laravel-Installation zu.

Subdomain-Erkennung muss zentral und explizit erfolgen. Fachliche Autorisierung darf niemals nur auf dem Hostnamen beruhen.

## Coding-Regeln

- Nutze Laravel-Konventionen, sofern diese nicht mit den Architekturregeln kollidieren.
- Nutze Eloquent und Query Builder für Datenzugriff.
- Nutze Form Requests für komplexe Eingabevalidierung.
- Nutze Policies/Gates bzw. das etablierte Permission-System für Autorisierung.
- Vermeide Controller mit umfangreicher Fachlogik.
- Vermeide globale Helper, wenn eine klar abgegrenzte Klasse besser geeignet ist.
- Keine fachlichen Bedeutungen über `is_*`-Booleanfelder modellieren, wenn ein echtes Fachobjekt oder eine Beziehung erforderlich ist.
- Keine Universal-Tabellen, die mehrere fachlich unterschiedliche Prozesse über Typfelder und optionale Spalten abbilden.
- Keine `TODO`-Implementierungen als angeblich fertige Architektur.
- Tests müssen den fachlichen Workflow absichern, nicht nur HTTP-Statuscodes.
- Migrationen müssen reproduzierbar und deterministisch sein.
- Seed-Daten müssen eindeutig als Demo/Testdaten erkennbar sein.
- Produktionsdaten dürfen niemals Bestandteil des Repositorys sein.

## Umgang mit Unsicherheit

Wenn eine Fachfrage noch nicht entschieden ist:

1. Nicht eigenmächtig eine irreversible Struktur wählen.
2. Die Annahme im Code und/oder in `.docs/` dokumentieren.
3. Eine möglichst reversible technische Lösung wählen.
4. Keine Migration erzeugen, deren spätere Änderung unnötig teuer wäre.

## Legacy-System

Die bereitgestellten XML-Definitionen beschreiben den Ist-Zustand. Sie sind Referenzmaterial, kein Zielschema.

Insbesondere dürfen folgende Muster nicht einfach kopiert werden:

- universelles `Address`-Objekt für Unternehmen, Personen und Kontakte
- überladenes generisches Ticket
- Servicevertrag als gleichzeitig Vertrag, Gerät, technische Konfiguration und Wartungsplanung
- Checklisten als fest verdrahtete Spalten
- fachliche Bedeutung ausschließlich über generische Typfelder

## Definition of Done

Eine Aufgabe ist erst abgeschlossen, wenn:

- die Architekturentscheidung dokumentiert ist, falls sie neu ist,
- Migrationen reproduzierbar sind,
- relevante Tests vorhanden sind,
- Berechtigungen berücksichtigt wurden,
- Audit-Anforderungen berücksichtigt wurden,
- Subdomain-Kontext korrekt funktioniert,
- keine bestehende Modulgrenze unnötig verletzt wurde,
- lokale Docker-Entwicklung weiterhin funktioniert.
