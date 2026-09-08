<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Application Context Domains
    |--------------------------------------------------------------------------
    |
    | Single source of truth for the subdomains served by this application.
    | Route groups in bootstrap/app.php bind to these hosts and the
    | ResolveApplicationContext middleware maps an incoming host back to its
    | App\Support\ApplicationContext. Values must not include a port; route
    | host matching compares against Request::getHost(), which omits it.
    |
    */

    'crm' => env('DOMAIN_CRM', 'crm.dormed.test'),

    'portal' => env('DOMAIN_PORTAL', 'portal.dormed.test'),

    'shop' => env('DOMAIN_SHOP', 'shop.dormed.test'),

];
