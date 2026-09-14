<?php

declare(strict_types=1);

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

test('jeder Zugriffspunkt beantwortet seine eigene Wurzel', function (string $key): void {
    $this->get('http://'.config("domains.{$key}").'/')->assertOk();
})->with(['website', 'erp', 'portal', 'shop']);

test('die Website antwortet server-gerendert, nicht ueber Inertia', function (): void {
    $response = $this->get('http://'.config('domains.website').'/');

    $response->assertOk();
    $response->assertSee('<!DOCTYPE html>', false);
    expect($response->headers->get('X-Inertia'))->toBeNull();
});

test('ERP, Portal und Shop antworten ueber Inertia', function (string $key, string $component): void {
    $this->get('http://'.config("domains.{$key}").'/')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component($component));
})->with([
    ['erp', 'erp/Home'],
    ['portal', 'portal/Home'],
    ['shop', 'shop/Home'],
]);

test('der Host bestimmt die Datenbankrolle', function (string $key, string $connection): void {
    $expected = config("database.connections.{$connection}.username");

    $this->get('http://'.config("domains.{$key}").'/')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->where('accessPoint.role', $expected));
})->with([
    ['erp', 'pgsql'],
    ['portal', 'pgsql_customer'],
    ['shop', 'pgsql_public'],
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
