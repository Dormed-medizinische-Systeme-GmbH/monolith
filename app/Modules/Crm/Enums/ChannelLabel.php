<?php

declare(strict_types=1);

namespace App\Modules\Crm\Enums;

/**
 * Verwendungszweck eines Kanals (D-010). Fest und auswertungsrelevant — anders als `company_contacts.role`, das Freitext bleibt (D-005).
 *
 * Die Werteliste muss mit dem CHECK-Constraint der Migration synchron bleiben —
 * beide stammen aus derselben D-Entscheidung (D-094).
 */
enum ChannelLabel: string
{
    case Geschaeftlich = 'geschaeftlich';
    case Praxis = 'praxis';
    case Zentrale = 'zentrale';
    case Durchwahl = 'durchwahl';
    case Rechnungsversand = 'rechnungsversand';
    case Privat = 'privat';
    case MobilPersoenlich = 'mobil_persoenlich';
    case MobilArzt = 'mobil_arzt';
    case Homepage = 'homepage';
    case Sonstige = 'sonstige';
}
