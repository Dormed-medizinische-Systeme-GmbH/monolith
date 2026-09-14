<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Modules\Core\Models\Role;
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
        $this->furtherContacts();
    }

    /**
     * Weitere Kontakte, damit die Listenansicht etwas zu zeigen hat.
     *
     * Bewusst alle drei Zugangszustaende (ADR-037/042): ohne Zugang, mit
     * aktivem, mit gesperrtem. Die Unterscheidung ist der Zweck der Liste — ein
     * gesperrter Zugang sieht sonst aus wie gar keiner, und der Kunde ruft an,
     * weil er sich nicht anmelden kann.
     */
    private function furtherContacts(): void
    {
        $zweite = Company::query()->firstOrCreate(
            ['name' => 'Gemeinschaftspraxis Nord'],
            ['debitor_number' => 'K-10002', 'avv_status' => 'signed', 'avv_signed_at' => now()->subYear()],
        );
        $zweite->locations()->firstOrCreate(['name' => 'Hauptstandort'], ['is_primary' => true]);
        $zweite->locations()->firstOrCreate(['name' => 'Zweigstelle Süd'], ['is_primary' => false]);

        $zweite->address()->firstOrCreate([], [
            'street' => 'Nordring',
            'house_number' => '8a',
            'postal_code' => '21073',
            'city' => 'Hamburg',
        ]);

        self::channels($zweite, [
            [ChannelType::Phone, ChannelLabel::Zentrale, '040 987654-0', true],
            [ChannelType::Email, ChannelLabel::Geschaeftlich, 'info@nordpraxis.test', true],
        ]);

        $erste = Company::query()->where('name', 'Musterpraxis Dr. Muster')->firstOrFail();

        /** @var list<array{string, string, string, ?string, ?bool}> */
        $kontakte = [
            // Vorname, Nachname, Rolle, Zugangs-Mail (null = keiner), aktiv
            ['Anke', 'Brehm', 'Praxismanager*in', 'a.brehm@praxis.test', true],
            ['Tobias', 'Ritter', 'Einkauf', null, null],
            ['Sabine', 'Kohl', 'Buchhaltung', 's.kohl@praxis.test', false],
            ['Martin', 'Vogt', 'IT', null, null],
            ['Claudia', 'Nowak', 'Ärztliche Leitung', 'c.nowak@nordpraxis.test', true],
            ['Jens', 'Hartmann', 'Technik', null, null],
            ['Petra', 'Lindner', 'Empfang', null, null],
            ['Ulrich', 'Baumann', 'Einkauf', 'u.baumann@nordpraxis.test', true],
        ];

        foreach ($kontakte as $index => [$vorname, $nachname, $rolle, $mail, $aktiv]) {
            $company = $index < 4 ? $erste : $zweite;

            $person = Person::query()->firstOrCreate(
                ['first_name' => $vorname, 'last_name' => $nachname],
                ['gender' => Gender::Unbekannt, 'locale' => 'de'],
            );

            CompanyContact::query()->firstOrCreate(
                ['company_id' => $company->id, 'person_id' => $person->id],
                ['role' => $rolle, 'is_primary' => false],
            );

            self::channels($person, [
                [ChannelType::Phone, ChannelLabel::Durchwahl, '04181 1234-'.(10 + $index), true],
                [ChannelType::Email, ChannelLabel::Geschaeftlich, mb_strtolower($vorname[0].'.'.$nachname).'@praxis.test', true],
            ]);

            /*
             * Einwilligungen werden fortgeschrieben, nicht geaendert (D-013).
             * Der erste Kontakt bekommt deshalb einen Widerruf NACH einer
             * Erteilung — sonst sieht man der Ansicht nicht an, dass sie den
             * juengsten Stand je Kanal zeigt und nicht einfach alles.
             */
            self::consents($person, [
                [ConsentChannel::Mail, ConsentStatus::Erteilt, ConsentSource::Formular],
                [ConsentChannel::Telefon, ConsentStatus::Erteilt, ConsentSource::Muendlich],
            ]);

            if ($index === 0) {
                self::consents($person, [
                    [ConsentChannel::Mail, ConsentStatus::Widerrufen, ConsentSource::Telefonisch],
                ]);
            }

            if ($mail === null) {
                continue;
            }

            CustomerAccount::query()->updateOrCreate(
                ['person_id' => $person->id],
                ['email' => $mail, 'password' => 'password', 'is_active' => $aktiv],
            );
        }
    }

    /**
     * Kommunikationswege an eine Company ODER Person haengen (D-010).
     *
     * @param  list<array{ChannelType, ChannelLabel, string, bool}>  $channels
     */
    private static function channels(Company|Person $owner, array $channels): void
    {
        foreach ($channels as [$type, $label, $value, $isPrimary]) {
            $owner->contactChannels()->firstOrCreate(
                ['channel_type' => $type, 'value' => $value],
                ['label' => $label, 'is_primary' => $isPrimary],
            );
        }
    }

    /**
     * Einwilligungen je Person (D-013). Ein Statuswechsel erzeugt einen NEUEN
     * Datensatz, er aendert keinen bestehenden.
     *
     * @param  list<array{ConsentChannel, ConsentStatus, ConsentSource}>  $consents
     */
    private static function consents(Person $person, array $consents): void
    {
        foreach ($consents as [$channel, $status, $source]) {
            $person->consents()->firstOrCreate(
                ['channel' => $channel, 'status' => $status],
                [
                    'source' => $source,
                    'granted_at' => $status === ConsentStatus::Erteilt ? now()->subMonths(6) : null,
                    'revoked_at' => $status === ConsentStatus::Widerrufen ? now()->subWeek() : null,
                ],
            );
        }
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
                // Platzhalter, damit die Detailansicht ihre Abschnitte auch
                // wirklich zeigt — leere Abschnitte blendet sie aus, und dann
                // sieht ein Entwurfsfehler aus wie fehlende Daten.
                'legal_form' => 'Gesellschaft bürgerlichen Rechts',
                'tax_number' => '21/815/04711',
                'vat_id' => 'DE000000000',
                'iban' => 'DE02120300000000202051',
                'bic' => 'BYLADEM1001',
                'bank_account_holder' => 'Musterpraxis Dr. Muster GbR',
                'bank_name' => 'Musterbank',
                'notes' => 'Platzhalter aus dem Entwicklungs-Seed.',
            ],
        );

        // Jede Company hat mindestens einen Standort (D-007).
        $location = $company->locations()->firstOrCreate(
            ['name' => 'Hauptstandort'],
            ['is_primary' => true],
        );

        $company->address()->firstOrCreate([], [
            'street' => 'Musterstraße',
            'house_number' => '12',
            'postal_code' => '21244',
            'city' => 'Buchholz in der Nordheide',
        ]);

        $location->address()->firstOrCreate([], [
            'street' => 'Musterstraße',
            'house_number' => '12',
            'postal_code' => '21244',
            'city' => 'Buchholz in der Nordheide',
        ]);

        self::channels($company, [
            [ChannelType::Phone, ChannelLabel::Zentrale, '04181 1234-0', true],
            [ChannelType::Fax, ChannelLabel::Praxis, '04181 1234-99', false],
            [ChannelType::Email, ChannelLabel::Rechnungsversand, 'rechnung@musterpraxis.test', true],
            [ChannelType::Web, ChannelLabel::Homepage, 'www.musterpraxis.test', true],
        ]);

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
