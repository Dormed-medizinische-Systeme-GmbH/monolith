<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Routing\Redirector;
use Inertia\Inertia;

/**
 * Eine Rueckmeldung fuer die naechste Seite.
 *
 * `Inertia::flash()` und NICHT `->with(...)`: Inertia v3 fuehrt einen eigenen
 * Flash-Speicher und liest die Laravel-Session dafuer nicht aus. Ueber
 * `->with()` gesetzte Meldungen kaemen im Browser nie an — ohne Fehler, es
 * taete nur nichts. Der Empfaenger ist der `Toaster` in der ERP-Huelle.
 */
final class Flash
{
    public static function success(string $message): Redirector
    {
        return self::melde('success', $message);
    }

    public static function error(string $message): Redirector
    {
        return self::melde('error', $message);
    }

    private static function melde(string $type, string $message): Redirector
    {
        Inertia::flash('toast', ['type' => $type, 'message' => $message]);

        return redirect();
    }
}
