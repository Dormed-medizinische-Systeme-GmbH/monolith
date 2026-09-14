<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Models;

use App\Support\TracksBlame;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Ein Eintrag der Werteliste eines `select`-Feldes (D-134).
 *
 * Werte referenzieren diese Zeile, sie kopieren sie nicht — eine Umbenennung
 * wirkt deshalb ueberall, statt in kopierten Zeichenketten zurueckzubleiben.
 *
 * @property string $id
 * @property string $value
 * @property string $label
 */
final class ArticleGroupFieldOption extends Model
{
    use HasUuids, SoftDeletes, TracksBlame;

    protected $attributes = ['is_active' => true];

    protected $fillable = ['field_id', 'value', 'label', 'position', 'is_active'];

    /**
     * @return BelongsTo<ArticleGroupField, $this>
     */
    public function field(): BelongsTo
    {
        return $this->belongsTo(ArticleGroupField::class, 'field_id');
    }

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'position' => 'integer'];
    }
}
