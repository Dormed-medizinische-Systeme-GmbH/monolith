# Domain Model — Discovery-Zielbild

## Grundprinzip

Das neue Modell ist fachlich strukturiert.

Es ersetzt nicht einfach die Legacy-Tabellen.

## Core

### Company

Repräsentiert eine juristische oder organisatorische Kunden-/Geschäftseinheit.

Eine Company kann mehrere Locations besitzen.

### Person

Repräsentiert eine reale Person.

Eine Person ist nicht automatisch genau einer Company zugeordnet.

### CompanyContact

Explizite Beziehung zwischen Person und Company.

Eine Person kann in seltenen Fällen Kontaktperson mehrerer Companies sein.

Mögliche spätere Attribute:

- Rolle/Funktion
- primärer Kontakt
- Gültigkeitszeitraum
- Kommunikationspräferenzen

### Address

Adresse als wiederverwendbare Adressinformation.

Sie ist nicht gleichzeitig das fachliche Objekt „Company“ oder „Person“.

### Location

Reale Betriebs-/Service-/Standortstruktur einer Company.

Eine Company kann mehrere Locations haben.

## Service

### Device

Ein physisches Gerät.

Ein Gerät soll langfristig ein First-Class-Objekt der Warenwirtschaft sein.

### ServiceContract

Servicevereinbarung für genau ein Gerät.

Im aktuellen Zielbild:

```text
Device 1 <-> 1 ServiceContract
```

Diese Regel soll nicht ohne fachliche Begründung zu einer Many-to-Many-Struktur erweitert werden.

### Maintenance

Konkrete Wartungsinstanz bzw. Wartungsvorgang.

Sie ist von der wiederkehrenden Servicevereinbarung zu unterscheiden.

### ServiceCase

Störung/Serviceeinsatz, der nicht automatisch eine Wartung ist.

## Dokumente

Strukturierte Fachdaten sind die Source of Truth.

PDFs sind möglichst Repräsentationen davon.

Beispiel:

```text
Invoice data
    |
    +--> PDF representation
    +--> e-invoice representation
```

## Billing

Rechnungen müssen historische Daten erhalten.

Eine aktuelle Company-Adresse darf nicht nachträglich die historische Rechnungsadresse einer bereits ausgestellten Rechnung verändern.

## Location und Billing

Keine starre Matrix erzwingen.

Mögliche Realität:

```text
Company A
├── Location A
├── Location B
├── Location C
└── central billing address
```

Ein Service kann an Location B stattfinden, während die Rechnung an eine zentrale Adresse geht.

Ob der Rechnungsempfänger immer dieselbe Company ist, ist als Fachfrage noch offen.

## Offene Fachfragen

- Kann eine Managementgesellschaft Rechnungen für mehrere unabhängige Praxen erhalten?
- Ist die Managementgesellschaft dann eine eigene Company?
- Kann Service-/Leistungsempfänger A und Rechnungsempfänger B unterschiedliche Companies sein?
- Welche Rollen benötigt `CompanyContact`?
- Welche Adresse gehört fachlich zur Company, welche zur Location?
