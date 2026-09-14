<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Enums;

/**
 * Typ eines benutzerdefinierten Feldes (D-111).
 *
 * **Bewusste Ausnahme zu D-094:** die Feldliste selbst ist im UI pflegbar und
 * kann deshalb keine Migration je Aenderung haben. Diese Typliste hier ist es
 * NICHT — sie ist fest und traegt ihren CHECK wie jeder andere Enum.
 */
enum FieldType: string
{
    case String = 'string';
    case Text = 'text';
    case Integer = 'integer';
    case Decimal = 'decimal';
    case Date = 'date';
    case Boolean = 'boolean';
    case Select = 'select';

    /**
     * Die Wertespalte, die zu diesem Typ gehoert.
     *
     * Die Wertetabellen fuehren je Typ eine eigene Spalte statt einer
     * VARCHAR-Spalte fuer alles (D-134) — deshalb muss jede Stelle, die einen
     * Wert liest oder schreibt, hier nachschlagen statt zu raten.
     */
    public function column(): string
    {
        return match ($this) {
            self::String => 'value_string',
            self::Text => 'value_text',
            self::Integer => 'value_integer',
            self::Decimal => 'value_decimal',
            self::Date => 'value_date',
            self::Boolean => 'value_boolean',
            self::Select => 'value_option_id',
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::String => 'Text, einzeilig',
            self::Text => 'Text, mehrzeilig',
            self::Integer => 'Ganzzahl',
            self::Decimal => 'Dezimalzahl',
            self::Date => 'Datum',
            self::Boolean => 'Ja/Nein',
            self::Select => 'Auswahl',
        };
    }
}
