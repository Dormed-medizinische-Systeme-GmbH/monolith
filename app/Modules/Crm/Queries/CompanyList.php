<?php

declare(strict_types=1);

namespace App\Modules\Crm\Queries;

use App\Modules\Crm\Enums\AvvStatus;
use App\Modules\Crm\Models\Company;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;

/**
 * Die Firmenliste des ERP als Lesemodell.
 *
 * **Eine Zeile ist eine Firma** — das ist der ganze Punkt. Die beiden Joins
 * hier koennen die Zeilen nicht vervielfachen: eine Firma hat hoechstens eine
 * Sitzadresse (D-003/D-014, unique auf `addressable`) und hoechstens eine
 * Fachrichtung. Ansprechpartner werden deshalb NICHT mitgejoint, sondern nur
 * gezaehlt — ein Join auf `company_contacts` machte aus einer Praxis mit sieben
 * Kontakten sieben Zeilen. Die Personen stehen in der Detailansicht
 * (`CompanyProfile`), und dort ist genau eine Firma der Rahmen.
 */
final class CompanyList
{
    /**
     * Freigegebene Sortierspalten: Schluessel aus der URL => Ausdruck fuer
     * `ORDER BY`. Was hier nicht steht, laesst sich nicht sortieren.
     *
     * @var array<string, string>
     */
    public const SORTABLE = [
        'name' => 'companies.name',
        'debitor' => 'companies.debitor_number',
        'specialty' => 'medical_specialties.name',
        'city' => 'addresses.city',
        'contacts' => 'contacts_count',
    ];

    /**
     * @var list<string>
     */
    public const SEARCHABLE = [
        'companies.name',
        'companies.name_addition',
        'companies.debitor_number',
        'addresses.postal_code',
        'addresses.city',
    ];

    /**
     * @return Builder<Company>
     */
    public static function query(): Builder
    {
        return Company::query()
            ->select('companies.*')
            /*
             * Als Liste mit `as`, NICHT als `alias => spalte`: die
             * Schluessel-Form von `addSelect()` erwartet Unterabfragen und
             * liefert bei Spaltennamen stillschweigend nichts.
             */
            ->addSelect([
                'medical_specialties.name as specialty_name',
                'addresses.postal_code as address_postal_code',
                'addresses.city as address_city',
            ])
            /*
             * Zaehlen statt joinen. `withCount` setzt eine korrelierte
             * Unterabfrage je Zeile ab und laesst die Zeilenzahl in Ruhe —
             * anders als ein Join, der `meta.total` zur Zahl der Beziehungen
             * statt der Firmen machen wuerde.
             */
            ->withCount(['contacts', 'locations'])
            ->leftJoin('medical_specialties', function (JoinClause $join): void {
                $join->on('medical_specialties.id', '=', 'companies.medical_specialty_id')
                    ->whereNull('medical_specialties.deleted_at');
            })
            ->leftJoin('addresses', function (JoinClause $join): void {
                $join->on('addresses.addressable_id', '=', 'companies.id')
                    ->where('addresses.addressable_type', '=', Company::class)
                    ->whereNull('addresses.deleted_at');
            });
    }

    /**
     * @return array<string, mixed>
     */
    public static function row(Company $company): array
    {
        $postalCode = $company->getAttribute('address_postal_code');
        $city = $company->getAttribute('address_city');

        return [
            'id' => $company->id,
            'name' => $company->name,
            'nameAddition' => $company->name_addition,
            'debitorNumber' => $company->debitor_number,
            'specialty' => $company->getAttribute('specialty_name'),
            'city' => $city === null ? null : trim("{$postalCode} {$city}"),
            'contactCount' => (int) $company->getAttribute('contacts_count'),
            'locationCount' => (int) $company->getAttribute('locations_count'),
            'avvSigned' => $company->avv_status === AvvStatus::Signed,
        ];
    }
}
