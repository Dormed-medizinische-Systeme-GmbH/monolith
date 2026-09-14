<?php

declare(strict_types=1);

namespace App\Modules\Crm\Models;

use App\Support\TracksBlame;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Die explizite Beziehung Person <-> Company (CORE.md).
 *
 * `role` ist bewusst Freitext mit Vorschlagsliste und KEIN Enum (D-005) — es
 * ist nicht auswertungsrelevant. Bei `ContactChannel.label` ist es umgekehrt.
 *
 * @property int $id
 * @property int $company_id
 * @property int $person_id
 * @property string|null $role
 * @property bool $is_primary
 */
final class CompanyContact extends Model
{
    use SoftDeletes, TracksBlame;

    /**
     * Vorschlagswerte fuer das UI (D-005). Eine Datalist, keine Einschraenkung —
     * Freitext bleibt erlaubt.
     *
     * @var list<string>
     */
    public const ROLE_SUGGESTIONS = [
        'Praxismanager*in', 'Einkauf', 'IT', 'Buchhaltung',
        'Ärztliche Leitung', 'Technik', 'Empfang', 'Sonstige',
    ];

    protected $fillable = ['company_id', 'person_id', 'role', 'department', 'is_primary'];

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

    protected function casts(): array
    {
        return ['is_primary' => 'boolean'];
    }
}
