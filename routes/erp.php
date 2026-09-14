<?php

declare(strict_types=1);

use App\Http\Controllers\Erp\HomeController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| erp.dormed.de — Mitarbeiter (ADR-031/033)
|--------------------------------------------------------------------------
|
| Guard `staff`, Datenbankrolle `dormed_staff` (Standardverbindung),
| Frontend Inertia + Svelte.
|
*/

/*
 * Hinter dem Gate. Der Login selbst kommt von Fortify und ist in
 * bootstrap/app.php registriert — er liegt ausserhalb dieser Gruppe.
 */
Route::middleware('auth:staff')->group(function (): void {
    Route::get('/', HomeController::class)->name('erp.home');
});
