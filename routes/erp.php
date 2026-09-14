<?php

declare(strict_types=1);

use App\Http\Controllers\Erp\CompanyController;
use App\Http\Controllers\Erp\DashboardController;
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
    // Die Wurzel ist das Dashboard. Keine zweite `/dashboard`-Adresse daneben.
    Route::get('/', DashboardController::class)->name('erp.dashboard');

    /*
     * Vorerst nur Ansicht. Anlegen und Bearbeiten kommen mit dem ersten
     * vertikalen Slice (IMPLEMENTATION_SEQUENCE Phase 5).
     *
     * Der Kundenstamm wird ueber die FIRMA betreten, nicht ueber die Person:
     * eine Person gibt es fachlich nie eigenstaendig (D-002), und ihre Rolle
     * gilt immer gegenueber einer bestimmten Firma (D-005).
     */
    Route::get('/firmen', [CompanyController::class, 'index'])->name('erp.companies.index');
    Route::get('/firmen/{company}', [CompanyController::class, 'show'])->name('erp.companies.show');

    /*
     * Verschachtelt gebunden: `{contact}` wird ueber `$company->contacts()`
     * aufgeloest. Ein Kontakt einer anderen Firma liefert damit 404 statt ihn
     * unter einem fremden Pfad zu zeigen — die Firma ist der Rahmen, auch in
     * der Adresse.
     */
    Route::get('/firmen/{company}/kontakte/{contact}', [CompanyController::class, 'contact'])
        ->scopeBindings()
        ->name('erp.companies.contacts.show');
});
