<?php

declare(strict_types=1);

use App\Modules\Core\Models\User;
use App\Modules\Crm\Enums\Gender;
use App\Modules\Crm\Models\Company;
use App\Modules\Crm\Models\CompanyContact;
use App\Modules\Crm\Models\MedicalSpecialty;
use App\Modules\Crm\Models\Person;
use Database\Seeders\RoleSeeder;
use Inertia\Testing\AssertableInertia as Assert;

beforeEach(function (): void {
    (new RoleSeeder)->run();
});

function firma(string $name, array $attribute = [], ?array $anschrift = null): Company
{
    $company = Company::query()->create(['name' => $name, ...$attribute]);

    if ($anschrift !== null) {
        $company->address()->create($anschrift);
    }

    return $company;
}

function kontaktBei(Company $company, string $vorname, string $nachname, string $rolle = 'Einkauf'): Person
{
    $person = Person::query()->firstOrCreate(
        ['first_name' => $vorname, 'last_name' => $nachname],
        ['gender' => Gender::Unbekannt],
    );

    CompanyContact::query()->create([
        'company_id' => $company->id,
        'person_id' => $person->id,
        'role' => $rolle,
    ]);

    return $person;
}

function besucheFirmen(object $test, string $query = ''): object
{
    return $test->actingAs(User::factory()->create(), 'staff')
        ->get('http://'.config('domains.erp').'/firmen'.$query);
}

test('die Liste verlangt eine Anmeldung', function (): void {
    $this->get('http://'.config('domains.erp').'/firmen')
        ->assertRedirect('http://'.config('domains.erp').'/login');
});

test('sie zeigt Firma, Fachrichtung und Anschrift', function (): void {
    $fach = MedicalSpecialty::query()->create(['name' => 'Radiologie']);

    firma('Praxis Alpha', [
        'name_addition' => 'Gemeinschaftspraxis',
        'debitor_number' => 'K-1',
        'medical_specialty_id' => $fach->id,
    ], [
        'street' => 'Hauptstraße',
        'house_number' => '7',
        'postal_code' => '21244',
        'city' => 'Buchholz',
    ]);

    besucheFirmen($this)
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->component('erp/companies/Index')
            ->where('rows.0.name', 'Praxis Alpha')
            // Untertitel der Leitspalte — wird gesucht, also muss er auch
            // ausgeliefert werden.
            ->where('rows.0.nameAddition', 'Gemeinschaftspraxis')
            ->where('rows.0.specialty', 'Radiologie')
            ->where('rows.0.street', 'Hauptstraße 7')
            ->where('rows.0.city', '21244 Buchholz')
        );
});

test('eine Firma mit mehreren Ansprechpartnern bleibt EINE Zeile', function (): void {
    /*
     * Der Grund, warum diese Liste Firmen fuehrt und nicht Personen: ein Join
     * auf `company_contacts` machte aus einer Praxis mit drei Kontakten drei
     * Zeilen und aus `meta.total` die Zahl der Beziehungen statt der Firmen.
     * `CompanyList` zaehlt deshalb, statt zu joinen.
     */
    $alpha = firma('Praxis Alpha');
    kontaktBei($alpha, 'Anke', 'Brehm');
    kontaktBei($alpha, 'Tobias', 'Ritter');
    kontaktBei($alpha, 'Sabine', 'Kohl');

    besucheFirmen($this)
        ->assertInertia(fn (Assert $page) => $page
            ->where('meta.total', 1)
            ->has('rows', 1)
        );
});

test('eine Person bei zwei Firmen erzeugt keine Doppelzeile', function (): void {
    $alpha = firma('Praxis Alpha');
    $beta = firma('Praxis Beta');

    $anke = kontaktBei($alpha, 'Anke', 'Brehm', 'Praxismanager*in');
    CompanyContact::query()->create([
        'company_id' => $beta->id,
        'person_id' => $anke->id,
        'role' => 'Einkauf',
    ]);

    besucheFirmen($this)
        ->assertInertia(fn (Assert $page) => $page
            ->where('meta.total', 2)
            ->has('rows', 2)
        );
});

test('die Suche greift auf Name, Kundennummer und Ort', function (string $suche, string $erwartet): void {
    firma('Praxis Alpha', ['debitor_number' => 'K-1'], [
        'street' => 'Hauptstraße', 'postal_code' => '21244', 'city' => 'Buchholz',
    ]);
    firma('Gemeinschaftspraxis Nord', ['debitor_number' => 'K-2'], [
        'street' => 'Nordring', 'postal_code' => '21073', 'city' => 'Hamburg',
    ]);

    besucheFirmen($this, '?suche='.urlencode($suche))
        ->assertInertia(fn (Assert $page) => $page
            ->where('meta.total', 1)
            ->where('rows.0.name', $erwartet)
        );
})->with([
    ['Alpha', 'Praxis Alpha'],
    // Die Kundennummer steht nicht mehr in der Tabelle, bleibt aber auffindbar.
    ['K-2', 'Gemeinschaftspraxis Nord'],
    ['Nordring', 'Gemeinschaftspraxis Nord'],
    ['Buchholz', 'Praxis Alpha'],
]);

test('sortiert wird nur nach freigegebenen Spalten', function (): void {
    /*
     * Der Spaltenname kommt aus der URL und landet in einer ORDER-BY-Klausel.
     * Ohne Freigabeliste liesse sich damit nach beliebigen Spalten ordnen — auch
     * nach solchen aus fremden Tabellen. Unbekanntes faellt still auf die
     * Vorgabe zurueck.
     */
    firma('Praxis Alpha');

    besucheFirmen($this, '?sortierung=companies.iban')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page->where('meta.sort', 'name'));
});

test('nach der Strasse laesst sich sortieren', function (): void {
    // Die Spalte kommt aus der gejointen `addresses` — ohne den Join im
    // Lesemodell liefe das ORDER BY ins Leere.
    firma('Praxis Alpha', [], ['street' => 'Zederweg', 'postal_code' => '21244', 'city' => 'Buchholz']);
    firma('Praxis Beta', [], ['street' => 'Ahornallee', 'postal_code' => '21073', 'city' => 'Hamburg']);

    besucheFirmen($this, '?sortierung=street')
        ->assertOk()
        ->assertInertia(fn (Assert $page) => $page
            ->where('meta.sort', 'street')
            ->where('rows.0.name', 'Praxis Beta')
            ->where('rows.1.name', 'Praxis Alpha')
        );
});

test('die Seitenaufteilung meldet die Gesamtzahl', function (): void {
    foreach (range(1, 30) as $i) {
        firma('Praxis '.str_pad((string) $i, 2, '0', STR_PAD_LEFT));
    }

    besucheFirmen($this)
        ->assertInertia(fn (Assert $page) => $page
            ->where('meta.total', 30)
            ->where('meta.lastPage', 2)
            ->has('rows', 25)
        );
});
