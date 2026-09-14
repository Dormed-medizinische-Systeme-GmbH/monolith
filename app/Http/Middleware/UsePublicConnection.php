<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

/**
 * Schaltet auf die anonyme Datenbankrolle `dormed_public` um (ADR-036).
 *
 * Gehoert an die AEUSSERSTE Stelle der Route-Group: alles, was vorher aufgeloest
 * wird — andere Middleware, Route-Model-Binding, Event-Listener — laeuft sonst
 * noch auf der Standardverbindung.
 *
 * Die Rolle hat auf Kundendaten keine Grants. Ein Fehler im oeffentlichen
 * Template kann Rechnungen oder Servicefaelle damit nicht lesen — nicht, weil es
 * verboten waere, sondern weil die Verbindung sie nicht sieht.
 */
final class UsePublicConnection
{
    public function handle(Request $request, Closure $next): Response
    {
        if (config('database.switch_by_access_point')) {
            DB::setDefaultConnection('pgsql_public');
        }

        return $next($request);
    }
}
