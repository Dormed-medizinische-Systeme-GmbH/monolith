<?php

declare(strict_types=1);

use App\Modules\Core\Models\User;
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
    $this->other = Company::query()->create(['name' => 'Praxis Beta']);
});

function kontakt(Company $company, string $vorname, string $nachname, ?string $mail = null, bool $aktiv = true): Person
{
    $person = Person::query()->create([
        'first_name' => $vorname,
        'last_name' => $nachname,
        'gender' => Gender::Unbekannt,
    ]);

    CompanyContact::query()->create([
        'company_id' => $company->id,
        'person_id' => $person->id,
        'role' => 'Einkauf',
    ]);

    if ($mail !== null) {
        CustomerAccount::query()->create([
            'person_id' => $person->id,
            'email' => $mail,
            'password' => 'password',
            'is_active' => $aktiv,
        ]);
    }

    return $person;
}

function besucheListe(object $test, string $query = ''): object
{
    return $test->actingAs(User::factory()->create(), 'staff')
        ->get('http://'.config('domains.erp').'/kontakte'.$query);
}

test('die Liste verlangt eine Anmeldung', function (): void {
    $this->get('http://'.config('domains.erp').'/kontakte')
        ->assertRedirect('http://'.config('domains.erp').'/login');
});

test('sie zeigt Kontakte mit Firma und Rolle', function (): void {
    kontakt($this->company, 'Anke', 'Brehm');

    besucheListe($this)
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('erp/contacts/Index')
            ->where('rows.0.name', 'Anke Brehm')
            ->where('rows.0.company', 'Praxis Alpha')
            ->where('rows.0.role', 'Einkauf')
        );
});

test('sie unterscheidet die drei Zustaende des Portalzugangs', function (): void {
    /*
     * Der eigentliche Zweck dieser Ansicht (ADR-037/042): ein gesperrter Zugang
     * sieht sonst aus wie gar keiner, und der Kunde ruft an, weil er sich nicht
     * anmelden kann.
     */
    kontakt($this->company, 'Ohne', 'Zugang');
    kontakt($this->company, 'Mit', 'Zugang', 'aktiv@praxis.test');
    kontakt($this->company, 'Gesperrter', 'Zugang', 'gesperrt@praxis.test', aktiv: false);

    besucheListe($this, '?sortierung=access')
        ->assertInertia(function (Assert $page): void {
            $rows = collect($page->toArray()['props']['rows'])->keyBy('name');

            expect($rows['Ohne Zugang']['accountEmail'])->toBeNull();
            expect($rows['Mit Zugang']['accountActive'])->toBeTrue();
            expect($rows['Gesperrter Zugang']['accountActive'])->toBeFalse();
            expect($rows['Gesperrter Zugang']['accountEmail'])->toBe('gesperrt@praxis.test');
        });
});

test('die Suche greift auf Name, Firma und Zugangs-Mail', function (string $suche, string $erwartet): void {
    kontakt($this->company, 'Anke', 'Brehm', 'brehm@alpha.test');
    kontakt($this->other, 'Tobias', 'Ritter');

    besucheListe($this, '?suche='.urlencode($suche))
        ->assertInertia(fn (Assert $page) => $page
            ->where('meta.total', 1)
            ->where('rows.0.name', $erwartet)
        );
})->with([
    ['Brehm', 'Anke Brehm'],
    ['Beta', 'Tobias Ritter'],
    ['brehm@alpha', 'Anke Brehm'],
]);

test('sortiert wird nur nach freigegebenen Spalten', function (): void {
    /*
     * Der Spaltenname kommt aus der URL und landet in einer ORDER-BY-Klausel.
     * Ohne Freigabeliste liesse sich damit nach beliebigen Spalten ordnen — auch
     * nach solchen aus fremden Tabellen, was Rueckschluesse auf deren Inhalt
     * erlaubt. Unbekanntes faellt still auf die Vorgabe zurueck.
     */
    kontakt($this->company, 'Anke', 'Brehm');

    besucheListe($this, '?sortierung=customer_accounts.password')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->where('meta.sort', 'name'));
});

test('die Sortierrichtung laesst sich umkehren', function (): void {
    kontakt($this->company, 'Anke', 'Brehm');
    kontakt($this->company, 'Tobias', 'Ritter');

    besucheListe($this, '?sortierung=name&richtung=desc')
        ->assertInertia(fn (Assert $page) => $page
            ->where('meta.direction', 'desc')
            ->where('rows.0.name', 'Tobias Ritter')
        );
});

test('die Seitenaufteilung meldet die Gesamtzahl', function (): void {
    foreach (range(1, 30) as $i) {
        kontakt($this->company, 'Person', 'Nummer'.str_pad((string) $i, 2, '0', STR_PAD_LEFT));
    }

    besucheListe($this)
        ->assertInertia(fn (Assert $page) => $page
            ->where('meta.total', 30)
            ->where('meta.lastPage', 2)
            ->has('rows', 25)
        );
});
