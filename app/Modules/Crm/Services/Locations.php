<?php

declare(strict_types=1);

namespace App\Modules\Crm\Services;

use App\Modules\Crm\Models\Company;
use App\Modules\Crm\Models\Location;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * Schreibzugriffe auf Kundenstandorte.
 *
 * Hier sitzt die Zusatzlogik, die ein Formular allein nicht leisten kann:
 *
 * 1. **Genau ein Hauptstandort je Firma** (CORE.md). Einen zweiten zu setzen
 *    heisst, den bisherigen abzusetzen — sonst gaebe es zwei oder keinen.
 * 2. **Jede Firma hat mindestens einen Standort** (D-007). Der letzte laesst
 *    sich nicht loeschen; Geraete und Servicevertraege haengen daran.
 *
 * Beides betrifft mehrere Zeilen gleichzeitig und laeuft deshalb in einer
 * Transaktion.
 */
final class Locations
{
    /**
     * @param  array<string, mixed>  $attributes
     * @param  array<string, mixed>|null  $address
     */
    public static function create(Company $company, array $attributes, ?array $address = null): Location
    {
        return DB::transaction(function () use ($company, $attributes, $address): Location {
            // Der erste Standort einer Firma IST der Hauptstandort — eine Firma
            // ohne einen waere ein Zustand, den D-007 ausschliesst.
            $erster = ! $company->locations()->exists();

            $location = $company->locations()->create([
                ...$attributes,
                'is_primary' => $erster || ($attributes['is_primary'] ?? false),
            ]);

            self::syncPrimary($location);
            self::writeAddress($location, $address);

            return $location;
        });
    }

    /**
     * @param  array<string, mixed>  $attributes
     * @param  array<string, mixed>|null  $address
     */
    public static function update(Location $location, array $attributes, ?array $address = null): Location
    {
        return DB::transaction(function () use ($location, $attributes, $address): Location {
            /*
             * Den Haken beim einzigen Hauptstandort zu entfernen hinterliesse
             * eine Firma ohne einen. Umgesetzt wird er, indem man ihn woanders
             * setzt — nicht, indem man ihn hier wegnimmt.
             */
            if ($location->is_primary && ($attributes['is_primary'] ?? false) === false) {
                throw new RuntimeException(
                    'Jede Firma braucht einen Hauptstandort. Setzen Sie ihn bei einem anderen Standort, dann wechselt er.'
                );
            }

            $location->update($attributes);

            self::syncPrimary($location);
            self::writeAddress($location, $address);

            return $location;
        });
    }

    public static function delete(Location $location): void
    {
        DB::transaction(function () use ($location): void {
            $uebrige = Location::query()
                ->where('company_id', $location->company_id)
                ->whereKeyNot($location->getKey());

            if (! $uebrige->exists()) {
                throw new RuntimeException(
                    'Der letzte Standort einer Firma kann nicht gelöscht werden (D-007).'
                );
            }

            // War es der Hauptstandort, rueckt der naechste nach — sonst haette
            // die Firma keinen mehr.
            if ($location->is_primary) {
                $uebrige->orderBy('name')->first()?->update(['is_primary' => true]);
            }

            $location->delete();
        });
    }

    /**
     * Setzt alle anderen Standorte derselben Firma zurueck.
     */
    private static function syncPrimary(Location $location): void
    {
        if (! $location->is_primary) {
            return;
        }

        Location::query()
            ->where('company_id', $location->company_id)
            ->whereKeyNot($location->getKey())
            ->update(['is_primary' => false]);
    }

    /**
     * @param  array<string, mixed>|null  $address
     */
    private static function writeAddress(Location $location, ?array $address): void
    {
        if ($address === null || array_filter($address) === []) {
            return;
        }

        // Genau eine Adresse je Standort (D-003/D-014, unique auf `addressable`).
        $location->address()->updateOrCreate([], $address);
    }
}
