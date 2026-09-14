<?php

declare(strict_types=1);

namespace App\Modules\Crm\Enums;

/**
 * Herkunft einer Einwilligung (D-013).
 *
 * Die Werteliste muss mit dem CHECK-Constraint der Migration synchron bleiben —
 * beide stammen aus derselben D-Entscheidung (D-094).
 */
enum ConsentSource: string
{
    case Formular = 'formular';
    case Muendlich = 'muendlich';
    case Telefonisch = 'telefonisch';
    case Import = 'import';
    case Sonstige = 'sonstige';

    public function label(): string
    {
        return match ($this) {
            self::Formular => 'Formular',
            self::Muendlich => 'mündlich',
            self::Telefonisch => 'telefonisch',
            self::Import => 'Import',
            self::Sonstige => 'sonstige',
        };
    }
}
