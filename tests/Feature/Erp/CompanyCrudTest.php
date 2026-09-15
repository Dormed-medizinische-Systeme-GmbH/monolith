<?php

declare(strict_types=1);

use App\Modules\Core\Models\Role;
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
    $vertrieb = User::factory()->role('sales')->create();
    $service = User::factory()->role('service')->create();

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
    $ausgeschieden = User::factory()->role('sales')->inactive()->create();

    $this->actingAs($this->ich, 'staff')
        ->patch(firmen('/'.$this->company->id), [
            'name' => 'Praxis Alpha',
            'avv_status' => 'none',
            'responsible_sales_id' => $ausgeschieden->id,
        ])
        ->assertSessionHasErrors('responsible_sales_id');
});

test('nur die eigene Abteilung steht zur Auswahl', function (): void {
    /*
     * Die Rolle ist die einzige Quelle dafuer, wer wozu gehoert (D-124). Wer im
     * Service sitzt, taucht im Vertriebsfeld nicht auf — und umgekehrt.
     */
    $techniker = User::factory()->role('service')->create();

    $this->actingAs($this->ich, 'staff')
        ->patch(firmen('/'.$this->company->id), [
            'name' => 'Praxis Alpha',
            'avv_status' => 'none',
            'responsible_sales_id' => $techniker->id,
        ])
        ->assertSessionHasErrors('responsible_sales_id');
});

test('der bisherige Zustaendige bleibt waehlbar, auch nach Abteilungswechsel', function (): void {
    /*
     * Sonst schluege jedes Speichern fehl, solange niemand den Nachfolger
     * benannt hat — man koennte an der Firma nicht einmal die Anschrift
     * aendern, ohne zuerst eine Personalfrage zu klaeren.
     */
    $vertrieb = User::factory()->role('sales')->create();
    $this->company->update(['responsible_sales_id' => $vertrieb->id]);

    // Wechselt in den Service.
    $vertrieb->update(['role_id' => Role::query()->where('key', 'service')->value('id')]);

    $this->actingAs($this->ich, 'staff')
        ->get(firmen('/'.$this->company->id.'/bearbeiten'))
        ->assertInertia(fn (Assert $page) => $page
            ->has('salesEmployees', 1)
            // Gekennzeichnet statt versteckt.
            ->where('salesEmployees.0.foreign', true)
        );

    $this->actingAs($this->ich, 'staff')
        ->patch(firmen('/'.$this->company->id), [
            'name' => 'Praxis Umbenannt',
            'avv_status' => 'none',
            'responsible_sales_id' => $vertrieb->id,
        ])
        ->assertSessionHasNoErrors();

    expect($this->company->fresh()->name)->toBe('Praxis Umbenannt');
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

test('die Maske bietet nicht die Firma selbst als Rechnungsempfaenger an', function (): void {
    User::factory()->inactive()->create();
    Company::query()->create(['name' => 'Praxis Beta']);

    $this->actingAs($this->ich, 'staff')
        ->get(firmen('/'.$this->company->id.'/bearbeiten'))
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('erp/companies/Form')
            ->where('company.fields.name', 'Praxis Alpha')
            // Der angemeldete Mitarbeiter gehoert keiner der beiden
            // Abteilungen an — beide Listen sind leer.
            ->has('salesEmployees', 0)
            ->has('serviceEmployees', 0)
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
