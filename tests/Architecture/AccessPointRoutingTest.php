<?php

declare(strict_types=1);

use App\Modules\Core\Models\User;
use App\Modules\Crm\Models\CustomerAccount;
use App\Modules\Crm\Models\Person;
use Database\Seeders\RoleSeeder;
use Inertia\Testing\AssertableInertia as Assert;

/*
|--------------------------------------------------------------------------
| Beweis fuer das Domain-Routing aus ADR-033/038/039
|--------------------------------------------------------------------------
|
| Vier Hostnames, eine Anwendung. Der Host entscheidet ueber Routen,
| Frontend-Stack und Datenbankrolle.
|
*/

beforeEach(function (): void {
    requirePostgres();
});

/**
 * Meldet an, was der Zugriffspunkt verlangt. Portal und Shop liegen hinter
 * einem Gate (ADR-037) — ohne Anmeldung antworten sie mit 302 auf ihre
 * Login-Maske, und genau das prueft der letzte Test dieser Datei.
 */
function actingAtAccessPoint(object $test, string $key): object
{
    if ($key === 'erp') {
        (new RoleSeeder)->run();

        return $test->actingAs(User::factory()->create(), 'staff');
    }

    if (in_array($key, ['portal', 'shop'], true)) {
        $person = Person::query()->create([
            'first_name' => 'Test',
            'last_name' => 'Kunde',
            'gender' => 'unbekannt',
        ]);

        return $test->actingAs(CustomerAccount::query()->create([
            'person_id' => $person->id,
            'email' => 'kunde-'.bin2hex(random_bytes(4)).'@example.test',
            'password' => 'password',
        ]), 'customer');
    }

    return $test;
}

test('jeder Zugriffspunkt beantwortet seine eigene Wurzel', function (string $key): void {
    actingAtAccessPoint($this, $key)
        ->get('http://'.config("domains.{$key}").'/')
        ->assertOk();
})->with(['website', 'erp', 'portal', 'shop']);

test('Portal und Shop liegen hinter einem Gate', function (string $key): void {
    $this->get('http://'.config("domains.{$key}").'/')
        ->assertRedirect('http://'.config("domains.{$key}").'/login');
})->with(['portal', 'shop']);

test('die Anmeldemaske ist ohne Anmeldung erreichbar', function (string $key): void {
    $this->get('http://'.config("domains.{$key}").'/login')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component("{$key}/Login"));
})->with(['portal', 'shop']);

test('die Website antwortet server-gerendert, nicht ueber Inertia', function (): void {
    $response = $this->get('http://'.config('domains.website').'/');

    $response->assertOk();
    $response->assertSee('<!DOCTYPE html>', false);
    expect($response->headers->get('X-Inertia'))->toBeNull();
});

test('ERP, Portal und Shop antworten ueber Inertia', function (string $key, string $component): void {
    actingAtAccessPoint($this, $key)
        ->get('http://'.config("domains.{$key}").'/')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component($component));
})->with([
    ['erp', 'erp/Home'],
    ['portal', 'portal/Home'],
    ['shop', 'shop/Home'],
]);

test('der Host bestimmt die Datenbankrolle', function (string $key, string $connection): void {
    $expected = config("database.connections.{$connection}.username");

    actingAtAccessPoint($this, $key)
        ->get('http://'.config("domains.{$key}").'/')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->where('accessPoint.role', $expected));
})->with([
    ['erp', 'pgsql'],
    ['portal', 'pgsql_customer'],
    // Die Shop-Wurzel liegt hinter dem Gate und laeuft deshalb ueber die
    // Kundenrolle. Der anonyme Katalog kommt spaeter und wird `pgsql_public`
    // benutzen — dann gehoert hier eine zweite Zeile hin (ADR-036/038).
    ['shop', 'pgsql_customer'],
]);

test('die Website laeuft ueber die oeffentliche Rolle', function (): void {
    $this->getJson('http://'.config('domains.website').'/__access-point')
        ->assertOk()
        ->assertJsonPath('connection', 'pgsql_public')
        ->assertJsonPath('role', config('database.connections.pgsql_public.username'));
});

test('ein unbekannter Host trifft keine Route', function (): void {
    $this->get('http://fremd.example/')->assertNotFound();
});
