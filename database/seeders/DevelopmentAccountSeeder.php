<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Modules\Core\Models\Employee;
use App\Modules\Core\Models\Role;
use App\Modules\Core\Models\Site;
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
        $this->furtherEmployees();
        $this->customerAccount();
        $this->furtherContacts();
        $this->assignResponsibles();
    }

    /**
     * Weitere Mitarbeiter — abgeleitet aus `database/seeders/employees/`.
     *
     * Das Verzeichnis ist die Quelle, nicht eine Liste in dieser Datei: die
     * Fotos heissen `Initial.Nachname.jpg`, daraus entstehen Name, Mailadresse
     * und die Verknuepfung zum Bild. Kommt ein Foto dazu, kommt der Mitarbeiter
     * mit — dieselbe Ueberlegung wie im `ObjectStorageSeeder`, wo eine
     * gepflegte Ordnerliste driften wuerde.
     *
     * > **Der Vorname ist nur die Initiale**, weil mehr in den Dateinamen nicht
     * > steht. „A. Draheim" ist damit unvollstaendig, aber nicht erfunden — und
     * > das ist die bessere der beiden Moeglichkeiten.
     *
     * > **Rollen- UND Standortzuordnung sind Platzhalter.** Wer welcher
     * > Abteilung angehoert und an welchem Standort sitzt, geht aus den Dateien
     * > nicht hervor; verteilt wird der Reihe nach, damit die Listenansicht
     * > Abwechslung zeigt. Vor dem ersten echten Einsatz gehoert das ersetzt.
     */
    private function furtherEmployees(): void
    {
        $rollen = Role::query()->where('is_active', true)->orderBy('key')->pluck('id', 'key');
        // Standorte wechseln sich ab — auch das ist Platzhalter, siehe oben.
        $standorte = Site::query()->orderBy('name')->pluck('id');

        foreach ($this->employeePhotos() as $index => [$initiale, $nachname, $pfad]) {
            $email = mb_strtolower($initiale.'.'.$nachname).'@dormed.de';

            // Der eigene Zugang ist schon da und traegt einen vollen Vornamen.
            if ($email === 'l.everding@dormed.de') {
                continue;
            }

            $user = Employee::query()->updateOrCreate(
                ['email' => $email],
                [
                    'first_name' => mb_strtoupper($initiale).'.',
                    'last_name' => $nachname,
                    'password' => 'password',
                    'email_verified_at' => now(),
                    'is_admin' => false,
                    'is_active' => true,
                    'role_id' => $rollen->values()[$index % $rollen->count()],
                    'site_id' => $standorte[$index % $standorte->count()],
                    'last_login_at' => now()->subDays($index * 3),
                ],
            );

            // `photo_path` ist nicht `$fillable` — es gehoert nicht in eine
            // Maske und wird deshalb ausdruecklich gesetzt.
            $user->photo_path = $pfad;
            $user->save();
        }

        $this->besondereZustaende();
    }

    /**
     * Die Fotos aus `database/seeders/employees/`, je Person eines.
     *
     * Liegt ein Bild doppelt vor (`.jpg` und `.png`), gewinnt das erste nach
     * Dateiname — eine Person, ein Foto, ohne dass die Auswahl vom Zufall der
     * Verzeichnisreihenfolge abhaengt.
     *
     * @return list<array{string, string, string}> Initiale, Nachname, Ablagepfad
     */
    private function employeePhotos(): array
    {
        $verzeichnis = database_path('seeders/employees');

        if (! is_dir($verzeichnis)) {
            return [];
        }

        $dateien = array_values(array_filter(
            scandir($verzeichnis) ?: [],
            fn (string $name): bool => (bool) preg_match('/^([A-Za-z])\.([A-Za-zÄÖÜäöüß-]+)\.(jpe?g|png)$/u', $name),
        ));

        sort($dateien);

        $gefunden = [];

        foreach ($dateien as $datei) {
            preg_match('/^([A-Za-z])\.([A-Za-zÄÖÜäöüß-]+)\./u', $datei, $treffer);
            $schluessel = mb_strtolower($treffer[1].'.'.$treffer[2]);

            $gefunden[$schluessel] ??= [$treffer[1], $treffer[2], 'employees/'.$datei];
        }

        return array_values($gefunden);
    }

    /**
     * Zustaende, die es in der Liste zu sehen geben soll.
     *
     * „Ich komme nicht rein" hat drei Ursachen und sieht dreimal verschieden
     * aus: stillgelegt (D-032), ohne Passwort (der Normalfall, sobald
     * Entra-SSO uebernimmt, D-029), mit zweitem Faktor (ADR-043). Ohne je einen
     * Vertreter liesse sich die Unterscheidung nicht pruefen.
     */
    private function besondereZustaende(): void
    {
        $ohneZugang = Employee::query()->where('email', '!=', 'l.everding@dormed.de')
            ->orderBy('email')->skip(1)->first();

        $stillgelegt = Employee::query()->where('email', '!=', 'l.everding@dormed.de')
            ->orderBy('email')->skip(2)->first();

        $mitZweitemFaktor = Employee::query()->where('email', '!=', 'l.everding@dormed.de')
            ->orderBy('email')->first();

        $ohneZugang?->forceFill(['password' => null, 'last_login_at' => null])->save();
        $stillgelegt?->forceFill(['is_active' => false])->save();
        $mitZweitemFaktor?->forceFill([
            'two_factor_secret' => encrypt('DEVSECRETDEVSECRET'),
            'two_factor_confirmed_at' => now()->subMonths(2),
        ])->save();
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
     * Verantwortliche je Praxis.
     *
     * In der Maske sind beide Felder Pflicht — die Seed-Firmen sollen deshalb
     * nicht in einem Zustand liegen, den man ueber die Oberflaeche gar nicht
     * herstellen koennte. Wer zustaendig ist, ist wie die Rollen selbst
     * Platzhalter.
     */
    private function assignResponsibles(): void
    {
        $vertrieb = Employee::query()->whereRelation('role', 'key', 'sales')
            ->where('is_active', true)->orderBy('last_name')->pluck('id');
        $service = Employee::query()->whereRelation('role', 'key', 'service')
            ->where('is_active', true)->orderBy('last_name')->pluck('id');

        if ($vertrieb->isEmpty() || $service->isEmpty()) {
            return;
        }

        foreach (Company::query()->orderBy('name')->get() as $index => $company) {
            $company->update([
                'responsible_sales_id' => $vertrieb[$index % $vertrieb->count()],
                'responsible_service_id' => $service[$index % $service->count()],
            ]);
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
        Employee::query()->updateOrCreate(
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
                // NOT NULL: jeder Mitarbeiter gehoert zu einer Betriebsstaette.
                'site_id' => Site::query()->orderBy('name')->value('id'),
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
