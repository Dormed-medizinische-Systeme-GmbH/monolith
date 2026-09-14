<?php

declare(strict_types=1);

namespace App\Modules\Crm\Enums;

/**
 * Stand der Geokodierung einer Adresse (D-024). Grundlage der Fahrtzone.
 *
 * Die Werteliste muss mit dem CHECK-Constraint der Migration synchron bleiben —
 * beide stammen aus derselben D-Entscheidung (D-094).
 */
enum GeocodeStatus: string
{
    case Pending = 'pending';
    case Ok = 'ok';
    case Failed = 'failed';
    case Manual = 'manual';
}
