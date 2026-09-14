<?php

declare(strict_types=1);

namespace App\Support;

use Illuminate\Http\Request;

/**
 * Wohin nach einer Anmeldung.
 *
 * Portal und Shop teilen sich EINE Session (ADR-037), und weil sie auf
 * verschiedenen Hostnamen liegen, muss das Cookie auf der gemeinsamen
 * Basisdomain sitzen — `SESSION_DOMAIN=.dormed.de`. Damit teilen sich aber
 * ALLE vier Zugriffspunkte dieselbe Session, und `url.intended` ist eine
 * gewoehnliche Sitzungsvariable.
 *
 * Die Folge, real aufgetreten: wer als Gast `shop.dormed.de` aufruft, legt dort
 * `url.intended` ab. Meldet er sich danach im ERP an, schickt ihn Fortifys
 * `redirect()->intended()` in den Shop — aus einem Inertia-XHR heraus als
 * CORS-Fehler, weil die Antwort von einem anderen Ursprung kommt.
 *
 * Deshalb wird ein gemerktes Ziel nur akzeptiert, wenn es zum aufrufenden
 * Hostnamen gehoert. Sonst geht es auf die Startseite des Zugriffspunkts.
 */
final class AccessPointRedirect
{
    public static function after(Request $request, string $fallback): string
    {
        $intended = $request->session()->pull('url.intended');

        if (is_string($intended) && self::belongsToCurrentHost($intended, $request)) {
            return $intended;
        }

        return $fallback;
    }

    private static function belongsToCurrentHost(string $url, Request $request): bool
    {
        $host = parse_url($url, PHP_URL_HOST);

        // Relative Ziele („/einstellungen") haben keinen Host und sind damit
        // ohnehin auf dem aufrufenden Zugriffspunkt.
        return $host === null || $host === $request->getHost();
    }
}
