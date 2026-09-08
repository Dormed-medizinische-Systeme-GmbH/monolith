<?php

namespace App\Modules\Crm\Models;

use App\Support\TracksBlame;
use Database\Factories\Crm\LocationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphOne;

#[Fillable(['company_id', 'name', 'notes'])]
class Location extends Model
{
    /** @use HasFactory<LocationFactory> */
    use HasFactory, TracksBlame;

    /**
     * @return BelongsTo<Company, $this>
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * @return MorphOne<Address, $this>
     */
    public function address(): MorphOne
    {
        return $this->morphOne(Address::class, 'addressable');
    }

    protected static function newFactory(): Factory
    {
        return LocationFactory::new();
    }
}
