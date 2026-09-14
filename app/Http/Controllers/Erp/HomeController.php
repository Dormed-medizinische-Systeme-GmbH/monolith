<?php

declare(strict_types=1);

namespace App\Http\Controllers\Erp;

use App\Http\Controllers\Controller;
use App\Support\AccessPoint;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Startseite von ERP. Inertia + Svelte (ADR-039).
 */
final class HomeController extends Controller
{
    public function __invoke(): Response
    {
        return Inertia::render('erp/Home', [
            'accessPoint' => AccessPoint::describe('erp', 'ERP', 'Inertia + Svelte'),
        ]);
    }
}
