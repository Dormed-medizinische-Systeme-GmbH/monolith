<?php

declare(strict_types=1);

namespace App\Support;

use App\Modules\Core\Models\User;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Setzt `created_by` / `updated_by` automatisch (D-018).
 *
 * Zeigt ausdruecklich auf `users`, also auf MITARBEITER (ADR-042). Was ein Kunde
 * erzeugt, wird ueber die fachliche Beziehung abgebildet (z. B. `person_id`),
 * nicht ueber diese Spalten — sonst bedeutete `created_by` mal das eine, mal das
 * andere.
 *
 * Die Spalten bleiben leer, wenn niemand angemeldet ist: Seeder, Kommandos und
 * Migrationen haben keinen Urheber, und das ist richtiger als ein erfundener.
 */
trait TracksBlame
{
    public static function bootTracksBlame(): void
    {
        static::creating(function (self $model): void {
            $id = auth('staff')->id();

            $model->created_by ??= $id;
            $model->updated_by ??= $id;
        });

        static::updating(function (self $model): void {
            $model->updated_by = auth('staff')->id() ?? $model->updated_by;
        });
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
