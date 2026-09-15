<?php

declare(strict_types=1);

use App\Http\Controllers\Erp\ArticleController;
use App\Http\Controllers\Erp\CompanyController;
use App\Http\Controllers\Erp\DashboardController;
use App\Http\Controllers\Erp\EmployeeController;
use App\Http\Controllers\Erp\SiteController;
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
    // `neu` steht VOR `{company}`, sonst wird das Wort als Schluessel aufgeloest.
    Route::get('/firmen/neu', [CompanyController::class, 'create'])->name('erp.companies.create');
    Route::post('/firmen', [CompanyController::class, 'store'])->name('erp.companies.store');
    Route::get('/firmen/{company}', [CompanyController::class, 'show'])->name('erp.companies.show');
    Route::get('/firmen/{company}/bearbeiten', [CompanyController::class, 'edit'])->name('erp.companies.edit');
    Route::patch('/firmen/{company}', [CompanyController::class, 'update'])->name('erp.companies.update');
    Route::delete('/firmen/{company}', [CompanyController::class, 'destroy'])->name('erp.companies.destroy');

    /*
     * Verschachtelt gebunden: `{contact}` wird ueber `$company->contacts()`
     * aufgeloest. Ein Kontakt einer anderen Firma liefert damit 404 statt ihn
     * unter einem fremden Pfad zu zeigen — die Firma ist der Rahmen, auch in
     * der Adresse.
     */
    Route::get('/firmen/{company}/kontakte/{contact}', [CompanyController::class, 'contact'])
        ->scopeBindings()
        ->name('erp.companies.contacts.show');

    /*
     * Standorte liegen UNTER der Firma, auch in der Adresse: ein Kundenstandort
     * kann ohne sie nicht existieren (`company_id` NOT NULL, D-007). Die
     * verschachtelte Bindung sorgt dafuer, dass sich kein Standort einer
     * fremden Praxis ueber diesen Pfad aendern laesst.
     *
     * Kein `create`/`edit`: die Maske ist ein Dialog in der Firmenakte, es gibt
     * also keine eigene Seite dafuer.
     */
    Route::post('/firmen/{company}/standorte', [CompanyController::class, 'storeLocation'])
        ->name('erp.companies.locations.store');
    Route::patch('/firmen/{company}/standorte/{location}', [CompanyController::class, 'updateLocation'])
        ->scopeBindings()
        ->name('erp.companies.locations.update');
    Route::delete('/firmen/{company}/standorte/{location}', [CompanyController::class, 'destroyLocation'])
        ->scopeBindings()
        ->name('erp.companies.locations.destroy');

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

    /*
     * Die EIGENEN Standorte von Dormed. Die der Kunden liegen unter der Firma
     * und heissen `locations` (D-007) — zwei Begriffe, die dasselbe Wort
     * benutzen, deshalb hier eine eigene Adresse.
     */
    Route::get('/betriebsstaetten', [SiteController::class, 'index'])->name('erp.sites.index');
    Route::get('/betriebsstaetten/neu', [SiteController::class, 'create'])->name('erp.sites.create');
    Route::post('/betriebsstaetten', [SiteController::class, 'store'])->name('erp.sites.store');
    Route::get('/betriebsstaetten/{site}', [SiteController::class, 'show'])->name('erp.sites.show');
    Route::get('/betriebsstaetten/{site}/bearbeiten', [SiteController::class, 'edit'])->name('erp.sites.edit');
    Route::patch('/betriebsstaetten/{site}', [SiteController::class, 'update'])->name('erp.sites.update');
    Route::delete('/betriebsstaetten/{site}', [SiteController::class, 'destroy'])->name('erp.sites.destroy');
});
