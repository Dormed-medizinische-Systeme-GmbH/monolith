<?php

declare(strict_types=1);

use App\Http\Middleware\UseCustomerConnection;
use App\Http\Middleware\UsePublicConnection;
use App\Http\Middleware\UseStaffConnection;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Jede fachliche Route gehoert genau einem Zugriffspunkt (ADR-033/036)
|--------------------------------------------------------------------------
|
| Der Grund fuer diesen Test: die Verbindungs-Middleware haengt an den vier
| Domain-Gruppen. Eine Route OHNE Domain-Bindung wird auf allen vier Hostnamen
| ausgeliefert UND laeuft an der Umschaltung vorbei — also auf der
| Standardverbindung `dormed_staff`.
|
| Genau das war am 2026-09-14 der Fall: 45 Routen, darunter `/login`,
| `/dashboard` und alle Einstellungen, antworteten auch unter dormed.de.
| Aufgefallen ist es bei einer Durchsicht, nicht durch einen Test — deshalb
| gibt es diesen hier.
|
*/

/**
 * Infrastruktur ohne fachlichen Bezug. Sie kennt keinen Zugriffspunkt und
 * braucht deshalb auch keine Rolle.
 */
const DOMAIN_FREE_BY_DESIGN = [
    'up',                          // Health-Check des Reverse-Proxy
    'storage/{path}',              // Symlink-Fallback fuer oeffentliche Dateien
    '_boost/browser-logs',         // Laravel Boost, nur lokal
    '_inertia/devtools/entries',   // Inertia-Devtools, nur lokal
    '_inertia/devtools/entries/{id}',
];

test('keine fachliche Route ist ohne Domain-Bindung', function (): void {
    $unbound = collect(Route::getRoutes())
        ->reject(fn ($route) => $route->getDomain() !== null)
        ->map(fn ($route) => $route->uri())
        ->reject(fn (string $uri) => in_array($uri, DOMAIN_FREE_BY_DESIGN, true))
        ->unique()
        ->sort()
        ->values()
        ->all();

    expect($unbound)->toBe([], implode("\n", [
        'Diese Routen antworten auf ALLEN vier Hostnamen und laufen an der',
        'Verbindungs-Middleware vorbei (ADR-036). Entweder in eine Domain-Gruppe',
        'in bootstrap/app.php aufnehmen, oder — wenn es wirklich Infrastruktur',
        'ist — bewusst in DOMAIN_FREE_BY_DESIGN eintragen:',
        '  '.implode("\n  ", $unbound),
    ]));
});

test('jede Domain-Gruppe traegt ihre Verbindungs-Middleware', function (string $key, string $middleware): void {
    $routes = collect(Route::getRoutes())
        ->filter(fn ($route) => $route->getDomain() === config("domains.{$key}"));

    expect($routes)->not->toBeEmpty("Keine Route fuer den Zugriffspunkt {$key}");

    $without = $routes
        ->reject(fn ($route) => in_array($middleware, $route->gatherMiddleware(), true))
        ->map(fn ($route) => $route->uri())
        ->values()
        ->all();

    expect($without)->toBe([], "Ohne {$middleware}: ".implode(', ', $without));
})->with([
    ['website', UsePublicConnection::class],
    ['erp', UseStaffConnection::class],
    ['portal', UseCustomerConnection::class],
    ['shop', UsePublicConnection::class],
]);

test('der Mitarbeiter-Login ist nur unter der ERP-Domain erreichbar', function (string $key): void {
    $this->get('http://'.config("domains.{$key}").'/login')->assertNotFound();
})->with(['website', 'portal', 'shop']);

test('unter der ERP-Domain ist er erreichbar', function (): void {
    $this->get('http://'.config('domains.erp').'/login')->assertOk();
});
