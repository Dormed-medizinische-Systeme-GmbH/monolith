<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Customer Portal Context Routes
|--------------------------------------------------------------------------
|
| Served on config('domains.portal'). Registered in bootstrap/app.php with the
| "web" middleware group and a "portal." route-name prefix. The subdomain is a
| routing boundary only; authorize every action on its own merits.
|
*/

Route::get('/', fn () => view('portal.home'))->name('home');
