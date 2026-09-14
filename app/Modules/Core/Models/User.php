<?php

declare(strict_types=1);

namespace App\Modules\Core\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;

/**
 * Ein MITARBEITER (IDENTITY_RBAC.md, ADR-042).
 *
 * Kundenzugaenge sind bewusst NICHT hier, sondern in
 * `App\Modules\Crm\Models\CustomerAccount` — getrennte Tabelle, getrenntes
 * Model, getrennter Guard. Ein Kunde kann in einer Mitarbeiterabfrage damit
 * strukturell nicht vorkommen.
 *
 * Kein `name`-Feld (D-093): `first_name` + `last_name`, der Accessor setzt sie
 * zusammen. Der urspruengliche Breeze-Kompatibilitaetsgrund ist mit Fortify
 * entfallen.
 *
 * @property string $id
 * @property string $first_name
 * @property string $last_name
 * @property string $email
 * @property string|null $entra_oid
 * @property bool $is_admin
 * @property bool $is_active
 * @property string $role_id
 */
final class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasUuids, Notifiable, SoftDeletes, TwoFactorAuthenticatable;

    /**
     * Die Fabrik muss benannt werden: Laravel leitet ihren Namen sonst aus dem
     * Model-Namespace ab und sucht sie unter
     * `Database\Factories\Modules\Core\Models\UserFactory`. Das ist die
     * Folge davon, dass Models in Modulen liegen (ADR-033) und nicht in
     * `app/Models`.
     */
    protected static function newFactory(): UserFactory
    {
        return UserFactory::new();
    }

    protected $fillable = [
        'first_name', 'last_name', 'email', 'password',
        'entra_oid', 'is_admin', 'is_active', 'role_id',
    ];

    protected $hidden = [
        'password', 'remember_token',
        'two_factor_secret', 'two_factor_recovery_codes',
    ];

    /**
     * @return BelongsTo<Role, $this>
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    /**
     * @return Attribute<string, never>
     */
    protected function name(): Attribute
    {
        return Attribute::get(fn (): string => trim("{$this->first_name} {$this->last_name}"));
    }

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
            'is_active' => 'boolean',
        ];
    }
}
