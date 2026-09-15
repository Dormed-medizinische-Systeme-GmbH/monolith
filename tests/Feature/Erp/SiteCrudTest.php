<?php

declare(strict_types=1);

use App\Modules\Core\Models\Site;
use App\Modules\Core\Models\User;
use Database\Seeders\RoleSeeder;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function (): void {
    (new RoleSeeder)->run();

    $this->ich = User::factory()->create();
    $this->standort = Site::query()->create([
        'name' => 'Buchholz',
        'short_name' => 'BUC',
        'street' => 'Musterstraße',
        'house_number' => '1',
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
    User::factory()->count(2)->create(['site_id' => $this->standort->id]);

    $this->actingAs($this->ich, 'staff')->get(betrieb())
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('erp/sites/Index')
            ->where('rows.0.name', 'Buchholz')
            ->where('rows.0.addressLine', 'Musterstraße 1, 21244 Buchholz in der Nordheide')
            // Gezaehlt, nicht gejoint — sonst waeren aus einem Standort mit zwei
            // Mitarbeitern zwei Zeilen geworden.
            ->where('rows.0.userCount', 2)
            ->has('rows', 1)
        );
});

test('ein Standort laesst sich anlegen', function (): void {
    $this->actingAs($this->ich, 'staff')
        ->post(betrieb(), [
            'name' => 'Holzwickede',
            'short_name' => 'HOL',
            'postal_code' => '59439',
            'city' => 'Holzwickede',
            'is_active' => true,
        ])
        ->assertRedirect();

    expect(Site::query()->where('name', 'Holzwickede')->exists())->toBeTrue();
});

test('ein doppelter Name wird abgewiesen', function (): void {
    $this->actingAs($this->ich, 'staff')
        ->post(betrieb(), ['name' => 'Buchholz', 'is_active' => true])
        ->assertSessionHasErrors('name');
});

test('ein Standort ohne Anschrift ist zulaessig', function (): void {
    // Ein Standort entsteht manchmal, bevor die Adresse feststeht.
    $this->actingAs($this->ich, 'staff')
        ->post(betrieb(), ['name' => 'Noch ohne Adresse', 'is_active' => true])
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
    User::factory()->create(['site_id' => $this->standort->id]);

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
    User::factory()->create(['site_id' => $this->standort->id, 'last_name' => 'Draheim']);

    $this->actingAs($this->ich, 'staff')
        ->get(betrieb('/'.$this->standort->id))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('erp/sites/Show')
            ->has('site.users', 1)
            ->where('site.address.line', 'Musterstraße 1, 21244 Buchholz in der Nordheide')
        );
});
