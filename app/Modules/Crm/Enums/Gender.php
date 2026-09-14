<?php

declare(strict_types=1);

namespace App\Modules\Crm\Enums;

/**
 * Geschlecht einer Person (CORE.md).
 *
 * Die Werteliste muss mit dem CHECK-Constraint der Migration synchron bleiben —
 * beide stammen aus derselben D-Entscheidung (D-094).
 */
enum Gender: string
{
    case Maennlich = 'maennlich';
    case Weiblich = 'weiblich';
    case Divers = 'divers';
    case Unbekannt = 'unbekannt';
}
