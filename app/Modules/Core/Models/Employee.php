<?php

declare(strict_types=1);

namespace App\Modules\Core\Models;

use Database\Factories\EmployeeFactory;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
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
 * @property string|null $site_id
 * @property string|null $photo_path
 * @property-read string|null $photo_url
 */
final class Employee extends Authenticatable
{
    /** @use HasFactory<EmployeeFactory> */
    use HasFactory, HasUuids, Notifiable, SoftDeletes, TwoFactorAuthenticatable;

    /**
     * Die Fabrik muss benannt werden: Laravel leitet ihren Namen sonst aus dem
     * Model-Namespace ab und sucht sie unter
     * `Database\Factories\Modules\Core\Models\EmployeeFactory`. Das ist die
     * Folge davon, dass Models in Modulen liegen (ADR-033) und nicht in
     * `app/Models`.
     */
    protected static function newFactory(): EmployeeFactory
    {
        return EmployeeFactory::new();
    }

    /**
     * `photo_path` steht bewusst NICHT hier. Das Foto wird nicht ueber die
     * Mitarbeitermaske gepflegt — es kommt aus dem Seed, spaeter aus einem
     * eigenen Vorgang mit eigener Ability. Ausserhalb von `$fillable` kann es
     * kein Formularfeld setzen, auch kein untergeschobenes.
     */
    protected $fillable = [
        'first_name', 'last_name', 'email', 'password',
        'entra_oid', 'is_admin', 'is_active', 'role_id', 'site_id',
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

    /**
     * Der Dormed-Standort, an dem dieser Mitarbeiter sitzt.
     *
     * @return BelongsTo<Site, $this>
     */
    public function site(): BelongsTo
    {
        return $this->belongsTo(Site::class);
    }

    /**
     * Die Adresse des Mitarbeiterfotos, oder `null`.
     *
     * `Storage::url()` baut nur eine Zeichenkette — die Anwendung steht nicht
     * im Abrufweg, der Browser holt die Datei direkt beim Speicher (ADR-045).
     *
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
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            // Fehlte: Fortify prueft die Spalte nur auf „gesetzt" und kommt
            // deshalb ohne Cast aus. Wer sie anzeigen will, bekam eine
            // Zeichenkette und `->format()` darauf einen Fehler.
            'two_factor_confirmed_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
            'is_active' => 'boolean',
        ];
    }
}
