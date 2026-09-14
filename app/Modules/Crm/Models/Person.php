<?php

declare(strict_types=1);

namespace App\Modules\Crm\Models;

use App\Modules\Crm\Enums\Gender;
use App\Support\TracksBlame;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Eine reale Person (CORE.md).
 *
 * Nie eigenstaendig — Anlage immer im Kontext einer Company (D-002), und ohne
 * eigene Adresse (D-014). Die Briefanrede wird bei der Dokumenterstellung
 * erzeugt, nicht gespeichert (D-015).
 *
 * @property int $id
 * @property string $first_name
 * @property string $last_name
 * @property Gender $gender
 */
final class Person extends Model
{
    use SoftDeletes, TracksBlame;

    protected $fillable = ['first_name', 'last_name', 'name_suffix', 'title', 'gender', 'locale'];

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
            ->withPivot(['role', 'department', 'is_primary'])
            ->withTimestamps();
    }

    /**
     * @return MorphMany<ContactChannel, $this>
     */
    public function contactChannels(): MorphMany
    {
        return $this->morphMany(ContactChannel::class, 'channelable');
    }

    /**
     * @return HasMany<Consent, $this>
     */
    public function consents(): HasMany
    {
        return $this->hasMany(Consent::class);
    }

    /**
     * Der Portal-/Shop-Zugang dieser Person (ADR-037/042). Die meisten Personen
     * haben keinen — deshalb eine eigene Tabelle statt Spalten hier.
     *
     * @return HasOne<CustomerAccount, $this>
     */
    public function customerAccount(): HasOne
    {
        return $this->hasOne(CustomerAccount::class);
    }

    /**
     * @return Attribute<string, never>
     */
    protected function fullName(): Attribute
    {
        return Attribute::get(fn (): string => trim(implode(' ', array_filter([
            $this->title, $this->first_name, $this->last_name,
        ]))));
    }

    protected function casts(): array
    {
        return ['gender' => Gender::class];
    }
}
