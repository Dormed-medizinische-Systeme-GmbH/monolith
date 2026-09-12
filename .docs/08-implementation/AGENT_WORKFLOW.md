# Arbeitsweise für Coding Agents

## Vor jeder Änderung

1. Relevante Dokumentation lesen.
2. Bestehenden Code prüfen.
3. Bestehende Laravel-Version und verfügbare Packages prüfen.
4. Keine Annahme über nicht geprüfte Infrastruktur treffen.

## Bei Architekturänderungen

Wenn eine Änderung Auswirkungen auf:

- Module
- Routing
- Datenmodell
- Auth
- Authorization
- Security
- Docker
- Subdomains

hat, muss die entsprechende Dokumentation aktualisiert werden.

## Laravel Skills / Boost

Wenn im Repository oder in der Agent-Umgebung Laravel Skills bzw. Laravel Boost verfügbar sind, sollen diese für Laravel-spezifische Fragen und Implementierung genutzt werden.

Sie ersetzen nicht die projektspezifische Dokumentation.

## AI Rules

Ein vorhandener AI-Rules-Ordner soll weiterverwendet werden.

Regeln dürfen nicht dupliziert und widersprüchlich gepflegt werden.

Priorität:

```text
Projektentscheidungen
    >
AI Rules
    >
allgemeine Agent-Annahmen
```

Wenn AI Rules vorhanden sind, sind sie zu prüfen und bei Bedarf auf die Architektur dieses Projekts anzupassen.

## Keine Big-Bang-Implementierung

Nicht gleichzeitig:

- gesamtes CRM
- Portal
- Shop
- Service
- Billing
- Inventory

implementieren.

Jeder Slice muss lokal lauffähig und testbar bleiben.
