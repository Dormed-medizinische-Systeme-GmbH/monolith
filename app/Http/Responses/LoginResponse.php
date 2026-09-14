<?php

declare(strict_types=1);

namespace App\Http\Responses;

use App\Support\AccessPointRedirect;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

/**
 * Ersetzt Fortifys Standardantwort, die `redirect()->intended()` blind folgt.
 *
 * Bei vier Zugriffspunkten auf einer geteilten Session zeigt ein gemerktes Ziel
 * moeglicherweise auf einen anderen Hostnamen — siehe AccessPointRedirect.
 */
final class LoginResponse implements LoginResponseContract
{
    public function toResponse($request): RedirectResponse
    {
        /** @var Request $request */
        return redirect()->to(
            AccessPointRedirect::after($request, config('fortify.home'))
        );
    }
}
