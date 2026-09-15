<?php

declare(strict_types=1);

namespace App\Modules\Crm\Services;

use App\Modules\Crm\Models\Company;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Schreibzugriffe auf den Firmenstamm.
 *
 * `responsible_sales_id` und `responsible_service_id` sind INFORMATIV (D-016):
 * sie sagen, wer zustaendig ist, nicht wer zugreifen darf. Berechtigungen
 * kommen ausschliesslich aus der Rolle (D-030). Wer hier eine Pruefung
 * einbaut, dreht die Entscheidung um.
 */
final class Companies
{
    /**
     * @param  array<string, mixed>  $attributes
     * @param  array<string, mixed>|null  $address
     */
    public static function create(array $attributes, ?array $address = null): Company
    {
        return DB::transaction(function () use ($attributes, $address): Company {
            $company = Company::query()->create($attributes);

            self::writeAddress($company, $address);

            /*
             * Jede Company hat mindestens einen Standort (D-007/D-078). Der
             * entsteht bei der Anlage automatisch mit einer Kopie der
             * Sitzadresse und ist danach unabhaengig bearbeitbar.
             */
            Locations::create($company, ['name' => 'Hauptstandort', 'is_primary' => true], $address);

            return $company;
        });
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @param  array<string, mixed>|null  $address
     */
    public static function update(Company $company, array $attributes, ?array $address = null): Company
    {
        return DB::transaction(function () use ($company, $attributes, $address): Company {
            /*
             * Eine Firma kann nicht ihr eigener Rechnungsempfaenger sein — das
             * waere eine Kette ohne Ende (D-004/D-066).
             */
            if (($attributes['billing_company_id'] ?? null) === $company->id) {
                throw new RuntimeException(
                    'Eine Firma kann nicht ihr eigener Rechnungsempfänger sein.'
                );
            }

            $company->update($attributes);

            self::writeAddress($company, $address);

            return $company;
        });
    }

    public static function delete(Company $company): void
    {
        // `SoftDeletes` (D-018): Standorte, Kontakte und spaeter Rechnungen
        // zeigen weiter auf den Datensatz und laufen nicht ins Leere.
        $company->delete();
    }

    /**
     * @param  array<string, mixed>|null  $address
     */
    private static function writeAddress(Company $company, ?array $address): void
    {
        if ($address === null || array_filter($address) === []) {
            return;
        }

        // Genau eine Sitzadresse je Firma (D-003/D-014).
        $company->address()->updateOrCreate([], $address);
    }
}
