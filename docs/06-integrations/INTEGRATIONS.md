# Integrationen

## Ziel

Externe Systeme werden über klar abgegrenzte Adapter/Integrationsmodule angebunden.

Vorgesehene Integrationen:

- Microsoft 365
- DATEV
- PayPal
- Versanddienstleister
- E-Mail
- SFTP
- Webhooks
- ggf. Google Search Console API
- digitale Signaturen

## E-Mail

Automatische Archivierung ist ein wichtiges Ziel.

Das aktuelle Problem:

- Benutzer müssen manuell archivieren
- Empfänger muss teilweise bereits im CRM existieren
- Zuordnung ist dadurch unzuverlässig

Ziel:

```text
Mailbox
  |
  v
Laravel Integration
  |
  +--> identify sender/recipient
  +--> match Company/Person
  +--> archive communication
  +--> retain attachments
```

Die konkrete Microsoft-Integration wird separat spezifiziert.

## Integrationsprinzip

Fachlogik darf nicht direkt in SDK-Aufrufen verteilt werden.

Bevorzugt:

```text
Domain
  -> Application Service
      -> Integration Adapter
          -> External API
```

## Asynchronität

Nicht zeitkritische Integrationen werden über Jobs abgewickelt.

Fehler müssen retrybar und auditierbar sein.
