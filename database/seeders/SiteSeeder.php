<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Modules\Core\Models\Site;
use Illuminate\Database\Seeder;

/**
 * Die eigenen Standorte von Dormed, abgeleitet aus
 * `database/seeders/locations/`.
 *
 * Das Verzeichnis ist die Quelle, nicht eine Liste hier: aus
 * `Buchholz-outside.jpg` wird der Standort „Buchholz" samt Aussenansicht.
 * Kommt ein Bild dazu, kommt der Standort mit — dieselbe Ueberlegung wie beim
 * Mitarbeiterseed und im `ObjectStorageSeeder`.
 *
 * **Laeuft ueberall, auch in Produktion.** Anders als die Entwicklungszugaenge
 * sind das keine Beispieldaten: ohne Standort haette kein Mitarbeiter eine
 * Zuordnung. Die Anschriften sind allerdings Platzhalter und gehoeren
 * ausgefuellt.
 */
final class SiteSeeder extends Seeder
{
    /**
     * Anschriften je Standort. Platzhalter — die echten stehen noch aus.
     *
     * @var array<string, array{string, string, string, string}>
     */
    private const ANSCHRIFTEN = [
        'Buchholz' => ['Musterstraße', '1', '21244', 'Buchholz in der Nordheide'],
        'Holzwickede' => ['Musterweg', '2', '59439', 'Holzwickede'],
    ];

    public function run(): void
    {
        foreach ($this->photos() as [$name, $pfad]) {
            [$strasse, $hausnummer, $plz, $ort] = self::ANSCHRIFTEN[$name]
                ?? [null, null, null, null];

            $site = Site::query()->firstOrCreate(
                ['name' => $name],
                [
                    'short_name' => mb_substr($name, 0, 3),
                    'street' => $strasse,
                    'house_number' => $hausnummer,
                    'postal_code' => $plz,
                    'city' => $ort,
                ],
            );

            // Nicht `$fillable` — das Bild kommt aus dem Seed, nicht aus einer Maske.
            $site->photo_path = $pfad;
            $site->save();
        }
    }

    /**
     * Die Aussenansichten aus `database/seeders/locations/`.
     *
     * @return list<array{string, string}> Standortname, Ablagepfad
     */
    private function photos(): array
    {
        $verzeichnis = database_path('seeders/locations');

        if (! is_dir($verzeichnis)) {
            return [];
        }

        $dateien = array_values(array_filter(
            scandir($verzeichnis) ?: [],
            fn (string $name): bool => (bool) preg_match('/^([A-Za-zÄÖÜäöüß-]+)-outside\.(jpe?g|png)$/u', $name),
        ));

        sort($dateien);

        return array_map(function (string $datei): array {
            preg_match('/^([A-Za-zÄÖÜäöüß-]+)-outside\./u', $datei, $treffer);

            return [$treffer[1], 'locations/'.$datei];
        }, $dateien);
    }
}
