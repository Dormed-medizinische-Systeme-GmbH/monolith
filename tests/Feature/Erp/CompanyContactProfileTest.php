<?php

declare(strict_types=1);

use App\Modules\Core\Models\User;
use App\Modules\Crm\Enums\ChannelLabel;
use App\Modules\Crm\Enums\ChannelType;
use App\Modules\Crm\Enums\ConsentChannel;
use App\Modules\Crm\Enums\ConsentSource;
use App\Modules\Crm\Enums\ConsentStatus;
use App\Modules\Crm\Enums\Gender;
use App\Modules\Crm\Models\Company;
use App\Modules\Crm\Models\CompanyContact;
use App\Modules\Crm\Models\CustomerAccount;
use App\Modules\Crm\Models\Person;
use Database\Seeders\RoleSeeder;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function (): void {
    (new RoleSeeder)->run();

    $this->company = Company::query()->create(['name' => 'Praxis Alpha']);

    $this->anke = Person::query()->create([
        'first_name' => 'Anke',
        'last_name' => 'Brehm',
        'title' => 'Dr.',
        'gender' => Gender::Weiblich,
    ]);

    $this->contact = CompanyContact::query()->create([
        'company_id' => $this->company->id,
        'person_id' => $this->anke->id,
        'role' => 'Praxismanager*in',
        'department' => 'Verwaltung',
        'is_primary' => true,
    ]);
});

function besucheKontakt(object $test, CompanyContact $contact, ?Company $unter = null): object
{
    $company = $unter ?? $contact->company;

    return $test->actingAs(User::factory()->create(), 'staff')
        ->get('http://'.config('domains.erp').'/firmen/'.$company->id.'/kontakte/'.$contact->id);
}

test('die Kontaktansicht verlangt eine Anmeldung', function (): void {
    $this->get('http://'.config('domains.erp').'/firmen/'.$this->company->id.'/kontakte/'.$this->contact->id)
        ->assertRedirect('http://'.config('domains.erp').'/login');
});

test('sie zeigt Person, Rolle und Firma', function (): void {
    besucheKontakt($this, $this->contact)
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('erp/companies/contacts/Show')
            ->where('contact.person.name', 'Dr. Anke Brehm')
            ->where('contact.role', 'Praxismanager*in')
            ->where('contact.department', 'Verwaltung')
            ->where('contact.isPrimary', true)
            ->where('contact.company.name', 'Praxis Alpha')
            ->where('contact.person.stammdaten.Geschlecht', 'weiblich')
        );
});

test('ein Kontakt einer anderen Firma ist unter diesem Pfad nicht erreichbar', function (): void {
    /*
     * Die Firma ist der Rahmen, auch in der Adresse. Ohne `scopeBindings`
     * liesse sich jeder Kontakt unter jeder beliebigen Firma aufrufen — die
     * Ansicht zeigte dann eine Rolle, die dort gar nicht gilt.
     */
    $fremde = Company::query()->create(['name' => 'Praxis Beta']);

    besucheKontakt($this, $this->contact, unter: $fremde)->assertNotFound();
});

test('sie fuehrt die weiteren Firmen derselben Person', function (): void {
    $beta = Company::query()->create(['name' => 'Praxis Beta']);
    CompanyContact::query()->create([
        'company_id' => $beta->id,
        'person_id' => $this->anke->id,
        'role' => 'Einkauf',
    ]);

    besucheKontakt($this, $this->contact)
        ->assertInertia(fn (Assert $page) => $page
            ->has('contact.weitereFirmen', 1)
            ->where('contact.weitereFirmen.0.name', 'Praxis Beta')
            ->where('contact.weitereFirmen.0.role', 'Einkauf')
        );
});

test('die Einwilligung zeigt je Kanal den juengsten Stand', function (): void {
    /*
     * Ein Widerruf aendert keine Einwilligung, er schreibt eine neue fort
     * (D-013). Beide Zeilen bleiben stehen; massgeblich ist die juengere.
     */
    $this->anke->consents()->create([
        'channel' => ConsentChannel::Mail,
        'status' => ConsentStatus::Erteilt,
        'source' => ConsentSource::Formular,
        'granted_at' => now()->subYear(),
    ]);
    $this->anke->consents()->create([
        'channel' => ConsentChannel::Mail,
        'status' => ConsentStatus::Widerrufen,
        'source' => ConsentSource::Telefonisch,
        'revoked_at' => now()->subDay(),
    ]);

    besucheKontakt($this, $this->contact)
        ->assertInertia(fn (Assert $page) => $page
            ->has('contact.consents', 1)
            ->where('contact.consents.0.channel', 'E-Mail')
            ->where('contact.consents.0.status', 'widerrufen')
            ->where('contact.consents.0.granted', false)
            ->where('contact.consents.0.source', 'telefonisch')
        );
});

test('sie zeigt den Zustand des Portalzugangs', function (): void {
    $this->anke->contactChannels()->create([
        'channel_type' => ChannelType::Phone,
        'label' => ChannelLabel::Durchwahl,
        'value' => '04181 1234-11',
        'is_primary' => true,
    ]);

    CustomerAccount::query()->create([
        'person_id' => $this->anke->id,
        'email' => 'a.brehm@praxis.test',
        'password' => 'password',
        'is_active' => false,
    ]);

    besucheKontakt($this, $this->contact)
        ->assertInertia(fn (Assert $page) => $page
            ->where('contact.account.email', 'a.brehm@praxis.test')
            ->where('contact.account.active', false)
            ->where('contact.channels.0.value', '04181 1234-11')
            ->where('contact.channels.0.typeLabel', 'Telefon')
        );
});

test('ein Kontakt ohne Zugang meldet das ausdruecklich', function (): void {
    besucheKontakt($this, $this->contact)
        ->assertInertia(fn (Assert $page) => $page->where('contact.account', null));
});
