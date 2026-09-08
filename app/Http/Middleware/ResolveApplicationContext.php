<?php

namespace App\Http\Middleware;

use App\Support\ApplicationContext;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

/**
 * Resolves the application context from the request host early in the pipeline
 * and makes it available to the container and every view as $applicationContext.
 *
 * An unknown host (e.g. a bare domain or a preview URL) resolves to null; that
 * is not an error, only the shared fallback routes are reachable there.
 */
class ResolveApplicationContext
{
    /**
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $context = ApplicationContext::tryFromHost($request->getHost());

        if ($context instanceof ApplicationContext) {
            app()->instance(ApplicationContext::class, $context);
        }

        View::share('applicationContext', $context);

        return $next($request);
    }
}
