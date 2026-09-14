<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Services;

use App\Modules\Inventory\Enums\FieldScope;
use App\Modules\Inventory\Enums\FieldType;
use App\Modules\Inventory\Models\Article;
use App\Modules\Inventory\Models\ArticleFieldValue;
use App\Modules\Inventory\Models\ArticleGroupField;
use App\Modules\Inventory\Models\ArticleGroupFieldOption;
use InvalidArgumentException;

/**
 * Schreibt Werte benutzerdefinierter Felder an einen Artikel (D-134).
 *
 * Existiert, weil ein solcher Wert nicht in eine Spalte gehoert, sondern in
 * EINE VON SIEBEN — und zwar die, die zum Typ des Feldes passt. Dazu muessen
 * `type` und `scope` aus der Definition uebernommen werden, sonst greift der
 * zusammengesetzte Fremdschluessel nicht.
 *
 * Postgres prueft das alles (Migration `create_article_field_values_table`).
 * Diese Klasse existiert nicht, WEIL die Datenbank es nicht koennte, sondern
 * damit der Aufrufer nicht raten muss — und damit die Fehlermeldung fachlich
 * ist statt eine Constraint-Verletzung.
 */
final class ArticleFieldValues
{
    /**
     * Setzt den Wert eines Feldes. Ein vorhandener Wert wird ersetzt.
     */
    public static function set(Article $article, ArticleGroupField $field, mixed $value): ArticleFieldValue
    {
        self::guard($article, $field, $value);

        return ArticleFieldValue::query()->updateOrCreate(
            ['article_id' => $article->id, 'field_id' => $field->id],
            [
                'type' => $field->type,
                'scope' => $field->scope,
                // Alle sieben zuruecksetzen: bliebe ein alter Wert stehen,
                // schluege der „genau eine Spalte"-CHECK zu.
                ...array_fill_keys(
                    array_map(fn (FieldType $type): string => $type->column(), FieldType::cases()),
                    null,
                ),
                $field->type->column() => self::normalise($value),
            ],
        );
    }

    private static function guard(Article $article, ArticleGroupField $field, mixed $value): void
    {
        if ($field->article_group_id !== $article->article_group_id) {
            throw new InvalidArgumentException(
                "Das Feld „{$field->label}\" gehoert nicht zur Artikelgruppe dieses Artikels."
            );
        }

        /*
         * `scope = item` haengt am Exemplar, nicht am Artikel — der Wert
         * gehoerte dann in `device_field_values` (D-119/D-134).
         */
        if ($field->scope !== FieldScope::Article) {
            throw new InvalidArgumentException(
                "Das Feld „{$field->label}\" wird je Exemplar erfasst, nicht je Artikel."
            );
        }

        if ($field->is_mandatory && ($value === null || $value === '')) {
            throw new InvalidArgumentException("Das Feld „{$field->label}\" ist ein Pflichtfeld.");
        }

        /*
         * Das optionale Muster aus D-111. Die Durchsetzung liegt hier und nicht
         * in einem CHECK: ein CHECK-Ausdruck darf keine Unterabfrage enthalten
         * und kaeme deshalb an `validation_regex` gar nicht heran.
         */
        if ($field->validation_regex !== null && is_string($value)
            && preg_match('/'.str_replace('/', '\/', $field->validation_regex).'/u', $value) !== 1) {
            throw new InvalidArgumentException(
                "Der Wert fuer „{$field->label}\" entspricht nicht dem hinterlegten Muster."
            );
        }
    }

    /**
     * Bei `select` wird die OPTION referenziert, nicht ihr Text kopiert —
     * sonst bliebe eine Umbenennung in alten Zeilen zurueck (D-134). Deshalb
     * nimmt `set()` dort die Option selbst entgegen und legt ihren Schluessel ab.
     */
    private static function normalise(mixed $value): mixed
    {
        return $value instanceof ArticleGroupFieldOption ? $value->id : $value;
    }
}
