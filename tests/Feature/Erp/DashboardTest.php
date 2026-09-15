<?php

declare(strict_types=1);

use App\Modules\Core\Models\Employee;
use Database\Seeders\RoleSeeder;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function (): void {
    (new RoleSeeder)->run();
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
