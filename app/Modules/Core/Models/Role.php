<?php

declare(strict_types=1);

namespace App\Modules\Core\Models;

use Database\Factories\RoleFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Abteilung eines Mitarbeiters (D-125).
 *
 * Genau fuenf, hart definiert ohne Dynamik. Die Permissions je Rolle stecken im
 * Code (`config/authorization.php`, D-030), nicht in der Datenbank und nicht im
 * UI — eine Rechteaenderung ist ein Deployment, keine Klickstrecke.
 *
 * @property int $id
 * @property string $key
 * @property string $name
 * @property bool $is_active
 */
final class Role extends Model
{
    /** @use HasFactory<RoleFactory> */
    use HasFactory;

    /**
     * Wie bei User: der Fabrikname muss benannt werden, weil das Model in
     * einem Modul liegt und nicht in `app/Models` (ADR-033).
     */
    protected static function newFactory(): RoleFactory
    {
        return RoleFactory::new();
    }

    protected $fillable = ['key', 'name', 'is_active'];

    /**
     * @return HasMany<User, $this>
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }
}
