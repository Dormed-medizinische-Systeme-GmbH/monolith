<?php

declare(strict_types=1);

use App\Http\Controllers\Customer\SessionController;
use App\Http\Controllers\Shop\HomeController;
use App\Http\Middleware\UseCustomerConnection;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| shop.dormed.de — E-Commerce (ADR-033)
|--------------------------------------------------------------------------
|
| Der Katalog ist spaeter anonym und laeuft ueber `dormed_public`; angemeldete
| Flaechen laufen ueber `dormed_customer` (ADR-036). Deshalb steht die
| Umschaltung hier auf den Untergruppen und nicht ueber der ganzen Datei.
|
| Derselbe Zugang wie im Portal — eine Anwendung, eine Session (ADR-037).
|
*/

Route::middleware('guest:customer')->group(function (): void {
    Route::get('/login', fn () => app(SessionController::class)->create(request(), 'shop'))
        ->name('shop.login');
    Route::post('/login', [SessionController::class, 'store'])
        ->middleware('throttle:10,1')
        ->name('shop.login.store');
});

Route::post('/logout', [SessionController::class, 'destroy'])
    ->middleware('auth:customer')
    ->name('shop.logout');

Route::middleware(['auth:customer', UseCustomerConnection::class])->group(function (): void {
    Route::get('/', HomeController::class)->name('shop.home');
});
