<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Models;

use App\Modules\Inventory\Enums\TaxCategory;
use App\Support\TracksBlame;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Artikel — die Katalogstufe zwischen Gruppe und Exemplar (D-099).
 *
 * Preise sind LISTENPREISE. Beim Einfuegen in eine Position wird der Preis
 * gesnapshottet (D-104); eine spaetere Aenderung hier wirkt nie rueckwirkend.
 *
 * Bestand ist keine Eigenschaft des Artikels, sondern die Summe ueber den
 * Bewegungs-Ledger (D-102) — es wird hier nie eine `stock`-Spalte geben.
 *
 * @property string $id
 * @property string $article_number
 * @property string $name
 * @property TaxCategory $tax_category
 */
final class Article extends Model
{
    use HasUuids, SoftDeletes, TracksBlame;

    /*
     * Dieselben Vorgaben wie die Spalten in der Migration. Ohne sie steht auf
     * einem frisch angelegten Objekt `null`, wo in der Datenbank schon der
     * Vorgabewert greift — und Aufrufer, die vor dem Neuladen lesen, bekaemen
     * eine andere Antwort als danach.
     */
    protected $attributes = [
        'is_serial_tracked' => false,
        'is_service_item' => false,
        'is_active' => true,
        'is_public' => false,
        'is_orderable' => false,
        'tax_category' => 'standard_19',
        'tax_rate' => 19,
        'sale_price' => 0,
    ];

    protected $fillable = [
        'article_group_id', 'article_number', 'name', 'description',
        'is_serial_tracked', 'is_service_item', 'is_active', 'is_public', 'is_orderable',
        'unit', 'cost_center', 'tax_category', 'tax_rate',
        'sale_price', 'purchase_price',
        'manufacturer', 'model_name', 'manufacturer_article_number',
        'ean', 'weight_kg', 'min_stock', 'notes',
    ];

    /**
     * @return BelongsTo<ArticleGroup, $this>
     */
    public function group(): BelongsTo
    {
        return $this->belongsTo(ArticleGroup::class, 'article_group_id');
    }

    /**
     * Die Werte der benutzerdefinierten Felder mit `scope = article` (D-134).
     *
     * @return HasMany<ArticleFieldValue, $this>
     */
    public function fieldValues(): HasMany
    {
        return $this->hasMany(ArticleFieldValue::class);
    }

    protected function casts(): array
    {
        return [
            'is_serial_tracked' => 'boolean',
            'is_service_item' => 'boolean',
            'is_active' => 'boolean',
            'is_public' => 'boolean',
            'is_orderable' => 'boolean',
            'tax_category' => TaxCategory::class,
            'tax_rate' => 'decimal:2',
            'sale_price' => 'decimal:2',
            'purchase_price' => 'decimal:2',
            'weight_kg' => 'decimal:3',
            'min_stock' => 'decimal:2',
        ];
    }
}
