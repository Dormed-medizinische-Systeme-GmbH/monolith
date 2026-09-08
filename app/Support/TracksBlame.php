<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

/**
 * Stamps `created_by` / `updated_by` from the authenticated user.
 *
 * The table must carry both columns as nullable foreign keys to `users`
 * (see the module migrations). When there is no authenticated user — console,
 * unauthenticated tests — the columns are left untouched.
 */
trait TracksBlame
{
    public static function bootTracksBlame(): void
    {
        static::creating(function (Model $model): void {
            $userId = Auth::id();

            if ($userId === null) {
                return;
            }

            if ($model->getAttribute('created_by') === null) {
                $model->setAttribute('created_by', $userId);
            }

            $model->setAttribute('updated_by', $userId);
        });

        static::updating(function (Model $model): void {
            if (($userId = Auth::id()) !== null) {
                $model->setAttribute('updated_by', $userId);
            }
        });
    }
}
