<?php

declare(strict_types=1);

use App\Modules\Core\Models\User;
use App\Modules\Crm\Models\Company;
use Database\Seeders\RoleSeeder;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function (): void {
    (new RoleSeeder)->run();

    $this->ich = User::factory()->create();
    $this->company = Company::query()->create(['name' => 'Praxis Alpha']);
});

function firmen(string $pfad = ''): string
{
    return 'http://'.config('domains.erp').'/firmen'.$pfad;
}

test('eine neue Firma bekommt automatisch einen Hauptstandort', function (): void {
    /*
     * Jede Company hat mindestens eine Location (D-007/D-078), und sie entsteht
     * mit einer Kopie der Sitzadresse. Ohne das haette eine frisch angelegte
     * Praxis keinen Ort, an dem ein Geraet stehen koennte.
     */
    $this->actingAs($this->ich, 'staff')
        ->post(firmen(), [
            'name' => 'Praxis Neu',
            'avv_status' => 'none',
            'street' => 'Hauptstraße',
            'house_number' => '3',
            'postal_code' => '21244',
            'city' => 'Buchholz',
        ])
        ->assertRedirect();

    $neu = Company::query()->where('name', 'Praxis Neu')->firstOrFail();
    $standort = $neu->locations()->firstOrFail();

    expect($standort->name)->toBe('Hauptstandort');
    expect($standort->is_primary)->toBeTrue();
    expect($standort->address->city)->toBe('Buchholz');
    expect($neu->address->street)->toBe('Hauptstraße');
});

test('die Zustaendigen lassen sich setzen', function (): void {
    $vertrieb = User::factory()->create();
    $service = User::factory()->create();

    $this->actingAs($this->ich, 'staff')
        ->patch(firmen('/'.$this->company->id), [
            'name' => 'Praxis Alpha',
            'avv_status' => 'none',
            'responsible_sales_id' => $vertrieb->id,
            'responsible_service_id' => $service->id,
        ])
        ->assertRedirect();

    $this->company->refresh();

    expect($this->company->responsible_sales_id)->toBe($vertrieb->id);
    expect($this->company->responsible_service_id)->toBe($service->id);
});

test('ein stillgelegter Mitarbeiter laesst sich nicht als zustaendig setzen', function (): void {
    // Ein stillgelegter Zugang gehoert nicht mehr ins Haus und wird aus den
    // `responsible_*`-Feldern ausgeblendet (IDENTITY_RBAC.md).
    $ausgeschieden = User::factory()->inactive()->create();

    $this->actingAs($this->ich, 'staff')
        ->patch(firmen('/'.$this->company->id), [
            'name' => 'Praxis Alpha',
            'avv_status' => 'none',
            'responsible_sales_id' => $ausgeschieden->id,
        ])
        ->assertSessionHasErrors('responsible_sales_id');
});

test('eine Firma kann nicht ihr eigener Rechnungsempfaenger sein', function (): void {
    // Sonst entstuende eine Kette ohne Ende (D-004/D-066).
    $this->actingAs($this->ich, 'staff')
        ->patch(firmen('/'.$this->company->id), [
            'name' => 'Praxis Alpha',
            'avv_status' => 'none',
            'billing_company_id' => $this->company->id,
        ])
        ->assertSessionHasErrors('billing_company_id');
});

test('die Maske bietet nur aktive Mitarbeiter und nicht die Firma selbst an', function (): void {
    User::factory()->inactive()->create();
    Company::query()->create(['name' => 'Praxis Beta']);

    $this->actingAs($this->ich, 'staff')
        ->get(firmen('/'.$this->company->id.'/bearbeiten'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('erp/companies/Form')
            ->where('company.fields.name', 'Praxis Alpha')
            // Nur der angemeldete Mitarbeiter ist aktiv.
            ->has('employees', 1)
            // „Praxis Alpha" selbst steht nicht zur Auswahl.
            ->has('companies', 1)
        );
});

test('eine Firma wird ausgeblendet, nicht entfernt', function (): void {
    $this->actingAs($this->ich, 'staff')
        ->delete(firmen('/'.$this->company->id))
        ->assertRedirect();

    expect(Company::query()->find($this->company->id))->toBeNull();
    expect(Company::withTrashed()->find($this->company->id))->not->toBeNull();
});
