<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Queries;

/**
 * Geldbetraege fuer die Anzeige.
 *
 * Formatiert wird auf dem SERVER: die Betraege liegen als `decimal(12,2)` vor,
 * und der Weg ueber eine Gleitkommazahl im Browser ist genau der, auf dem aus
 * 49.000,00 irgendwann 48.999,99 wird. Gerechnet wird hier nichts.
 */
final class Money
{
    public static function format(int|float|string|null $amount): ?string
    {
        if ($amount === null) {
            return null;
        }

        return number_format((float) $amount, 2, ',', '.').' €';
    }
}
