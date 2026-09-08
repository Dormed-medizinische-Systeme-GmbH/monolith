<?php

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
