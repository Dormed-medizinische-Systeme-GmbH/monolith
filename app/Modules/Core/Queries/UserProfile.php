<?php

declare(strict_types=1);

namespace App\Modules\Core\Queries;

use App\Modules\Core\Models\User;

/**
 * Die Detailansicht eines Mitarbeiters.
 */
final class UserProfile
{
    /**
     * @return array<string, mixed>
     */
    public static function for(User $user): array
    {
        $user->load('role');

        return [
            'id' => $user->id,
            'firstName' => $user->first_name,
            'lastName' => $user->last_name,
            'name' => $user->name,
            'email' => $user->email,

            'role' => [
                'id' => $user->role->id,
                'key' => $user->role->key,
                'name' => $user->role->name,
            ],

            'flags' => [
                'active' => $user->is_active,
                'admin' => $user->is_admin,
            ],

            /*
             * Der Zustand der Anmeldung. Drei Dinge, die auseinandergehalten
             * gehoeren: ob ueberhaupt ein Passwort gesetzt ist (ohne eines
             * kommt niemand herein, D-029), ob zweiter Faktor bestaetigt wurde
             * (optional, nie Pflicht, ADR-043), und ob SSO schon uebernommen
             * hat (`entra_oid`, bis dahin leer).
             */
            'anmeldung' => [
                'hasPassword' => $user->password !== null,
                'twoFactorConfirmedAt' => $user->two_factor_confirmed_at?->format('d.m.Y'),
                'entraOid' => $user->entra_oid,
                'lastLoginAt' => $user->last_login_at?->format('d.m.Y H:i'),
            ],

            'angelegtAm' => $user->created_at?->format('d.m.Y'),
        ];
    }
}
