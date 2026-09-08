<?php

namespace Tests\Feature\Crm;

use App\Models\User;
use App\Modules\Crm\Models\Company;
use App\Modules\Crm\Models\CompanyContact;
use App\Modules\Crm\Models\Person;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompanyContactTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_person_can_be_linked_to_a_company_as_a_contact(): void
    {
        $user = User::factory()->create();
        $company = Company::factory()->create();
        $person = Person::factory()->create();

        $this->actingAs($user)->post(route('crm.companies.contacts.store', $company), [
            'person_id' => $person->id,
            'role' => 'Einkauf',
            'is_primary' => '1',
        ])->assertRedirect(route('crm.companies.show', $company));

        $this->assertDatabaseHas('company_contacts', [
            'company_id' => $company->id,
            'person_id' => $person->id,
            'role' => 'Einkauf',
            'is_primary' => true,
        ]);
    }

    public function test_the_same_person_cannot_be_linked_twice(): void
    {
        $user = User::factory()->create();
        $contact = CompanyContact::factory()->create();

        $this->actingAs($user)->post(route('crm.companies.contacts.store', $contact->company), [
            'person_id' => $contact->person_id,
        ])->assertSessionHasErrors('person_id');

        $this->assertSame(1, CompanyContact::where('company_id', $contact->company_id)->count());
    }

    public function test_a_contact_can_be_removed(): void
    {
        $user = User::factory()->create();
        $contact = CompanyContact::factory()->create();

        $this->actingAs($user)->delete(route('crm.company-contacts.destroy', $contact))
            ->assertRedirect(route('crm.companies.show', $contact->company));

        $this->assertModelMissing($contact);
    }
}
