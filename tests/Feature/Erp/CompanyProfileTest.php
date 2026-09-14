<?php

declare(strict_types=1);

use App\Modules\Core\Models\User;
use App\Modules\Crm\Enums\ChannelLabel;
use App\Modules\Crm\Enums\ChannelType;
use App\Modules\Crm\Enums\Gender;
use App\Modules\Crm\Models\Company;
use App\Modules\Crm\Models\CompanyContact;
use App\Modules\Crm\Models\CustomerAccount;
use App\Modules\Crm\Models\Person;
use Database\Seeders\RoleSeeder;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function (): void {
    (new RoleSeeder)->run();

    $this->company = Company::query()->create([
        'name' => 'Praxis Alpha',
        'name_addition' => 'Gemeinschaftspraxis',
        'debitor_number' => 'K-1',
        'legal_form' => 'GbR',
    ]);

    $this->company->address()->create([
        'street' => 'Hauptstraße',
        'house_number' => '12',
        'postal_code' => '21244',
        'city' => 'Buchholz',
    ]);

    $this->company->contactChannels()->create([
        'channel_type' => ChannelType::Phone,
        'label' => ChannelLabel::Zentrale,
        'value' => '04181 1234-0',
        'is_primary' => true,
    ]);

    $this->company->locations()->create(['name' => 'Hauptstandort', 'is_primary' => true]);
});

function person(string $vorname, string $nachname): Person
{
    return Person::query()->create([
        'first_name' => $vorname,
        'last_name' => $nachname,
        'gender' => Gender::Unbekannt,
    ]);
}

function besucheFirma(object $test, Company $company): object
{
    return $test->actingAs(User::factory()->create(), 'staff')
        ->get('http://'.config('domains.erp').'/firmen/'.$company->id);
}

test('die Detailansicht verlangt eine Anmeldung', function (): void {
    $this->get('http://'.config('domains.erp').'/firmen/'.$this->company->id)
        ->assertRedirect('http://'.config('domains.erp').'/login');
});

test('sie zeigt die Stammdaten der Firma auf einen Blick', function (): void {
    besucheFirma($this, $this->company)
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('erp/companies/Show')
            ->where('company.name', 'Praxis Alpha')
            ->where('company.nameAddition', 'Gemeinschaftspraxis')
            ->where('company.stammdaten.Kundennummer', 'K-1')
            ->where('company.stammdaten.Rechtsform', 'GbR')
            ->where('company.address.street', 'Hauptstraße 12')
            ->where('company.address.city', '21244 Buchholz')
            ->where('company.channels.0.value', '04181 1234-0')
            ->where('company.channels.0.typeLabel', 'Telefon')
            ->where('company.avv.signed', false)
            ->where('company.locations.0.name', 'Hauptstandort')
        );
});

test('sie fuehrt die Ansprechpartner mit ihrer Rolle', function (): void {
    CompanyContact::query()->create([
        'company_id' => $this->company->id,
        'person_id' => person('Anke', 'Brehm')->id,
        'role' => 'Praxismanager*in',
        'is_primary' => true,
    ]);

    besucheFirma($this, $this->company)
        ->assertInertia(fn (Assert $page) => $page
            ->where('company.contacts.0.name', 'Anke Brehm')
            ->where('company.contacts.0.role', 'Praxismanager*in')
            ->where('company.contacts.0.isPrimary', true)
        );
});

test('sie unterscheidet die drei Zustaende des Portalzugangs', function (): void {
    /*
     * Der eigentliche Zweck dieser Ansicht (ADR-037/042): ein gesperrter Zugang
     * sieht sonst aus wie gar keiner, und der Kunde ruft an, weil er sich nicht
     * anmelden kann.
     */
    $zustaende = [
        ['Ohne', 'Zugang', null, null],
        ['Mit', 'Zugang', 'aktiv@praxis.test', true],
        ['Gesperrter', 'Zugang', 'gesperrt@praxis.test', false],
    ];

    foreach ($zustaende as [$vorname, $nachname, $mail, $aktiv]) {
        $p = person($vorname, $nachname);

        CompanyContact::query()->create([
            'company_id' => $this->company->id,
            'person_id' => $p->id,
            'role' => 'Einkauf',
        ]);

        if ($mail !== null) {
            CustomerAccount::query()->create([
                'person_id' => $p->id,
                'email' => $mail,
                'password' => 'password',
                'is_active' => $aktiv,
            ]);
        }
    }

    besucheFirma($this, $this->company)
        ->assertInertia(function (Assert $page): void {
            $kontakte = collect($page->toArray()['props']['company']['contacts'])->keyBy('name');

            expect($kontakte['Ohne Zugang']['account'])->toBeNull();
            expect($kontakte['Mit Zugang']['account']['active'])->toBeTrue();
            expect($kontakte['Gesperrter Zugang']['account']['active'])->toBeFalse();
            expect($kontakte['Gesperrter Zugang']['account']['email'])->toBe('gesperrt@praxis.test');
        });
});

test('eine Person bei zwei Firmen erscheint in beiden mit der dortigen Rolle', function (): void {
    /*
     * Offene Frage 2 aus ADR-037, hier aufgeloest: die Firma ist der Rahmen,
     * also ist die Mehrfachzuordnung kein Sonderfall mehr. Offen bleibt sie nur
     * fuer `app.company_id` im Portal.
     */
    $anke = person('Anke', 'Brehm');
    $beta = Company::query()->create(['name' => 'Praxis Beta']);

    CompanyContact::query()->create([
        'company_id' => $this->company->id,
        'person_id' => $anke->id,
        'role' => 'Praxismanager*in',
    ]);
    CompanyContact::query()->create([
        'company_id' => $beta->id,
        'person_id' => $anke->id,
        'role' => 'Einkauf',
    ]);

    besucheFirma($this, $this->company)
        ->assertInertia(fn (Assert $page) => $page
            ->has('company.contacts', 1)
            ->where('company.contacts.0.role', 'Praxismanager*in')
        );

    besucheFirma($this, $beta)
        ->assertInertia(fn (Assert $page) => $page
            ->has('company.contacts', 1)
            ->where('company.contacts.0.role', 'Einkauf')
        );
});

test('ein unsinniger Schluessel wird zu 404, nicht zu einem Datenbankfehler', function (): void {
    /*
     * Seit die Schluessel UUIDs sind (ADR-046) wuerde ein `where id = 'abc'`
     * in Postgres mit „invalid input syntax for type uuid" abbrechen — also
     * mit 500 statt 404. Laravel faengt das im Route-Model-Binding ab; dieser
     * Test haelt fest, dass wir uns darauf verlassen.
     */
    $this->actingAs(User::factory()->create(), 'staff')
        ->get('http://'.config('domains.erp').'/firmen/keine-uuid')
        ->assertNotFound();
});

test('eine geloeschte Firma ist nicht erreichbar', function (): void {
    $this->company->delete();

    besucheFirma($this, $this->company)->assertNotFound();
});
