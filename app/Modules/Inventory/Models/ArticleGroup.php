<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Models;

use App\Support\TracksBlame;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Artikelgruppe — hierarchisch, ohne Feldvererbung (D-110).
 *
 * `parent` dient NUR Navigation und Filterung. Das effektive Feldset eines
 * Artikels ist `fields` DIESER Gruppe — Obergruppen steuern nichts bei. Wer
 * hier eine Vererbung einbaut, dreht D-110 um.
 *
 * @property string $id
 * @property string $name
 */
final class ArticleGroup extends Model
{
    use HasUuids, SoftDeletes, TracksBlame;

    protected $attributes = ['is_active' => true];

    protected $fillable = ['parent_id', 'name', 'description', 'position', 'is_active'];

    /**
     * @return BelongsTo<ArticleGroup, $this>
     */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    /**
     * @return HasMany<ArticleGroup, $this>
     */
    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('position')->orderBy('name');
    }

    /**
     * @return HasMany<Article, $this>
     */
    public function articles(): HasMany
    {
        return $this->hasMany(Article::class);
    }

    /**
     * Der Feldkatalog dieser Gruppe (D-100).
     *
     * @return HasMany<ArticleGroupField, $this>
     */
    public function fields(): HasMany
    {
        return $this->hasMany(ArticleGroupField::class)->orderBy('position')->orderBy('label');
    }

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'position' => 'integer'];
    }
}
