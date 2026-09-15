---
paths:
  - app/**
  - database/**
  - resources/**
  - routes/**
  - tests/**
  - config/**
---

# Sprache: Bezeichner Englisch, Oberfläche Deutsch

Eine Regel, zwei Seiten. Sie gilt ausnahmslos, solange es nur eine
Oberflächensprache gibt — kommt je eine zweite dazu, ist das ein eigener
Durchgang und nicht mit einer Ausnahme hier zu erledigen.

## Englisch: alles, was ein Bezeichner ist

Klassen, Methoden, Funktionen, Variablen, Eigenschaften, Enum-Fälle,
Tabellen, Spalten, Indizes, Constraints, Prop-Namen im Frontend, Schlüssel in
Inertia-Nutzlasten, Testnamen.

```php
public static function assignableFor(string $role): array   // ja
public static function auswaehlbar(string $rolle): array    // nein
```

## Deutsch: alles, was jemand liest

Beschriftungen, Platzhalter, Meldungen, Fehlertexte, Überschriften,
E-Mail-Texte, PDF-Inhalte — und **die URL-Pfade**, denn die stehen in der
Adresszeile und im Lesezeichen: `/firmen`, `/mitarbeiter`,
`/betriebsstaetten`. Die Route-NAMEN bleiben englisch
(`erp.companies.index`), sie sind Bezeichner.

## Deutsch: Dateninhalte, die in die Oberfläche gehen

Was in einer Zelle steht und angezeigt wird, ist deutsch — Rollennamen,
Fachrichtungen, Artikelbezeichnungen, Feldbeschriftungen des
benutzerdefinierten Katalogs. Das **Schema** drumherum bleibt englisch.

```
roles.key   = 'geschaeftsfuehrung'   Bezeichner, englisch behandelt (ASCII, snake_case)
roles.name  = 'Geschäftsführung'     Inhalt, deutsch, wird angezeigt
```

## Kommentare und Dokumentation bleiben Deutsch

Das ist Absicht und keine Ausnahme von oben: Kommentare erklären, sie
bezeichnen nichts. `.docs/`, `.ai/rules/` und Commit-Nachrichten ebenso.

## Stand

**Der Bestand hält die Regel noch nicht ein.** Beim Bauen der ERP-Flächen sind
deutsche Bezeichner entstanden — `auswaehlbar()`, `$standorte`, `merkmale`,
`stammdaten`, `zeilen`, deutsche Inertia-Schlüssel und die Suchparameter
`?suche`/`?sortierung`/`?richtung`. Wer eine solche Stelle anfasst, zieht sie
mit; ein eigener Durchgang dafür steht aus.
