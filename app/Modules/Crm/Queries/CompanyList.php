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
 * Fachrichtung. Ansprechpartner werden deshalb gar nicht mitgejoint — ein Join auf
 * `company_contacts` machte aus einer Praxis mit sieben Kontakten sieben
 * Zeilen. Die Personen stehen in der Detailansicht
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
        'specialty' => 'medical_specialties.name',
        'street' => 'addresses.street',
        'city' => 'addresses.city',
    ];

    /**
     * Die Kundennummer steht nicht in der Tabelle, bleibt aber durchsuchbar:
     * sie ist das, womit in Buchhaltung und Lager nach einer Praxis gefragt
     * wird. Wer sie eintippt, weiss, was er sucht.
     *
     * @var list<string>
     */
    public const SEARCHABLE = [
        'companies.name',
        'companies.name_addition',
        'companies.debitor_number',
        'addresses.street',
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
                'addresses.street as address_street',
                'addresses.house_number as address_house_number',
                'addresses.postal_code as address_postal_code',
                'addresses.city as address_city',
            ])
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
        $street = $company->getAttribute('address_street');
        $houseNumber = $company->getAttribute('address_house_number');
        $postalCode = $company->getAttribute('address_postal_code');
        $city = $company->getAttribute('address_city');

        return [
            'id' => $company->id,
            'name' => $company->name,
            'nameAddition' => $company->name_addition,
            'specialty' => $company->getAttribute('specialty_name'),
            'street' => $street === null ? null : trim("{$street} {$houseNumber}"),
            'city' => $city === null ? null : trim("{$postalCode} {$city}"),
            'avvSigned' => $company->avv_status === AvvStatus::Signed,
        ];
    }
}
