<?php

declare(strict_types=1);

use App\Http\Controllers\Shop\HomeController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| shop.dormed.de — E-Commerce (ADR-033)
|--------------------------------------------------------------------------
|
| Anonymer Katalog ueber `dormed_public`, nach dem Login `dormed_customer`
| (ADR-036). Frontend Inertia + Svelte.
|
| Die Umschaltung haengt hier am Login, nicht an der Domain — deshalb steht
| die Middleware auf den jeweiligen Untergruppen, nicht ueber der ganzen Datei.
|
*/

Route::get('/', HomeController::class)->name('shop.home');
