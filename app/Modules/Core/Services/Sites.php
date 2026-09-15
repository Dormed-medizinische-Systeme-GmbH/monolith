<?php

declare(strict_types=1);

namespace App\Modules\Core\Services;

use App\Modules\Core\Models\Site;
use RuntimeException;

/**
 * Schreibzugriffe auf die eigenen Standorte.
 */
final class Sites
{
    /**
     * @param  array<string, mixed>  $attributes
     */
    public static function create(array $attributes): Site
    {
        return Site::query()->create($attributes);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    public static function update(Site $site, array $attributes): Site
    {
        $site->update($attributes);

        return $site;
    }

    public static function delete(Site $site): void
    {
        /*
         * Der Fremdschluessel ist `nullOnDelete` — das greift aber nur beim
         * HARTEN Loeschen. `SoftDeletes` (D-018) laesst `users.site_id` stehen,
         * und die Mitarbeiter zeigten dann auf einen ausgeblendeten Standort:
         * in der Liste stuende weiter „Buchholz", in der Auswahl gaebe es ihn
         * nicht mehr. Deshalb hier ein ausdruecklicher Riegel statt einer
         * stillen Inkonsistenz.
         */
        if ($site->users()->exists()) {
            throw new RuntimeException(
                'Dem Standort sind noch Mitarbeiter zugeordnet. Erst umsetzen, dann löschen.'
            );
        }

        $site->delete();
    }
}
