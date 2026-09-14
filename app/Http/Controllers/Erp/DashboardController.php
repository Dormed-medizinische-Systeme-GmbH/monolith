<?php

declare(strict_types=1);

namespace App\Http\Controllers\Erp;

use App\Http\Controllers\Controller;
use App\Support\AccessPoint;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Die Wurzel des ERP IST das Dashboard.
 *
 * Es gibt bewusst keine zusaetzliche `/dashboard`-Route: zwei Adressen fuer
 * dieselbe Flaeche bedeuten zwei Ziele nach dem Login, zwei Eintraege in der
 * Historie und die Frage, welche davon die richtige ist. Alles aus der
 * Seitenleiste liegt darunter.
 *
 * Inhaltlich noch leer — das Cockpit ist je Abteilung definiert und entsteht
 * mit den Fachmodulen (D-126).
 */
final class DashboardController extends Controller
{
    public function __invoke(): Response
    {
        return Inertia::render('erp/Dashboard', [
            // Beweist im Browser und im Architekturtest, welche Postgres-Rolle
            // den Request bedient hat (ADR-036).
            'accessPoint' => AccessPoint::describe('erp', 'ERP', 'Inertia + Svelte'),
        ]);
    }
}
