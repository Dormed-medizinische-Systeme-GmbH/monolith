<?php

declare(strict_types=1);

use App\Http\Controllers\Customer\SessionController;
use App\Http\Controllers\Portal\HomeController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| my.dormed.de — Kundenportal (ADR-033)
|--------------------------------------------------------------------------
|
| Guard `customer`, Datenbankrolle `dormed_customer` mit RLS auf die eigene
| Firma (ADR-036), Frontend Inertia + Svelte.
|
| Ein Zugang fuer Portal UND Shop — eine Anwendung, eine Session (ADR-037).
|
*/

Route::middleware('guest:customer')->group(function (): void {
    Route::get('/login', fn () => app(SessionController::class)->create(request(), 'portal'))
        ->name('portal.login');
    Route::post('/login', [SessionController::class, 'store'])
        ->middleware('throttle:10,1')
        ->name('portal.login.store');
});

Route::post('/logout', [SessionController::class, 'destroy'])
    ->middleware('auth:customer')
    ->name('portal.logout');

/*
 * Die Kundenverbindung liegt bereits ueber der ganzen Domain-Gruppe
 * (bootstrap/app.php) — auch die Anmeldemaske laeuft darueber. Das ist
 * unschaedlich: sie fragt keine Fachdaten ab, und Sessions haengen ohnehin an
 * einer festen Verbindung (ADR-036).
 */
Route::middleware('auth:customer')->group(function (): void {
    Route::get('/', HomeController::class)->name('portal.home');
});
