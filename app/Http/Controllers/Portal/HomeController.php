<?php

declare(strict_types=1);

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\Controller;
use App\Support\AccessPoint;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Startseite von Kundenportal. Inertia + Svelte (ADR-039).
 */
final class HomeController extends Controller
{
    public function __invoke(): Response
    {
        return Inertia::render('portal/Home', [
            'accessPoint' => AccessPoint::describe('portal', 'Kundenportal', 'Inertia + Svelte'),
        ]);
    }
}
