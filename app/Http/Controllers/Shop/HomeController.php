<?php

declare(strict_types=1);

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Support\AccessPoint;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Startseite von Shop. Inertia + Svelte (ADR-039).
 */
final class HomeController extends Controller
{
    public function __invoke(): Response
    {
        return Inertia::render('shop/Home', [
            'accessPoint' => AccessPoint::describe('shop', 'Shop', 'Inertia + Svelte'),
        ]);
    }
}
