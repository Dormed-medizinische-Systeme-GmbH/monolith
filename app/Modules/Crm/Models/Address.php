<?php

declare(strict_types=1);

namespace App\Modules\Crm\Models;

use App\Modules\Crm\Enums\GeocodeStatus;
use App\Support\TracksBlame;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Polymorph an Company (Sitz) und Location — genau EINE je Besitzer
 * (D-003/D-014). Eine Person hat keine eigene Adresse.
 *
 * `latitude`/`longitude` sind kein Beiwerk: sie sind die Grundlage der
 * Fahrtzone und damit der Service-Anfahrtspauschale (D-024/D-020).
 *
 * @property int $id
 * @property string $street
 * @property string $postal_code
 * @property string $city
 */
final class Address extends Model
{
    use SoftDeletes, TracksBlame;

    protected $fillable = [
        'street', 'house_number', 'postal_code', 'city', 'district', 'state',
        'country_code', 'po_box', 'po_box_postal_code', 'po_box_city',
        'latitude', 'longitude', 'geocode_status', 'verified_at', 'verified_by',
    ];

    /**
     * @return MorphTo<Model, $this>
     */
    public function addressable(): MorphTo
    {
        return $this->morphTo();
    }

    protected function casts(): array
    {
        return [
            'geocode_status' => GeocodeStatus::class,
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'verified_at' => 'datetime',
        ];
    }
}
