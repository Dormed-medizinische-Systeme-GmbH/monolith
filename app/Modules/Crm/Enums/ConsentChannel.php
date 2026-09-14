<?php

declare(strict_types=1);

namespace App\Modules\Crm\Enums;

/**
 * Kanal einer DSGVO-Einwilligung (D-013).
 *
 * Die Werteliste muss mit dem CHECK-Constraint der Migration synchron bleiben —
 * beide stammen aus derselben D-Entscheidung (D-094).
 */
enum ConsentChannel: string
{
    case Fax = 'fax';
    case Mail = 'mail';
    case Post = 'post';
    case Sms = 'sms';
    case Telefon = 'telefon';
}
