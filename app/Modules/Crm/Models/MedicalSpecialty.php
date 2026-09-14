<?php

declare(strict_types=1);

namespace App\Modules\Crm\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Fachrichtung (D-019/D-133).
 *
 * EINE Liste fuer alles — auch das Website-Kontaktformular zieht seine Auswahl
 * hieraus statt aus einem hartcodierten Array. Vorher waren es zwei
 * konkurrierende Listen.
 *
 * @property int $id
 * @property string $name
 * @property bool $is_active
 */
final class MedicalSpecialty extends Model
{
    use SoftDeletes;

    protected $fillable = ['name', 'is_active'];

    /**
     * @return HasMany<Company, $this>
     */
    public function companies(): HasMany
    {
        return $this->hasMany(Company::class);
    }

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }
}
