<?php

declare(strict_types=1);

use App\Http\Middleware\HandleInertiaRequests;
use App\Http\Middleware\UsePublicConnection;
use App\Http\Middleware\UseStaffConnection;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Middleware\AddLinkHeadersForPreloadedAssets;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        // Kein `web:` — es wuerde die Routen OHNE Domain-Bindung registrieren und
        // damit auf allen vier Hostnamen ausliefern. `routes/web.php` haengt
        // deshalb unten in der ERP-Gruppe (ADR-033/036).
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
        then: function (): void {
            /*
             * Vier Hostnames, eine Anwendung (ADR-033). Der Host entscheidet ueber
             * Routen, Frontend-Stack (ADR-039) und Datenbankrolle (ADR-036).
             *
             * Die Domains kommen aus `config/domains.php`, nie als Literal —
             * sonst weichen Dev, Staging und Produktion voneinander ab.
             *
             * Die Verbindungs-Middleware steht hier und nicht in den Route-Dateien,
             * damit sie garantiert die aeusserste der Gruppe ist. Was vorher
             * aufgeloest wird, laeuft noch auf der Standardverbindung.
             *
             * JEDER Zugriffspunkt benennt seine Rolle ausdruecklich — auch das ERP,
             * dessen Rolle zufaellig der Standard ist. Eine Sicherheitsgrenze darf
             * sich nicht darauf verlassen, was gerade der Standard ist.
             */
            Route::middleware(['web', UsePublicConnection::class])
                ->domain(config('domains.website'))
                ->group(base_path('routes/website.php'));

            Route::middleware(['web', UseStaffConnection::class])
                ->domain(config('domains.erp'))
                ->group(function (): void {
                    require base_path('routes/erp.php');

                    // Dashboard, Einstellungen und Profil sind Mitarbeiterflaechen.
                    // Ohne Domain-Bindung waeren sie auch unter dormed.de erreichbar,
                    // und zwar ohne Verbindungs-Middleware, also auf `dormed_staff`.
                    require base_path('routes/web.php');
                });

            Route::middleware('web')
                ->domain(config('domains.portal'))
                ->group(base_path('routes/portal.php'));

            Route::middleware(['web', UsePublicConnection::class])
                ->domain(config('domains.shop'))
                ->group(base_path('routes/shop.php'));
        },
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->encryptCookies(except: ['sidebar_state']);

        $middleware->web(append: [
            HandleInertiaRequests::class,
            AddLinkHeadersForPreloadedAssets::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
