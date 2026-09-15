<?php

declare(strict_types=1);

namespace App\Modules\Core\Models;

use App\Support\TracksBlame;
use Database\Factories\SiteFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
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
 * @property string|null $photo_path
 * @property-read string|null $photo_url
 */
final class Site extends Model
{
    /** @use HasFactory<SiteFactory> */
    use HasFactory, HasUuids, SoftDeletes, TracksBlame;

    /**
     * Wie bei User und Role: der Fabrikname muss benannt werden, weil das Model
     * in einem Modul liegt und nicht in `app/Models` (ADR-033).
     */
    protected static function newFactory(): SiteFactory
    {
        return SiteFactory::new();
    }

    /**
     * `photo_path` fehlt hier bewusst — wie beim Mitarbeiterfoto kommt das Bild
     * aus dem Seed und nicht aus einer Maske.
     */
    protected $fillable = ['name', 'street', 'postal_code', 'city', 'notes'];

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
            $ort = trim("{$this->postal_code} {$this->city}");

            return trim(implode(', ', array_filter([$this->street, $ort]))) ?: null;
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
}
