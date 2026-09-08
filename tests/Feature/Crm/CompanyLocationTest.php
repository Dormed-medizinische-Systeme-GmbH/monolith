<?php

namespace Tests\Feature\Crm;

use App\Models\User;
use App\Modules\Crm\Models\Company;
use App\Modules\Crm\Models\Location;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompanyLocationTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_location_with_an_address_is_added_to_a_company(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create();

        $this->actingAs($user)->post(route('crm.companies.locations.store', $company), [
            'name' => 'Praxis Nord',
            'address' => [
                'street' => 'Nordstr',
                'postal_code' => '24103',
                'city' => 'Kiel',
            ],
        ])->assertRedirect(route('crm.companies.show', $company));

        $location = $company->locations()->firstWhere('name', 'Praxis Nord');
        $this->assertNotNull($location);
        $this->assertSame('Kiel', $location->address->city);
        $this->assertSame($user->id, $location->created_by);
    }

    public function test_a_location_is_updated_and_deleted(): void
    {
        $user = User::factory()->create();
        $location = Location::factory()->create(['name' => 'Alt']);

        $this->actingAs($user)->put(route('crm.locations.update', $location), [
            'name' => 'Neu',
        ])->assertRedirect(route('crm.companies.show', $location->company));

        $this->assertSame('Neu', $location->refresh()->name);

        $this->actingAs($user)->delete(route('crm.locations.destroy', $location))
            ->assertRedirect(route('crm.companies.show', $location->company));
        $this->assertModelMissing($location);
    }

    public function test_creating_a_location_requires_a_name(): void
    {
        $company = Company::factory()->create();

        $this->actingAs(User::factory()->create())
            ->post(route('crm.companies.locations.store', $company), ['name' => ''])
            ->assertSessionHasErrors('name');
    }
}
