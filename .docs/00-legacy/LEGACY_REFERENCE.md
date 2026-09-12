# Legacy Reference

## Zweck

Diese Datei dokumentiert die Rolle der bereitgestellten Altdefinitionen.

Die rohen Schema-Exporte liegen unter [`xml/`](xml/) (CAS genesisWorld
`gwconnect`-Objektdefinitionen aus dem produktiven CRM). Lese-Anleitung und
Inventar: [`xml/README.md`](xml/README.md).

| Objekt | Datei | Spalten |
| --- | --- | --- |
| Address | [`xml/Adressen.xml`](xml/Adressen.xml) | 356 |
| Verkaufschancen | [`xml/Verkaufschancen.xml`](xml/Verkaufschancen.xml) | 34 |
| Termine | [`xml/Termine.xml`](xml/Termine.xml) | 25 |
| Tickets | [`xml/Tickets.xml`](xml/Tickets.xml) | 112 |
| Serviceverträge_NEU | [`xml/Servicevertraege-NEU.xml`](xml/Servicevertraege-NEU.xml) | 83 |

Sie stammen aus dem aktuellen System und dienen als fachliche **Ist-Referenz**
(ADR-004) — nicht als Zielschema.

## Kernerkenntnisse

### Adressen (`xml/Adressen.xml`)

Das alte `Address`-Objekt ist ein universelles Beziehungsobjekt mit sehr vielen
Verwendungszwecken (Unternehmen, Kontaktpersonen, Rechnungsadressen, Standorte …).

### Opportunities (`xml/Verkaufschancen.xml`)

Das alte Opportunity-Modell besitzt bereits typische Sales-Daten wie Status,
Phase, Wahrscheinlichkeit, Budget und Wettbewerber.

### Termine (`xml/Termine.xml`)

Termine sind derzeit relativ generisch und müssen im Zielsystem stärker mit
fachlichen Vorgängen verknüpft werden.

### Tickets (`xml/Tickets.xml`)

Tickets vermischen Service, Wartung, Checklisten, Messungen, Arbeiten und Abrechnung.

### Serviceverträge (`xml/Servicevertraege-NEU.xml`)

Serviceverträge vermischen Vertrag, Gerät und technische Gerätekonfiguration.

## Konsequenz

Die neue Architektur soll diese Vermischung reduzieren und fachliche Prozesse
explizit modellieren. Die konkrete Zerlegung wird in `/grill-me` festgelegt und
in `.docs/04-domain/` + der ROADMAP dokumentiert.
