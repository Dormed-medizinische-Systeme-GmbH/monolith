<?php

namespace Tests\Feature\Crm;

use App\Models\User;
use App\Modules\Crm\Models\Person;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PersonManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get(route('crm.people.index'))->assertRedirect(route('login'));
    }

    public function test_a_person_is_created_and_shown(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('crm.people.store'), [
            'first_name' => 'Hans',
            'last_name' => 'Beispiel',
            'email' => 'hans@example.test',
        ])->assertRedirect();

        $person = Person::firstWhere('last_name', 'Beispiel');
        $this->assertNotNull($person);
        $this->assertSame($user->id, $person->created_by);

        $this->actingAs($user)->get(route('crm.people.show', $person))
            ->assertOk()
            ->assertSee('Hans Beispiel');
    }

    public function test_creating_a_person_requires_first_and_last_name(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('crm.people.store'), ['email' => 'x@example.test'])
            ->assertSessionHasErrors(['first_name', 'last_name']);
    }

    public function test_an_invalid_email_is_rejected(): void
    {
        $this->actingAs(User::factory()->create())
            ->post(route('crm.people.store'), [
                'first_name' => 'A', 'last_name' => 'B', 'email' => 'not-an-email',
            ])
            ->assertSessionHasErrors('email');
    }

    public function test_a_person_is_updated_and_deleted(): void
    {
        $user = User::factory()->create();
        $person = Person::factory()->create();

        $this->actingAs($user)->put(route('crm.people.update', $person), [
            'first_name' => 'Neu',
            'last_name' => 'Name',
        ])->assertRedirect(route('crm.people.show', $person));

        $this->assertSame('Neu Name', $person->refresh()->full_name);

        $this->actingAs($user)->delete(route('crm.people.destroy', $person))
            ->assertRedirect(route('crm.people.index'));
        $this->assertModelMissing($person);
    }
}
