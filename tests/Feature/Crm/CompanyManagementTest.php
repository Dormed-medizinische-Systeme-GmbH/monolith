<?php

namespace Tests\Feature\Crm;

use App\Models\User;
use App\Modules\Crm\Models\Company;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompanyManagementTest extends TestCase
{
    use RefreshDatabase;

    private function actingUser(): User
    {
        return User::factory()->create();
    }

    public function test_index_lists_companies_for_an_authenticated_user(): void
    {
        $company = Company::factory()->create(['name' => 'Acme Diagnostics']);

        $this->actingAs($this->actingUser())
            ->get(route('crm.companies.index'))
            ->assertOk()
            ->assertSee('Acme Diagnostics');
    }

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get(route('crm.companies.index'))->assertRedirect(route('login'));
    }

    public function test_companies_route_is_not_available_on_other_context_hosts(): void
    {
        $this->actingAs($this->actingUser())
            ->get('http://portal.dormed.test/companies')
            ->assertNotFound();
    }

    public function test_a_company_is_created_with_its_address_and_blame(): void
    {
        $user = $this->actingUser();

        $response = $this->actingAs($user)->post(route('crm.companies.store'), [
            'name' => 'Nordklinik Service',
            'legal_name' => 'Nordklinik Service GmbH',
            'address' => [
                'street' => 'Hafenweg',
                'house_number' => '3',
                'postal_code' => '20095',
                'city' => 'Hamburg',
            ],
        ]);

        $company = Company::firstWhere('name', 'Nordklinik Service');

        $this->assertNotNull($company);
        $response->assertRedirect(route('crm.companies.show', $company));
        $this->assertSame($user->id, $company->created_by);
        $this->assertSame('Hamburg', $company->address->city);
        $this->assertSame('DE', $company->address->country_code);
    }

    public function test_creating_a_company_requires_a_name(): void
    {
        $this->actingAs($this->actingUser())
            ->post(route('crm.companies.store'), ['name' => ''])
            ->assertSessionHasErrors('name');

        $this->assertDatabaseCount('companies', 0);
    }

    public function test_a_partial_address_is_rejected(): void
    {
        $this->actingAs($this->actingUser())
            ->post(route('crm.companies.store'), [
                'name' => 'Partial Address Co',
                'address' => ['city' => 'Bremen'],
            ])
            ->assertSessionHasErrors(['address.street', 'address.postal_code']);

        $this->assertDatabaseCount('companies', 0);
    }

    public function test_a_company_is_updated_and_its_address_can_be_removed(): void
    {
        $user = $this->actingUser();
        $company = Company::factory()->create();
        $company->address()->create([
            'street' => 'Altweg', 'postal_code' => '10115', 'city' => 'Berlin',
        ]);

        $this->actingAs($user)->put(route('crm.companies.update', $company), [
            'name' => 'Renamed Co',
            'address' => ['street' => '', 'postal_code' => '', 'city' => ''],
        ])->assertRedirect(route('crm.companies.show', $company));

        $company->refresh();
        $this->assertSame('Renamed Co', $company->name);
        $this->assertNull($company->address);
        $this->assertSame($user->id, $company->updated_by);
    }

    public function test_a_company_is_deleted(): void
    {
        $company = Company::factory()->create();

        $this->actingAs($this->actingUser())
            ->delete(route('crm.companies.destroy', $company))
            ->assertRedirect(route('crm.companies.index'));

        $this->assertModelMissing($company);
    }
}
