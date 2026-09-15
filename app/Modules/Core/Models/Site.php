<?php

declare(strict_types=1);

namespace App\Modules\Core\Models;

use App\Support\TracksBlame;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

/**
 * Ein eigener Standort von Dormed — Buchholz, Holzwickede.
 *
 * Nicht zu verwechseln mit `Crm\Models\Location`: das sind KUNDENstandorte und
 * haengen an einer Company (D-007). Hier geht es um die eigenen
 * Betriebsstaetten, an denen Mitarbeiter sitzen.
 *
 * Die Anschrift steht als eigene Spalten hier und nicht in `addresses`: die
 * Tabelle gehoert zu `Modules\Crm`, und `Core` haengt von nichts ab — ein
 * Zugriff machte den Modulgraph zyklisch (ADR-005).
 *
 * @property string $id
 * @property string $name
 * @property bool $is_active
 * @property string|null $photo_path
 * @property-read string|null $photo_url
 */
final class Site extends Model
{
    use HasUuids, SoftDeletes, TracksBlame;

    protected $attributes = ['is_active' => true];

    /**
     * `photo_path` fehlt hier bewusst — wie beim Mitarbeiterfoto kommt das Bild
     * aus dem Seed und nicht aus einer Maske.
     */
    protected $fillable = [
        'name', 'short_name', 'street', 'house_number', 'postal_code', 'city',
        'notes', 'is_active',
    ];

    /**
     * @return HasMany<User, $this>
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * Einzeilige Anschrift, oder `null`, solange keine hinterlegt ist.
     *
     * @return Attribute<string|null, never>
     */
    protected function addressLine(): Attribute
    {
        return Attribute::get(function (): ?string {
            $strasse = trim("{$this->street} {$this->house_number}");
            $ort = trim("{$this->postal_code} {$this->city}");

            return trim(implode(', ', array_filter([$strasse, $ort]))) ?: null;
        });
    }

    /**
     * @return Attribute<string|null, never>
     */
    protected function photoUrl(): Attribute
    {
        return Attribute::get(fn (): ?string => $this->photo_path === null
            ? null
            : Storage::disk('s3')->url($this->photo_path));
    }

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }
}
