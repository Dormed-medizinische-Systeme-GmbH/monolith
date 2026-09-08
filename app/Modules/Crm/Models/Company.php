<?php

namespace App\Modules\Crm\Models;

use App\Policies\CompanyPolicy;
use App\Support\TracksBlame;
use Database\Factories\Crm\CompanyFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;

#[Fillable(['name', 'legal_name', 'notes'])]
#[UsePolicy(CompanyPolicy::class)]
class Company extends Model
{
    /** @use HasFactory<CompanyFactory> */
    use HasFactory, TracksBlame;

    /**
     * @return MorphOne<Address, $this>
     */
    public function address(): MorphOne
    {
        return $this->morphOne(Address::class, 'addressable');
    }

    /**
     * @return HasMany<Location, $this>
     */
    public function locations(): HasMany
    {
        return $this->hasMany(Location::class);
    }

    /**
     * @return HasMany<CompanyContact, $this>
     */
    public function contacts(): HasMany
    {
        return $this->hasMany(CompanyContact::class);
    }

    /**
     * @return BelongsToMany<Person, $this>
     */
    public function people(): BelongsToMany
    {
        return $this->belongsToMany(Person::class, 'company_contacts')
            ->withPivot(['role', 'is_primary'])
            ->withTimestamps();
    }

    protected static function newFactory(): Factory
    {
        return CompanyFactory::new();
    }
}
