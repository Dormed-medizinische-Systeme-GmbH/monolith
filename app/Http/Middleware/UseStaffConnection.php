<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

/**
 * Schaltet auf die Mitarbeiterrolle `dormed_staff` um (ADR-036).
 *
 * Wirkt wie ein No-Op, weil `pgsql` ohnehin die Standardverbindung ist — und ist
 * trotzdem noetig: „Standard" ist nichts, worauf eine Sicherheitsgrenze sich
 * verlassen darf. In der Testumgebung, in einem Artisan-Command und in einem
 * Queue-Job ist der Standard jeweils etwas anderes. Alle vier Zugriffspunkte
 * benennen ihre Rolle deshalb ausdruecklich, keiner erbt sie.
 */
final class UseStaffConnection
{
    public function handle(Request $request, Closure $next): Response
    {
        if (config('database.switch_by_access_point')) {
            DB::setDefaultConnection('pgsql');
        }

        return $next($request);
    }
}
