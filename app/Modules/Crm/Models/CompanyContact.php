<?php

namespace App\Modules\Crm\Models;

use App\Support\TracksBlame;
use Database\Factories\Crm\CompanyContactFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['company_id', 'person_id', 'role', 'is_primary'])]
class CompanyContact extends Model
{
    /** @use HasFactory<CompanyContactFactory> */
    use HasFactory, TracksBlame;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_primary' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<Company, $this>
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * @return BelongsTo<Person, $this>
     */
    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }

    protected static function newFactory(): Factory
    {
        return CompanyContactFactory::new();
    }
}
