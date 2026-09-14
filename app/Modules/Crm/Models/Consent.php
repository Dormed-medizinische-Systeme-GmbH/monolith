<?php

declare(strict_types=1);

namespace App\Modules\Crm\Models;

use App\Modules\Crm\Enums\ConsentChannel;
use App\Modules\Crm\Enums\ConsentSource;
use App\Modules\Crm\Enums\ConsentStatus;
use App\Support\TracksBlame;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Historisierte DSGVO-Einwilligung je Person und Kanal (D-013).
 *
 * Wird NIE hart geloescht — Nachweispflicht. Ein Statuswechsel erzeugt einen
 * neuen Datensatz, er aendert keinen bestehenden.
 *
 * @property string $id
 * @property string $person_id
 * @property ConsentChannel $channel
 * @property ConsentStatus $status
 */
final class Consent extends Model
{
    use HasUuids, SoftDeletes, TracksBlame;

    protected $fillable = ['person_id', 'channel', 'status', 'granted_at', 'revoked_at', 'source'];

    /**
     * @return BelongsTo<Person, $this>
     */
    public function person(): BelongsTo
    {
        return $this->belongsTo(Person::class);
    }

    protected function casts(): array
    {
        return [
            'channel' => ConsentChannel::class,
            'status' => ConsentStatus::class,
            'source' => ConsentSource::class,
            'granted_at' => 'datetime',
            'revoked_at' => 'datetime',
        ];
    }
}
