<?php

namespace Database\Seeders\Demo;

use App\Modules\Crm\Models\Company;
use App\Modules\Crm\Models\Person;
use Illuminate\Database\Seeder;

/**
 * A single, clearly-fictional CRM sample: one company with an address, one
 * location and one contact person. Idempotent — safe to re-run.
 */
class DemoCrmSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::firstOrCreate(
            ['name' => 'Demo Praxis Dr. Muster'],
            ['legal_name' => 'Demo Praxis Dr. Muster MVZ GmbH', 'notes' => 'Beispiel-Datensatz für lokale Tests.'],
        );

        $company->address()->updateOrCreate([], [
            'street' => 'Musterstraße',
            'house_number' => '12',
            'postal_code' => '48149',
            'city' => 'Münster',
            'country_code' => 'DE',
        ]);

        $location = $company->locations()->firstOrCreate(['name' => 'Hauptstandort']);
        $location->address()->updateOrCreate([], [
            'street' => 'Musterstraße',
            'house_number' => '12',
            'postal_code' => '48149',
            'city' => 'Münster',
            'country_code' => 'DE',
        ]);

        $person = Person::firstOrCreate(
            ['first_name' => 'Erika', 'last_name' => 'Musterfrau'],
            ['email' => 'erika.musterfrau@demo.dormed.test', 'phone' => '+49 251 0000000'],
        );

        $company->contacts()->firstOrCreate(
            ['person_id' => $person->id],
            ['role' => 'Praxismanagerin', 'is_primary' => true],
        );
    }
}
