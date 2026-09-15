<?php

declare(strict_types=1);
use App\Modules\Core\Models\Employee;
use App\Modules\Crm\Models\CustomerAccount;

return [

    /*
    |--------------------------------------------------------------------------
    | Zwei Guards, zwei Tabellen, zwei Models (ADR-042)
    |--------------------------------------------------------------------------
    |
    | Der Standard heisst `staff`, NICHT `web`: ein `auth()` ohne Argument soll
    | nicht stillschweigend auf die Mitarbeiterseite fallen, sondern auffallen.
    |
    | Geteilt wird die Mechanik, nie die Identitaet. Ein Kunde kann in einer
    | Mitarbeiterabfrage strukturell nicht vorkommen, weil er nicht in derselben
    | Tabelle steht.
    |
    */

    'defaults' => [
        'guard' => env('AUTH_GUARD', 'staff'),
        'passwords' => env('AUTH_PASSWORD_BROKER', 'staff'),
    ],

    'guards' => [
        'staff' => [
            'driver' => 'session',
            'provider' => 'staff',
        ],

        // Portal und Shop teilen sich EINEN Kundenzugang — eine Anwendung, eine
        // Session (ADR-037). Der Cross-App-Mechanismus aus ADR-016 entfaellt.
        'customer' => [
            'driver' => 'session',
            'provider' => 'customers',
        ],
    ],

    'providers' => [
        'staff' => [
            'driver' => 'eloquent',
            'model' => Employee::class,
        ],

        'customers' => [
            'driver' => 'eloquent',
            'model' => CustomerAccount::class,
        ],
    ],

    /*
    | Zwei Broker mit je eigener Token-Tabelle. In Laravel nativ vorgesehen,
    | kein Kunstgriff (ADR-043).
    |
    | Fuer `staff` ist der Self-Service-Reset nach D-032 abgeschaltet — der
    | Broker existiert trotzdem, weil ein Mitarbeiter sein Passwort in den
    | Einstellungen aendern kann. Kunden brauchen den Reset dagegen zwingend.
    */
    'passwords' => [
        'staff' => [
            'provider' => 'staff',
            'table' => 'employee_password_reset_tokens',
            'expire' => 60,
            'throttle' => 60,
        ],

        'customers' => [
            'provider' => 'customers',
            'table' => 'customer_password_reset_tokens',
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    'password_timeout' => env('AUTH_PASSWORD_TIMEOUT', 10800),

];
