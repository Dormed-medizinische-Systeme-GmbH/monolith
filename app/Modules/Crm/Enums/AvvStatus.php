<?php

declare(strict_types=1);

namespace App\Modules\Crm\Enums;

/**
 * Auftragsverarbeitungsvertrag einer Company (D-013).
 *
 * Die Werteliste muss mit dem CHECK-Constraint der Migration synchron bleiben —
 * beide stammen aus derselben D-Entscheidung (D-094).
 */
enum AvvStatus: string
{
    case None = 'none';
    case Signed = 'signed';

    public function label(): string
    {
        return match ($this) {
            self::None => 'nicht unterzeichnet',
            self::Signed => 'unterzeichnet',
        };
    }
}
