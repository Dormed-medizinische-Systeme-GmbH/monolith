<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use PDO;
use Symfony\Component\HttpFoundation\Response;

/**
 * Schaltet auf die Kundenrolle `dormed_customer` um und setzt den Firmenkontext
 * fuer die RLS-Policies (ADR-036).
 *
 * Fail-closed: ist `app.company_id` nicht gesetzt, liefert
 * `current_setting('app.company_id', true)` NULL, der Policy-Vergleich wird nie
 * wahr, und der Kunde sieht NULL Zeilen — nicht alle. Vergisst diese Middleware
 * ihre Aufgabe, bricht das Portal; es leakt nicht.
 *
 * Gehoert an die AEUSSERSTE Stelle der Route-Group (`.ai/rules/routing.md`).
 *
 * ACHTUNG: unvertraeglich mit PgBouncer im Transaction-Mode — eine sitzungsweite
 * Variable ueberlebt dort den Verbindungswechsel nicht und koennte an den naechsten
 * Request geraten. Bei der Last aus ADR-033 wird kein Pooler gebraucht; kommt
 * einer, muss das auf `set_config(…, true)` in einer expliziten Transaktion
 * umgestellt werden.
 */
final class UseCustomerConnection
{
    public function handle(Request $request, Closure $next): Response
    {
        if (config('database.switch_by_access_point')) {
            DB::setDefaultConnection('pgsql_customer');
        }

        $connection = DB::connection('pgsql_customer');
        $companyId = $this->companyId($request);

        /*
         * Die Variable muss an JEDER PDO-Instanz haengen, nicht nur an der ersten:
         * ein Reconnect mitten im Request wuerde sonst ohne Firmenkontext
         * weiterarbeiten. Laravel hat keinen "nach dem Verbinden"-Hook, also
         * prueft dieser Callback vor jeder Anweisung, ob die PDO-Instanz noch
         * dieselbe ist.
         *
         * `$pdo->exec()` statt `$connection->unprepared()` ist Pflicht, nicht
         * Geschmack: `unprepared()` laeuft durch `Connection::run()` und wuerde
         * genau diesen Callback erneut ausloesen — Endlosrekursion.
         */
        $configured = null;

        $connection->beforeExecuting(static function () use ($connection, $companyId, &$configured): void {
            $pdo = $connection->getPdo();

            if ($configured === $pdo) {
                return;
            }

            $pdo->exec(sprintf(
                'SELECT set_config(%s, %s, false)',
                $pdo->quote('app.company_id'),
                $pdo->quote((string) $companyId),
            ));

            $configured = $pdo;
        });

        return $next($request);
    }

    /**
     * Die Firma des angemeldeten Kontakts (ADR-037): `customer_accounts` →
     * `people` → `company_contacts`.
     *
     * Leer, solange niemand angemeldet ist — dann greift die Policy
     * fail-closed und liefert null Zeilen statt aller. Das ist der gewollte
     * Zustand auf der Anmeldemaske.
     *
     * Der Test setzt den Wert stattdessen ueber die Session; deshalb hat sie
     * hier weiterhin Vorrang.
     */
    private function companyId(Request $request): string
    {
        $fromSession = $request->hasSession()
            ? $request->session()->get('app.company_id')
            : null;

        if ($fromSession !== null) {
            return (string) $fromSession;
        }

        return (string) ($request->user('customer')?->companyId() ?? '');
    }
}
