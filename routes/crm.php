<?php

use App\Http\Controllers\Crm\CompanyContactController;
use App\Http\Controllers\Crm\CompanyController;
use App\Http\Controllers\Crm\CompanyLocationController;
use App\Http\Controllers\Crm\PersonController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| CRM Context Routes
|--------------------------------------------------------------------------
|
| Served on config('domains.crm'). Registered in bootstrap/app.php with the
| "web" middleware group and an "crm." route-name prefix. The subdomain is a
| routing boundary only; authorize every action on its own merits.
|
*/

Route::get('/', fn () => view('crm.home'))->name('home');

Route::middleware('auth')->group(function () {
    Route::resource('companies', CompanyController::class);
    Route::resource('people', PersonController::class)->parameters(['people' => 'person']);

    Route::post('companies/{company}/locations', [CompanyLocationController::class, 'store'])->name('companies.locations.store');
    Route::put('locations/{location}', [CompanyLocationController::class, 'update'])->name('locations.update');
    Route::delete('locations/{location}', [CompanyLocationController::class, 'destroy'])->name('locations.destroy');

    Route::post('companies/{company}/contacts', [CompanyContactController::class, 'store'])->name('companies.contacts.store');
    Route::delete('company-contacts/{companyContact}', [CompanyContactController::class, 'destroy'])->name('company-contacts.destroy');
});
