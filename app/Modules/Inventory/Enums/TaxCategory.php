<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Enums;

/**
 * Steuerliche Einordnung einer Position (D-074).
 *
 * Am Artikel nur VORBELEGUNG — massgeblich ist der Wert an der Position, weil
 * derselbe Artikel je nach Empfaenger anders zu behandeln ist (BILLING.md).
 *
 * Die Werteliste muss mit dem CHECK-Constraint der Migration synchron bleiben —
 * beide stammen aus derselben D-Entscheidung (D-094).
 */
enum TaxCategory: string
{
    case Standard19 = 'standard_19';
    case ReverseCharge = 'reverse_charge';
    case ExportTaxFree = 'export_tax_free';
    case OtherTaxFree = 'other_tax_free';

    public function label(): string
    {
        return match ($this) {
            self::Standard19 => 'Regelsteuersatz 19 %',
            self::ReverseCharge => 'Reverse Charge',
            self::ExportTaxFree => 'Ausfuhr, steuerfrei',
            self::OtherTaxFree => 'sonstiges steuerfrei',
        };
    }
}
