<?php

declare(strict_types=1);

use App\Http\Controllers\Erp\ArticleController;
use App\Http\Controllers\Erp\CompanyController;
use App\Http\Controllers\Erp\DashboardController;
use App\Http\Controllers\Erp\EmployeeController;
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

    // Katalog, kein Bestand — der kommt mit dem Ledger (D-102).
    Route::get('/artikel', [ArticleController::class, 'index'])->name('erp.articles.index');
    Route::get('/artikel/{article}', [ArticleController::class, 'show'])->name('erp.articles.show');

    /*
     * Mitarbeiter — die erste Flaeche mit Schreibzugriff. Angelegt wird
     * ausschliesslich von hier: es gibt keine Registrierung und keinen
     * Self-Service (D-032).
     *
     * `neu` steht VOR `{user}`, sonst versuchte Laravel, das Wort als
     * Schluessel aufzuloesen.
     */
    Route::get('/mitarbeiter', [EmployeeController::class, 'index'])->name('erp.employees.index');
    Route::get('/mitarbeiter/neu', [EmployeeController::class, 'create'])->name('erp.employees.create');
    Route::post('/mitarbeiter', [EmployeeController::class, 'store'])->name('erp.employees.store');
    Route::get('/mitarbeiter/{user}', [EmployeeController::class, 'show'])->name('erp.employees.show');
    Route::get('/mitarbeiter/{user}/bearbeiten', [EmployeeController::class, 'edit'])->name('erp.employees.edit');
    Route::patch('/mitarbeiter/{user}', [EmployeeController::class, 'update'])->name('erp.employees.update');
    Route::delete('/mitarbeiter/{user}', [EmployeeController::class, 'destroy'])->name('erp.employees.destroy');
});
