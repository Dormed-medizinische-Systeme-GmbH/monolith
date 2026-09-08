<?php

namespace App\Modules\Crm\Models;

use App\Policies\PersonPolicy;
use App\Support\TracksBlame;
use Database\Factories\Crm\PersonFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\UsePolicy;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['first_name', 'last_name', 'email', 'phone', 'notes'])]
#[UsePolicy(PersonPolicy::class)]
class Person extends Model
{
    /** @use HasFactory<PersonFactory> */
    use HasFactory, TracksBlame;

    /**
     * @return Attribute<string, never>
     */
    protected function fullName(): Attribute
    {
        return Attribute::get(fn (): string => trim("{$this->first_name} {$this->last_name}"));
    }

    /**
     * @return HasMany<CompanyContact, $this>
     */
    public function contacts(): HasMany
    {
        return $this->hasMany(CompanyContact::class);
    }

    /**
     * @return BelongsToMany<Company, $this>
     */
    public function companies(): BelongsToMany
    {
        return $this->belongsToMany(Company::class, 'company_contacts')
            ->withPivot(['role', 'is_primary'])
            ->withTimestamps();
    }

    protected static function newFactory(): Factory
    {
        return PersonFactory::new();
    }
}
