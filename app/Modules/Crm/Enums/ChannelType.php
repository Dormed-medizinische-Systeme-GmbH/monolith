<?php

declare(strict_types=1);

namespace App\Modules\Crm\Enums;

/**
 * Art eines Kommunikationskanals (D-010).
 *
 * Die Werteliste muss mit dem CHECK-Constraint der Migration synchron bleiben —
 * beide stammen aus derselben D-Entscheidung (D-094).
 */
enum ChannelType: string
{
    case Phone = 'phone';
    case Mobile = 'mobile';
    case Fax = 'fax';
    case Email = 'email';
    case Web = 'web';
}
