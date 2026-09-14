<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Models;

use App\Modules\Inventory\Enums\FieldScope;
use App\Modules\Inventory\Enums\FieldType;
use App\Support\TracksBlame;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Ein benutzerdefiniertes Feld einer Artikelgruppe (D-100/D-111/D-119).
 *
 * `type` und `scope` sind nach dem Anlegen faktisch unveraenderlich: die
 * Wertetabellen fuehren beide mit und binden sie ueber einen zusammengesetzten
 * Fremdschluessel. Eine Aenderung waere nur moeglich, solange noch kein Wert
 * existiert — Postgres verhindert den Rest.
 *
 * @property string $id
 * @property string $key
 * @property FieldType $type
 * @property FieldScope $scope
 */
final class ArticleGroupField extends Model
{
    use HasUuids, SoftDeletes, TracksBlame;

    protected $attributes = ['is_mandatory' => false];

    protected $fillable = [
        'article_group_id', 'key', 'label', 'type', 'scope',
        'is_mandatory', 'validation_regex', 'position',
    ];

    /**
     * @return BelongsTo<ArticleGroup, $this>
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(ArticleGroup::class, 'article_group_id');
    }

    /**
     * Werteliste bei `type = select` (D-134).
     *
     * @return HasMany<ArticleGroupFieldOption, $this>
     */
    public function options(): HasMany
    {
        return $this->hasMany(ArticleGroupFieldOption::class, 'field_id')
            ->orderBy('position')
            ->orderBy('label');
    }

    /**
     * @return HasMany<ArticleFieldValue, $this>
     */
    public function values(): HasMany
    {
        return $this->hasMany(ArticleFieldValue::class, 'field_id');
    }

    protected function casts(): array
    {
        return [
            'type' => FieldType::class,
            'scope' => FieldScope::class,
            'is_mandatory' => 'boolean',
            'position' => 'integer',
        ];
    }
}
