<?php

declare(strict_types=1);

use App\Modules\Core\Models\Employee;
use Carbon\Carbon;
use Database\Seeders\RoleSeeder;
use Database\Seeders\SiteSeeder;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function (): void {
    (new RoleSeeder)->run();
    (new SiteSeeder)->run();
});

test('der Kalender verlangt eine Anmeldung', function (): void {
    $this->get('http://'.config('domains.erp').'/kalender')
        ->assertRedirect('http://'.config('domains.erp').'/login');
});

test('er liefert Platzhaltertermine rund um heute', function (): void {
    /*
     * Die Termine haengen an HEUTE statt an festen Datumsangaben — ein Entwurf,
     * der naechste Woche leer aussieht, laesst sich nicht beurteilen.
     */
    $this->actingAs(Employee::factory()->create(), 'staff')
        ->get('http://'.config('domains.erp').'/kalender')
        ->assertOk()
        ->assertInertia(function (Assert $page): void {
            $page->component('erp/Calendar');

            $events = collect($page->toArray()['props']['events']);

            expect($events)->not->toBeEmpty();

            $dieseWoche = $events->filter(fn (array $e): bool => Carbon::parse($e['start'])
                ->between(now()->startOfWeek(), now()->endOfWeek()));

            expect($dieseWoche)->not->toBeEmpty();

            // Mehrtaegige sind der Grund, warum eine Monatszelle Positionen
            // vergeben muss und nicht einfach stapelt.
            expect($events->where('allDay', true))->not->toBeEmpty();
        });
});
