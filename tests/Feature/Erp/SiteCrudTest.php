<?php

declare(strict_types=1);

use App\Modules\Core\Models\Employee;
use App\Modules\Core\Models\Site;
use Database\Seeders\RoleSeeder;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function (): void {
    (new RoleSeeder)->run();

    /*
     * Der angemeldete Mitarbeiter sitzt anderswo: `site_id` ist NOT NULL, und
     * die Fabrik nimmt den ERSTEN vorhandenen Standort. Ohne diese Trennung
     * waere der gepruefte Standort nie leer — und „loeschen nur, was leer ist"
     * liesse sich nicht pruefen.
     */
    $this->anderswo = Site::query()->create(['name' => 'Anderswo']);
    $this->ich = Employee::factory()->create(['site_id' => $this->anderswo->id]);

    $this->standort = Site::query()->create([
        'name' => 'Buchholz',
        'street' => 'Musterstraße 1',
        'postal_code' => '21244',
        'city' => 'Buchholz in der Nordheide',
    ]);
});

function betrieb(string $pfad = ''): string
{
    return 'http://'.config('domains.erp').'/betriebsstaetten'.$pfad;
}

test('die Liste verlangt eine Anmeldung', function (): void {
    $this->get(betrieb())->assertRedirect('http://'.config('domains.erp').'/login');
});

test('sie zeigt Standort, Anschrift und die Zahl der Mitarbeiter', function (): void {
    Employee::factory()->count(2)->create(['site_id' => $this->standort->id]);

    $this->actingAs($this->ich, 'staff')->get(betrieb())
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('erp/sites/Index')
            ->where('rows.0.name', 'Anderswo')
            ->where('rows.1.name', 'Buchholz')
            ->where('rows.1.addressLine', 'Musterstraße 1, 21244 Buchholz in der Nordheide')
            // Gezaehlt, nicht gejoint — sonst waeren aus einem Standort mit zwei
            // Mitarbeitern zwei Zeilen geworden.
            ->where('rows.1.employeeCount', 2)
            ->has('rows', 2)
        );
});

test('ein Standort laesst sich anlegen', function (): void {
    $this->actingAs($this->ich, 'staff')
        ->post(betrieb(), [
            'name' => 'Holzwickede',
            'street' => 'Musterweg 2',
            'postal_code' => '59439',
            'city' => 'Holzwickede',
        ])
        ->assertRedirect();

    expect(Site::query()->where('name', 'Holzwickede')->exists())->toBeTrue();
});

test('ein doppelter Name wird abgewiesen', function (): void {
    $this->actingAs($this->ich, 'staff')
        ->post(betrieb(), ['name' => 'Buchholz'])
        ->assertSessionHasErrors('name');
});

test('ein Standort ohne Anschrift ist zulaessig', function (): void {
    // Ein Standort entsteht manchmal, bevor die Adresse feststeht.
    $this->actingAs($this->ich, 'staff')
        ->post(betrieb(), ['name' => 'Noch ohne Adresse'])
        ->assertRedirect()
        ->assertSessionHasNoErrors();
});

test('ein Standort mit Mitarbeitern wird nicht geloescht', function (): void {
    /*
     * Der Fremdschluessel ist `nullOnDelete`, das greift aber nur beim HARTEN
     * Loeschen. `SoftDeletes` (D-018) liesse `users.site_id` stehen, und die
     * Mitarbeiter zeigten auf einen ausgeblendeten Standort — in der Liste
     * sichtbar, in der Auswahl verschwunden.
     */
    Employee::factory()->create(['site_id' => $this->standort->id]);

    $this->actingAs($this->ich, 'staff')
        ->delete(betrieb('/'.$this->standort->id))
        ->assertRedirect();

    expect(Site::query()->find($this->standort->id))->not->toBeNull();
});

test('ein leerer Standort wird ausgeblendet, nicht entfernt', function (): void {
    $this->actingAs($this->ich, 'staff')
        ->delete(betrieb('/'.$this->standort->id))
        ->assertRedirect();

    expect(Site::query()->find($this->standort->id))->toBeNull();
    expect(Site::withTrashed()->find($this->standort->id))->not->toBeNull();
});

test('die Detailansicht fuehrt auf, wer dort sitzt', function (): void {
    Employee::factory()->create(['site_id' => $this->standort->id, 'last_name' => 'Draheim']);

    $this->actingAs($this->ich, 'staff')
        ->get(betrieb('/'.$this->standort->id))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('erp/sites/Show')
            ->has('site.employees', 1)
            ->where('site.address.line', 'Musterstraße 1, 21244 Buchholz in der Nordheide')
        );
});
