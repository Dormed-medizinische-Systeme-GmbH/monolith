<?php

declare(strict_types=1);

namespace App\Modules\Crm\Models;

use App\Modules\Core\Models\User;
use App\Modules\Crm\Enums\AvvStatus;
use App\Support\TracksBlame;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\Relations\MorphOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Die juristische/organisatorische Kunden-Einheit (CORE.md).
 *
 * Aktuell ausschliesslich Kunden (D-008). Flach — keine Hierarchie ausser der
 * Rechnungsempfaenger-Beziehung (D-006).
 *
 * `responsible_sales_id` und `responsible_service_id` sind INFORMATIV (D-016):
 * sie sagen, wer zustaendig ist, nicht wer zugreifen darf. Berechtigungen
 * kommen ausschliesslich aus der Rolle (D-030).
 *
 * @property string $id
 * @property string $name
 * @property AvvStatus $avv_status
 */
final class Company extends Model
{
    use HasUuids, SoftDeletes, TracksBlame;

    protected $fillable = [
        'name', 'name_addition', 'medical_specialty_id', 'notes', 'debitor_number',
        'avv_status', 'avv_signed_at', 'responsible_sales_id', 'responsible_service_id',
        'billing_company_id', 'iban', 'bic', 'bank_account_holder', 'bank_name',
        'legal_form', 'tax_number', 'vat_id', 'wid_number',
        'trade_register_number', 'register_court',
    ];

    /**
     * Sitzadresse (D-003/D-014).
     *
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
     * Abweichende Rechnungsanschrift (D-004/D-066). ALLE Rechnungen dieser
     * Praxis gehen dorthin — keine praxisuebergreifende Sammelrechnung.
     *
     * @return BelongsTo<Company, $this>
     */
    public function billingCompany(): BelongsTo
    {
        return $this->belongsTo(Company::class, 'billing_company_id');
    }

    /**
     * @return BelongsTo<MedicalSpecialty, $this>
     */
    public function medicalSpecialty(): BelongsTo
    {
        return $this->belongsTo(MedicalSpecialty::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function responsibleSales(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsible_sales_id');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function responsibleService(): BelongsTo
    {
        return $this->belongsTo(User::class, 'responsible_service_id');
    }

    protected function casts(): array
    {
        return [
            'avv_status' => AvvStatus::class,
            'avv_signed_at' => 'date',
        ];
    }
}
