<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Models;

use App\Modules\Inventory\Enums\FieldScope;
use App\Modules\Inventory\Enums\FieldType;
use App\Support\TracksBlame;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Der Wert eines benutzerdefinierten Feldes an einem Artikel (D-134).
 *
 * Geschrieben wird ueber `ArticleFieldValues` im Modul, nicht direkt: `type`
 * und `scope` muessen aus der Felddefinition uebernommen werden, und die
 * richtige der sieben Wertespalten muss getroffen werden. Beides prueft
 * Postgres — direkt geschrieben faengt man sich hier nur Constraint-Fehler.
 *
 * @property string $id
 * @property FieldType $type
 * @property FieldScope $scope
 */
final class ArticleFieldValue extends Model
{
    use HasUuids, SoftDeletes, TracksBlame;

    protected $fillable = [
        'article_id', 'field_id', 'type', 'scope',
        'value_string', 'value_text', 'value_integer', 'value_decimal',
        'value_date', 'value_boolean', 'value_option_id',
    ];

    /**
     * @return BelongsTo<Article, $this>
     */
    public function article(): BelongsTo
    {
        return $this->belongsTo(Article::class);
    }

    /**
     * @return BelongsTo<ArticleGroupField, $this>
     */
    public function field(): BelongsTo
    {
        return $this->belongsTo(ArticleGroupField::class, 'field_id');
    }

    /**
     * @return BelongsTo<ArticleGroupFieldOption, $this>
     */
    public function option(): BelongsTo
    {
        return $this->belongsTo(ArticleGroupFieldOption::class, 'value_option_id');
    }

    /**
     * Der gesetzte Wert, unabhaengig davon, in welcher Spalte er steht.
     */
    public function value(): mixed
    {
        return $this->getAttribute($this->type->column());
    }

    protected function casts(): array
    {
        return [
            'type' => FieldType::class,
            'scope' => FieldScope::class,
            'value_integer' => 'integer',
            'value_decimal' => 'decimal:4',
            'value_date' => 'date',
            'value_boolean' => 'boolean',
        ];
    }
}
