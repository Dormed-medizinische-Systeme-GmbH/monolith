<?php

use Illuminate\Support\Facades\Route;

/*
 * Kein '/' hier: die Wurzel gehoert jedem Zugriffspunkt einzeln
 * (routes/{website,erp,portal,shop}.php, ADR-033). Eine Route ohne
 * Domain-Constraint wuerde auf ALLEN vier Hosts greifen und die
 * domainspezifischen Startseiten verdecken, weil sie frueher registriert ist.
 */

/*
 * Kein `verified`: die E-Mail-Verifizierung ist nach D-032 abgeschaltet, das
 * Middleware taeuschte sonst eine Pruefung vor, die nicht stattfindet.
 */
Route::middleware('auth')->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');
});

require __DIR__.'/settings.php';
