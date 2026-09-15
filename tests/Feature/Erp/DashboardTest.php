<?php

declare(strict_types=1);

use App\Modules\Core\Models\Employee;
use Database\Seeders\RoleSeeder;
use Database\Seeders\SiteSeeder;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function (): void {
    (new RoleSeeder)->run();
    (new SiteSeeder)->run();
});

test('die ERP-Wurzel verlangt eine Anmeldung', function (): void {
    $this->get('http://'.config('domains.erp').'/')
        ->assertRedirect('http://'.config('domains.erp').'/login');
});

test('die ERP-Wurzel IST das Dashboard', function (): void {
    /*
     * Es gibt bewusst keine zweite `/dashboard`-Adresse daneben: zwei Wege auf
     * dieselbe Flaeche heissen zwei Ziele nach dem Login und zwei Eintraege in
     * der Historie.
     */
    $this->actingAs(Employee::factory()->create(), 'staff')
        ->get('http://'.config('domains.erp').'/')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->component('erp/Dashboard'));
});

test('es gibt keine /dashboard-Route mehr', function (): void {
    $this->actingAs(Employee::factory()->create(), 'staff')
        ->get('http://'.config('domains.erp').'/dashboard')
        ->assertNotFound();
});

test('die Auswahl oben rechts bekommt die aktiven Mitarbeiter', function (): void {
    /*
     * Noch ohne Wirkung — sie wechselt nichts. Sobald das Cockpit steht
     * (D-126), entscheidet sie, wessen Zahlen es zeigt.
     */
    Employee::factory()->create(['last_name' => 'Aktiv']);
    Employee::factory()->inactive()->create(['last_name' => 'Ausgeschieden']);

    $this->actingAs(Employee::factory()->create(), 'staff')
        ->get('http://'.config('domains.erp').'/')
        ->assertInertia(function (Assert $page): void {
            $namen = collect($page->toArray()['props']['employees'])->pluck('name');

            // `name` setzt sich aus Vor- und Nachname zusammen (D-093).
            expect($namen->filter(fn (string $n): bool => str_ends_with($n, 'Aktiv')))
                ->not->toBeEmpty();
            expect($namen->filter(fn (string $n): bool => str_contains($n, 'Ausgeschieden')))
                ->toBeEmpty();
        });
});
