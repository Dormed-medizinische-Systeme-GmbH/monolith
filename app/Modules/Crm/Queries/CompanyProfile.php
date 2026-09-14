<?php

declare(strict_types=1);

namespace App\Modules\Crm\Queries;

use App\Modules\Crm\Models\Address;
use App\Modules\Crm\Models\Company;
use App\Modules\Crm\Models\CompanyContact;
use App\Modules\Crm\Models\ContactChannel;
use App\Modules\Crm\Models\Location;
use Illuminate\Database\Eloquent\Collection;

/**
 * Die Detailansicht einer Firma: Stammdaten auf einen Blick, darunter ihre
 * Ansprechpartner.
 *
 * Hier ist der Rahmen genau eine Firma — deshalb ist eine Person, die bei
 * mehreren Praxen Ansprechpartner ist, kein Sonderfall mehr: sie taucht in
 * jeder dieser Praxen auf, jeweils mit der dortigen Rolle. Genau so ist es
 * fachlich gemeint (D-005).
 *
 * Offen bleibt davon nur die Frage aus ADR-037: `app.company_id` fuer die
 * Row-Level-Security ist bei so einer Person nicht eindeutig. Das betrifft das
 * Portal, nicht diese Ansicht.
 */
final class CompanyProfile
{
    /**
     * @return array<string, mixed>
     */
    public static function for(Company $company): array
    {
        $company->load([
            'address',
            'medicalSpecialty',
            'contactChannels',
            'locations.address',
            'billingCompany',
            'responsibleSales',
            'responsibleService',
            'contacts.person.customerAccount',
            'contacts.person.contactChannels',
        ]);

        return [
            'id' => $company->id,
            'name' => $company->name,
            'nameAddition' => $company->name_addition,
            'specialty' => $company->medicalSpecialty?->name,
            'notes' => $company->notes,

            // Steht als Kennzeichnung oben, nicht in der Aufstellung — sie ist
            // das, womit ein Mitarbeiter die Firma gegenueber Buchhaltung und
            // Lieferschein benennt.
            'debitorNumber' => $company->debitor_number,

            'stammdaten' => array_filter([
                'Rechtsform' => $company->legal_form,
                'Fachrichtung' => $company->medicalSpecialty?->name,
                'Steuernummer' => $company->tax_number,
                'USt-IdNr.' => $company->vat_id,
                'Wirtschafts-IdNr.' => $company->wid_number,
                'Handelsregister' => $company->trade_register_number,
                'Registergericht' => $company->register_court,
            ], fn (?string $value): bool => $value !== null && $value !== ''),

            'avv' => [
                'signed' => $company->avv_status->value === 'signed',
                'label' => $company->avv_status->label(),
                'signedAt' => $company->avv_signed_at?->format('d.m.Y'),
            ],

            'bank' => array_filter([
                'IBAN' => $company->iban,
                'BIC' => $company->bic,
                'Kontoinhaber' => $company->bank_account_holder,
                'Bank' => $company->bank_name,
            ], fn (?string $value): bool => $value !== null && $value !== ''),

            'address' => self::address($company->address),
            'channels' => self::channels($company->contactChannels),

            'billingCompany' => $company->billingCompany === null ? null : [
                'id' => $company->billingCompany->id,
                'name' => $company->billingCompany->name,
            ],

            'responsible' => [
                'sales' => $company->responsibleSales?->name,
                'service' => $company->responsibleService?->name,
            ],

            'locations' => $company->locations
                ->sortByDesc('is_primary')
                ->values()
                ->map(fn (Location $location): array => [
                    'id' => $location->id,
                    'name' => $location->name,
                    'isPrimary' => $location->is_primary,
                    'address' => self::address($location->address),
                    /*
                     * Der Hauptstandort liegt meistens an der Sitzadresse. Sie
                     * dort noch einmal auszuschreiben sieht aus wie ein
                     * Dublette-Fehler — die Ansicht verweist stattdessen
                     * darauf.
                     */
                    'sameAsCompanyAddress' => self::address($location->address) !== null
                        && self::address($location->address) === self::address($company->address),
                ])
                ->all(),

            'contacts' => $company->contacts
                ->sortBy([['is_primary', 'desc'], ['person.last_name', 'asc']])
                ->values()
                ->map(self::contact(...))
                ->all(),
        ];
    }

    /**
     * Eine Zeile der Ansprechpartner-Tabelle.
     *
     * Der Schluessel ist die BEZIEHUNG (`company_contacts.id`), nicht die
     * Person: dieselbe Person kann bei einer anderen Firma eine andere Rolle
     * haben, und die Zeile beschreibt die Rolle hier.
     *
     * @return array<string, mixed>
     */
    private static function contact(CompanyContact $contact): array
    {
        $person = $contact->person;
        $account = $person->customerAccount;

        return [
            'id' => $contact->id,
            'personId' => $person->id,
            'name' => trim(implode(' ', array_filter([
                $person->name_suffix, $person->title, $person->first_name, $person->last_name,
            ]))),
            'role' => $contact->role,
            'department' => $contact->department,
            'isPrimary' => $contact->is_primary,
            'channels' => self::channels($person->contactChannels),
            'account' => $account === null ? null : [
                'email' => $account->email,
                'active' => $account->is_active,
                'lastLoginAt' => $account->last_login_at?->format('d.m.Y H:i'),
            ],
        ];
    }

    /**
     * @param  Collection<int, ContactChannel>  $channels
     * @return list<array<string, mixed>>
     */
    private static function channels(Collection $channels): array
    {
        return $channels
            ->sortByDesc('is_primary')
            ->values()
            ->map(fn (ContactChannel $channel): array => [
                'id' => $channel->id,
                'type' => $channel->channel_type->value,
                'typeLabel' => $channel->channel_type->label(),
                'label' => $channel->label->label(),
                'value' => $channel->value,
                'isPrimary' => $channel->is_primary,
            ])
            ->all();
    }

    /**
     * @return array<string, mixed>|null
     */
    private static function address(?Address $address): ?array
    {
        if ($address === null) {
            return null;
        }

        return [
            'street' => trim("{$address->street} {$address->house_number}"),
            'city' => trim("{$address->postal_code} {$address->city}"),
            'district' => $address->district,
            'country' => $address->country_code,
        ];
    }
}
