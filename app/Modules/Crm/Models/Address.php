<?php

namespace App\Modules\Crm\Models;

use App\Support\TracksBlame;
use Database\Factories\Crm\AddressFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

#[Fillable(['street', 'house_number', 'postal_code', 'city', 'country_code'])]
class Address extends Model
{
    /** @use HasFactory<AddressFactory> */
    use HasFactory, TracksBlame;

    /**
     * Mirrors the migration default so an unsaved address reports its country.
     *
     * @var array<string, string>
     */
    protected $attributes = [
        'country_code' => 'DE',
    ];

    /**
     * @return MorphTo<Model, $this>
     */
    public function addressable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * @return list<string>
     */
    public function toLines(): array
    {
        return array_values(array_filter([
            trim("{$this->street} {$this->house_number}"),
            trim("{$this->postal_code} {$this->city}"),
            $this->country_code === 'DE' ? null : $this->country_code,
        ]));
    }

    protected static function newFactory(): Factory
    {
        return AddressFactory::new();
    }
}
