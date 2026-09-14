<?php

declare(strict_types=1);

use App\Http\Controllers\Portal\HomeController;
use App\Http\Middleware\UseCustomerConnection;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| my.dormed.de — Kundenportal (ADR-033)
|--------------------------------------------------------------------------
|
| Guard `customer`, Datenbankrolle `dormed_customer` mit RLS auf die eigene
| Firma (ADR-036), Frontend Inertia + Svelte.
|
*/

Route::middleware(UseCustomerConnection::class)->group(function (): void {
    Route::get('/', HomeController::class)->name('portal.home');
});
