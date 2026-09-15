<?php

declare(strict_types=1);

use App\Modules\Core\Models\User;
use App\Modules\Crm\Models\Company;
use App\Modules\Crm\Models\Location;
use Database\Seeders\RoleSeeder;

beforeEach(function (): void {
    (new RoleSeeder)->run();

    $this->ich = User::factory()->create();
    $this->company = Company::query()->create(['name' => 'Praxis Alpha']);
});

function standorte(Company $company, string $pfad = ''): string
{
    return 'http://'.config('domains.erp').'/firmen/'.$company->id.'/standorte'.$pfad;
}

test('der erste Standort wird automatisch zum Hauptstandort', function (): void {
    /*
     * Jede Firma hat mindestens einen Standort (D-007), und genau einen
     * Hauptstandort (CORE.md). Waere der erste keiner, entstuende eine Firma
     * ohne — ein Zustand, den die Spec ausschliesst.
     */
    $this->actingAs($this->ich, 'staff')
        ->post(standorte($this->company), [
            'name' => 'Hauptstandort',
            'is_primary' => false,
            'street' => 'Hauptstraße',
            'house_number' => '7',
            'postal_code' => '21244',
            'city' => 'Buchholz',
        ])
        ->assertRedirect();

    $location = $this->company->locations()->firstOrFail();

    expect($location->is_primary)->toBeTrue();
    expect($location->address->street)->toBe('Hauptstraße');
});

test('ein neuer Hauptstandort setzt den bisherigen ab', function (): void {
    $erster = $this->company->locations()->create(['name' => 'Alt', 'is_primary' => true]);

    $this->actingAs($this->ich, 'staff')
        ->post(standorte($this->company), ['name' => 'Neu', 'is_primary' => true])
        ->assertRedirect();

    expect($erster->fresh()->is_primary)->toBeFalse();
    expect($this->company->locations()->where('is_primary', true)->count())->toBe(1);
});

test('der Haken beim einzigen Hauptstandort laesst sich nicht entfernen', function (): void {
    // Umgesetzt wird er, indem man ihn woanders setzt — nicht, indem man ihn
    // hier wegnimmt.
    $haupt = $this->company->locations()->create(['name' => 'Haupt', 'is_primary' => true]);
    $this->company->locations()->create(['name' => 'Zweig', 'is_primary' => false]);

    $this->actingAs($this->ich, 'staff')
        ->patch(standorte($this->company, '/'.$haupt->id), [
            'name' => 'Haupt',
            'is_primary' => false,
        ])
        ->assertRedirect();

    expect($haupt->fresh()->is_primary)->toBeTrue();
});

test('der letzte Standort einer Firma wird nicht geloescht', function (): void {
    $einziger = $this->company->locations()->create(['name' => 'Einziger', 'is_primary' => true]);

    $this->actingAs($this->ich, 'staff')
        ->delete(standorte($this->company, '/'.$einziger->id))
        ->assertRedirect();

    expect(Location::query()->find($einziger->id))->not->toBeNull();
});

test('faellt der Hauptstandort weg, rueckt ein anderer nach', function (): void {
    $haupt = $this->company->locations()->create(['name' => 'Haupt', 'is_primary' => true]);
    $zweig = $this->company->locations()->create(['name' => 'Zweig', 'is_primary' => false]);

    $this->actingAs($this->ich, 'staff')
        ->delete(standorte($this->company, '/'.$haupt->id))
        ->assertRedirect();

    expect(Location::query()->find($haupt->id))->toBeNull();
    expect($zweig->fresh()->is_primary)->toBeTrue();
});

test('ein Standort einer fremden Firma ist ueber diesen Pfad nicht erreichbar', function (): void {
    /*
     * Die Firma kommt aus der Route und ist kein Formularfeld. Ohne die
     * verschachtelte Bindung liesse sich ein Standort in eine fremde Praxis
     * schieben.
     */
    $fremde = Company::query()->create(['name' => 'Praxis Beta']);
    $fremderStandort = $fremde->locations()->create(['name' => 'Fremd', 'is_primary' => true]);

    $this->actingAs($this->ich, 'staff')
        ->patch(standorte($this->company, '/'.$fremderStandort->id), [
            'name' => 'Gekapert',
            'is_primary' => false,
        ])
        ->assertNotFound();

    expect($fremderStandort->fresh()->name)->toBe('Fremd');
});

test('die Anschrift wird ersetzt und nicht verdoppelt', function (): void {
    // Genau eine Adresse je Standort (D-003/D-014, unique auf `addressable`).
    $location = $this->company->locations()->create(['name' => 'Haupt', 'is_primary' => true]);

    foreach (['Erste', 'Zweite'] as $strasse) {
        $this->actingAs($this->ich, 'staff')
            ->patch(standorte($this->company, '/'.$location->id), [
                'name' => 'Haupt',
                'is_primary' => true,
                'street' => $strasse,
                'postal_code' => '21244',
                'city' => 'Buchholz',
            ])
            ->assertRedirect();
    }

    expect($location->fresh()->address->street)->toBe('Zweite');
});
