<?php

declare(strict_types=1);

namespace App\Modules\Crm\Enums;

/**
 * Stand einer Einwilligung (D-013). Ein Wechsel erzeugt einen NEUEN Datensatz.
 *
 * Die Werteliste muss mit dem CHECK-Constraint der Migration synchron bleiben —
 * beide stammen aus derselben D-Entscheidung (D-094).
 */
enum ConsentStatus: string
{
    case Erteilt = 'erteilt';
    case Widerrufen = 'widerrufen';
}
