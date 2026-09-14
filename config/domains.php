<?php

declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Zugriffspunkte (ADR-033/038)
|--------------------------------------------------------------------------
|
| Vier Hostnames, eine Anwendung. Der Host entscheidet ueber Routen,
| Frontend-Stack (ADR-039) und Datenbankverbindung (ADR-036).
|
| Nie als Literal in eine Route-Datei — sonst weichen Dev, Staging und
| Produktion voneinander ab. Siehe .ai/rules/routing.md.
|
*/

return [

    'website' => env('APP_DOMAIN_WEBSITE', 'dormed.test'),

    'erp' => env('APP_DOMAIN_ERP', 'erp.dormed.test'),

    'portal' => env('APP_DOMAIN_PORTAL', 'my.dormed.test'),

    'shop' => env('APP_DOMAIN_SHOP', 'shop.dormed.test'),

];
