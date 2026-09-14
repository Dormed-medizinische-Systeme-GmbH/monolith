<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Modules\Inventory\Enums\FieldScope;
use App\Modules\Inventory\Enums\FieldType;
use App\Modules\Inventory\Models\Article;
use App\Modules\Inventory\Models\ArticleGroup;
use App\Modules\Inventory\Models\ArticleGroupField;
use App\Modules\Inventory\Services\ArticleFieldValues;
use Illuminate\Database\Seeder;

/**
 * Ein kleiner Artikelkatalog zum Ausprobieren.
 *
 * **Laeuft nur ausserhalb von Produktion.** Die Daten sind an echte Geraete
 * angelehnt, aber Platzhalter — Preise und Nummern sind erfunden. Der echte
 * Artikelstamm liegt in Sage/KHK und ist derzeit nicht beschaffbar (D-108);
 * genau deshalb ist der benutzerdefinierte Feldkatalog hier kein Komfort,
 * sondern das Sicherheitsnetz.
 *
 * Die Merkmale der Gruppe „Standgeraete" sind bewusst denen nachgebildet, die
 * auf den Produktseiten von dormed.de ohnehin schon stehen (Plattform,
 * Monitor, Schallkopfanschluesse, Klasse) — dort als Markdown, hier als Daten.
 */
final class ArticleCatalogSeeder extends Seeder
{
    public function run(): void
    {
        if (app()->isProduction()) {
            $this->command?->getOutput()->writeln(
                '  <fg=yellow>Beispielkatalog uebersprungen (Produktion).</>'
            );

            return;
        }

        $ultraschall = ArticleGroup::query()->firstOrCreate(
            ['name' => 'Ultraschall'],
            ['position' => 1],
        );

        $stand = $this->gruppe('Standgeräte', $ultraschall, 1);
        $mobil = $this->gruppe('Mobile Geräte', $ultraschall, 2);
        $zubehoer = ArticleGroup::query()->firstOrCreate(['name' => 'Zubehör'], ['position' => 2]);

        $felder = $this->felder($stand);

        $this->artikel($stand, [
            'article_number' => 'US-1001',
            'name' => 'Mindray Resona i9',
            'manufacturer' => 'Mindray',
            'model_name' => 'Resona i9',
            'unit' => 'Stk',
            'sale_price' => 48900,
            'purchase_price' => 35200,
            'is_serial_tracked' => true,
            'is_public' => true,
            'description' => 'Stationäres Premium-Ultraschallsystem. Platzhalter aus dem Entwicklungs-Seed.',
        ], $felder, ['ZST+', '21,5 Zoll Full HD', 5, 'premium']);

        $this->artikel($stand, [
            'article_number' => 'US-1002',
            'name' => 'Mindray Consona N8',
            'manufacturer' => 'Mindray',
            'model_name' => 'Consona N8',
            'unit' => 'Stk',
            'sale_price' => 29900,
            'purchase_price' => 21400,
            'is_serial_tracked' => true,
            'is_public' => true,
        ], $felder, ['Consona', '21,5 Zoll', 4, 'mittelklasse']);

        $this->artikel($mobil, [
            'article_number' => 'US-2001',
            'name' => 'Mindray TE 7 Ace',
            'manufacturer' => 'Mindray',
            'model_name' => 'TE 7 Ace',
            'unit' => 'Stk',
            'sale_price' => 18900,
            'is_serial_tracked' => true,
            'is_public' => true,
            'is_orderable' => true,
        ]);

        // Servicerelevantes Kleinteil: kein Exemplar, aber im Artikel-Tab des
        // Technikers (D-109).
        $this->artikel($zubehoer, [
            'article_number' => 'ZB-3001',
            'name' => 'Netzkabel C13, 2 m',
            'unit' => 'Stk',
            'sale_price' => 14.9,
            'purchase_price' => 4.2,
            'is_service_item' => true,
            'min_stock' => 20,
        ]);

        // Auslaufmodell: im Katalog, aber nirgends sichtbar.
        $this->artikel($zubehoer, [
            'article_number' => 'ZB-3002',
            'name' => 'Thermopapier, Rolle',
            'unit' => 'Rolle',
            'sale_price' => 6.5,
            'is_service_item' => true,
            'is_active' => false,
        ]);
    }

    private function gruppe(string $name, ArticleGroup $parent, int $position): ArticleGroup
    {
        return ArticleGroup::query()->firstOrCreate(
            ['name' => $name],
            ['parent_id' => $parent->id, 'position' => $position],
        );
    }

    /**
     * Der Feldkatalog der Gruppe „Standgeraete" (D-100/D-111).
     *
     * @return array<string, ArticleGroupField>
     */
    private function felder(ArticleGroup $gruppe): array
    {
        $definitionen = [
            ['plattform', 'Plattform', FieldType::String, 1, true],
            ['monitor', 'Monitor', FieldType::String, 2, false],
            ['ports', 'Schallkopfanschlüsse', FieldType::Integer, 3, false],
            ['klasse', 'Klasse', FieldType::Select, 4, false],
        ];

        $felder = [];

        foreach ($definitionen as [$key, $label, $type, $position, $mandatory]) {
            $felder[$key] = ArticleGroupField::query()->firstOrCreate(
                ['article_group_id' => $gruppe->id, 'key' => $key],
                [
                    'label' => $label,
                    'type' => $type,
                    'scope' => FieldScope::Article,
                    'is_mandatory' => $mandatory,
                    'position' => $position,
                ],
            );
        }

        foreach ([['premium', 'Premium'], ['mittelklasse', 'Mittelklasse'], ['einstieg', 'Einstieg']] as $i => [$value, $label]) {
            $felder['klasse']->options()->firstOrCreate(
                ['value' => $value],
                ['label' => $label, 'position' => $i + 1],
            );
        }

        return $felder;
    }

    /**
     * @param  array<string, mixed>  $attribute
     * @param  array<string, ArticleGroupField>  $felder
     * @param  list<mixed>  $werte  Plattform, Monitor, Ports, Klasse
     */
    private function artikel(ArticleGroup $gruppe, array $attribute, array $felder = [], array $werte = []): void
    {
        $artikel = Article::query()->firstOrCreate(
            ['article_number' => $attribute['article_number']],
            ['article_group_id' => $gruppe->id, ...$attribute],
        );

        if ($werte === []) {
            return;
        }

        [$plattform, $monitor, $ports, $klasse] = $werte;

        ArticleFieldValues::set($artikel, $felder['plattform'], $plattform);
        ArticleFieldValues::set($artikel, $felder['monitor'], $monitor);
        ArticleFieldValues::set($artikel, $felder['ports'], $ports);
        ArticleFieldValues::set(
            $artikel,
            $felder['klasse'],
            $felder['klasse']->options()->where('value', $klasse)->firstOrFail(),
        );
    }
}
