<?php

use App\Http\Middleware\ResolveApplicationContext;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function (): void {
            foreach (['crm', 'portal', 'shop'] as $context) {
                Route::middleware('web')
                    ->domain(config("domains.{$context}"))
                    ->as("{$context}.")
                    ->group(base_path("routes/{$context}.php"));
            }

            // Fallback for unknown hosts; registered after the context groups so
            // it never shadows a context's own "/" route.
            Route::view('/', 'welcome')->middleware('web')->name('welcome');
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Behind Coolify's Traefik proxy: honour X-Forwarded-* so the app knows
        // the request is HTTPS (correct scheme in redirects, secure cookies).
        $middleware->trustProxies(at: '*');

        $middleware->web(append: [
            ResolveApplicationContext::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
