<?php

declare(strict_types=1);

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Anmeldung fuer Portal UND Shop — ein Zugang, eine Session (ADR-037).
 *
 * Bewusst von Hand statt ueber Fortify, abweichend von ADR-043: dort ist die
 * geteilte Fortify-Installation beschlossen, WEIL 2FA auf beiden Seiten gewollt
 * ist und der Challenge-Fluss der teure Teil waere. Solange Kunden-2FA nicht
 * gebaut ist, bliebe von Fortify hier nur Standardkram — und die dafuer noetige
 * Umschaltung von `fortify.guard` per Middleware ist globaler Zustand pro
 * Request.
 *
 * Wenn Kunden-2FA kommt, ist das hier der Ort, an dem auf die geteilte
 * Installation umgestellt wird.
 */
final class SessionController extends Controller
{
    public function create(Request $request, string $accessPoint): Response
    {
        return Inertia::render("{$accessPoint}/Login", [
            'status' => $request->session()->get('status'),
        ]);
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        return redirect()->intended('/');
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('customer')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}
