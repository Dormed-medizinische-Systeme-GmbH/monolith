<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Shop Context Routes
|--------------------------------------------------------------------------
|
| Served on config('domains.shop'). Registered in bootstrap/app.php with the
| "web" middleware group and a "shop." route-name prefix. The subdomain is a
| routing boundary only; authorize every action on its own merits.
|
*/

Route::get('/', fn () => view('shop.home'))->name('home');
