<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Queries;

use App\Modules\Inventory\Enums\FieldScope;
use App\Modules\Inventory\Models\Article;
use App\Modules\Inventory\Models\ArticleFieldValue;
use App\Modules\Inventory\Models\ArticleGroup;
use App\Modules\Inventory\Models\ArticleGroupField;

/**
 * Die Detailansicht eines Artikels.
 *
 * Der interessante Teil ist `merkmale`: der Feldkatalog kommt aus der GRUPPE,
 * die Werte vom Artikel. Deshalb werden hier die Felder aufgezaehlt und nicht
 * die Werte — ein Pflichtfeld ohne Wert muss sichtbar leer sein und darf nicht
 * einfach fehlen.
 *
 * Felder mit `scope = item` bleiben aussen vor: die haengen am Exemplar
 * (D-119), und Exemplare gibt es noch nicht.
 */
final class ArticleProfile
{
    /**
     * @return array<string, mixed>
     */
    public static function for(Article $article): array
    {
        $article->load([
            'group.parent',
            'group.fields.options',
            'fieldValues.option',
        ]);

        return [
            'id' => $article->id,
            'name' => $article->name,
            'articleNumber' => $article->article_number,
            'description' => $article->description,
            'notes' => $article->notes,

            'group' => [
                'id' => $article->group->id,
                'name' => $article->group->name,
                'path' => self::path($article->group),
            ],

            'flags' => [
                'active' => $article->is_active,
                'public' => $article->is_public,
                'orderable' => $article->is_orderable,
                'serialTracked' => $article->is_serial_tracked,
                'serviceItem' => $article->is_service_item,
            ],

            'preise' => array_filter([
                'Verkauf' => Money::format($article->sale_price),
                'Einkauf' => Money::format($article->purchase_price),
                'Einheit' => $article->unit,
                'Steuer' => $article->tax_category->label().' ('.rtrim(rtrim((string) $article->tax_rate, '0'), '.').' %)',
                'Kostenstelle' => $article->cost_center,
            ], fn (?string $value): bool => $value !== null && $value !== ''),

            'stammdaten' => array_filter([
                'Hersteller' => $article->manufacturer,
                'Modell' => $article->model_name,
                'Herstellernummer' => $article->manufacturer_article_number,
                'EAN' => $article->ean,
                'Gewicht' => $article->weight_kg === null ? null : rtrim(rtrim((string) $article->weight_kg, '0'), '.').' kg',
                'Mindestbestand' => $article->min_stock === null ? null : rtrim(rtrim((string) $article->min_stock, '0'), '.').' '.$article->unit,
            ], fn (?string $value): bool => $value !== null && $value !== ''),

            'merkmale' => self::merkmale($article),
        ];
    }

    /**
     * Der Feldkatalog der Gruppe, je Feld mit dem Wert dieses Artikels.
     *
     * @return list<array<string, mixed>>
     */
    private static function merkmale(Article $article): array
    {
        $werte = $article->fieldValues->keyBy('field_id');

        return $article->group->fields
            ->filter(fn (ArticleGroupField $field): bool => $field->scope === FieldScope::Article)
            ->values()
            ->map(function (ArticleGroupField $field) use ($werte): array {
                /** @var ArticleFieldValue|null $wert */
                $wert = $werte->get($field->id);

                return [
                    'id' => $field->id,
                    'label' => $field->label,
                    'mandatory' => $field->is_mandatory,
                    'value' => self::darstellen($field, $wert),
                ];
            })
            ->all();
    }

    /**
     * `select` zeigt die Beschriftung der Option, nicht ihren technischen Wert —
     * und liest sie aus der Option, statt sie kopiert zu haben (D-134).
     */
    private static function darstellen(ArticleGroupField $field, ?ArticleFieldValue $wert): ?string
    {
        if ($wert === null) {
            return null;
        }

        $roh = $wert->value();

        return match (true) {
            $roh === null => null,
            $wert->option !== null => $wert->option->label,
            is_bool($roh) => $roh ? 'ja' : 'nein',
            $roh instanceof \DateTimeInterface => $roh->format('d.m.Y'),
            default => (string) $roh,
        };
    }

    /**
     * Der Pfad im Gruppenbaum — nur Navigation, keine Vererbung (D-110).
     *
     * Laedt je Ebene nach. Bei einer Detailseite und einem Baum von zwei bis
     * drei Ebenen sind das zwei bis drei Abfragen; erst wenn der Baum tief wird
     * oder der Pfad in eine LISTE muss, lohnt eine rekursive Abfrage.
     *
     * @return list<string>
     */
    private static function path(ArticleGroup $group): array
    {
        $pfad = [];

        for ($knoten = $group; $knoten !== null; $knoten = $knoten->parent) {
            array_unshift($pfad, $knoten->name);
        }

        return $pfad;
    }
}
