<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Modules\Core\Models\Role;
use App\Modules\Core\Models\User;
use App\Modules\Crm\Enums\Gender;
use App\Modules\Crm\Models\Company;
use App\Modules\Crm\Models\CompanyContact;
use App\Modules\Crm\Models\CustomerAccount;
use App\Modules\Crm\Models\MedicalSpecialty;
use App\Modules\Crm\Models\Person;
use Illuminate\Database\Seeder;

/**
 * Zwei Anmeldedaten zum Ausprobieren — je einer pro Guard (ADR-042).
 *
 * | Zugriffspunkt | Anmeldung               | Passwort |
 * | ERP           | l.everding@dormed.de    | password |
 * | Portal/Shop   | l.everding@web.de       | password |
 *
 * **Laeuft nur ausserhalb von Produktion.** Das Passwort steht hier im
 * Klartext; ein solcher Datensatz in einer echten Datenbank waere eine offene
 * Tuer. Die uebrigen Seeder (Rollen, Fachrichtungen, Object Storage) sind
 * dagegen Grundbestand und laufen ueberall.
 *
 * Der Kundenzugang haengt am CRM-Kontakt (ADR-037), und eine Person gibt es nie
 * eigenstaendig (D-002) — deshalb entstehen hier Company, Person und
 * CompanyContact mit. Alles ausser der Mailadresse ist Platzhalter.
 */
final class DevelopmentAccountSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->isProduction()) {
            $this->command?->getOutput()->writeln(
                '  <fg=yellow>Entwicklungszugaenge uebersprungen (Produktion).</>'
            );

            return;
        }

        $this->staffAccount();
        $this->customerAccount();
    }

    private function staffAccount(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'l.everding@dormed.de'],
            [
                'first_name' => 'Linus',
                'last_name' => 'Stücker-Everding',
                'password' => 'password',
                'email_verified_at' => now(),
                'is_admin' => true,
                'is_active' => true,
                // Vollzugriff ueber den Katalog (D-125) — nicht ueber
                // `is_admin`; der ist nur der Bootstrap-Bypass (D-028).
                'role_id' => Role::query()->where('key', 'geschaeftsfuehrung')->value('id'),
            ],
        );
    }

    private function customerAccount(): void
    {
        $company = Company::query()->firstOrCreate(
            ['name' => 'Musterpraxis Dr. Muster'],
            [
                'name_addition' => 'Gemeinschaftspraxis',
                'debitor_number' => 'K-10001',
                'avv_status' => 'none',
                'medical_specialty_id' => MedicalSpecialty::query()
                    ->where('name', 'Allgemeinmedizin / Hausarzt')
                    ->value('id'),
                'notes' => 'Platzhalter aus dem Entwicklungs-Seed.',
            ],
        );

        // Jede Company hat mindestens einen Standort (D-007).
        $company->locations()->firstOrCreate(
            ['name' => 'Hauptstandort'],
            ['is_primary' => true],
        );

        $person = Person::query()->firstOrCreate(
            ['first_name' => 'Linus', 'last_name' => 'Everding'],
            [
                'name_suffix' => null,
                'title' => null,
                'gender' => Gender::Unbekannt,
                'locale' => 'de',
            ],
        );

        CompanyContact::query()->firstOrCreate(
            ['company_id' => $company->id, 'person_id' => $person->id],
            ['role' => 'Praxismanager*in', 'department' => null, 'is_primary' => true],
        );

        CustomerAccount::query()->updateOrCreate(
            ['person_id' => $person->id],
            [
                'email' => 'l.everding@web.de',
                'password' => 'password',
                'email_verified_at' => now(),
                'is_active' => true,
            ],
        );
    }
}
