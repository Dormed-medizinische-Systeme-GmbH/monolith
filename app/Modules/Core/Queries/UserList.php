<?php

declare(strict_types=1);

namespace App\Modules\Core\Queries;

use App\Modules\Core\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;

/**
 * Die Mitarbeiterliste des ERP als Lesemodell.
 *
 * Eine Zeile ist ein Mitarbeiter. Der Join auf `roles` kann die Zeilen nicht
 * vervielfachen — genau eine Rolle je Mitarbeiter (D-124). Der Pivot
 * `role_user` ist ersatzlos entfallen; wer hier einen Join auf eine
 * Zuordnungstabelle einbaut, dreht die Entscheidung um.
 *
 * Kunden koennen hier strukturell nicht auftauchen: sie liegen in
 * `customer_accounts` (ADR-042). Das ist der Zweck der Trennung — kein
 * vergessenes `where` kann sie hereinlassen.
 */
final class UserList
{
    /**
     * @var array<string, string>
     */
    public const SORTABLE = [
        'name' => 'users.last_name',
        'email' => 'users.email',
        'role' => 'roles.name',
        'lastLogin' => 'users.last_login_at',
    ];

    /**
     * @var list<string>
     */
    public const SEARCHABLE = [
        "users.first_name || ' ' || users.last_name",
        'users.email',
        'roles.name',
    ];

    /**
     * @return Builder<User>
     */
    public static function query(): Builder
    {
        return User::query()
            ->select('users.*')
            ->addSelect(['roles.name as role_name'])
            ->leftJoin('roles', function (JoinClause $join): void {
                $join->on('roles.id', '=', 'users.role_id');
            });
    }

    /**
     * @return array<string, mixed>
     */
    public static function row(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->getAttribute('role_name'),
            'isActive' => $user->is_active,
            'isAdmin' => $user->is_admin,
            'hasPassword' => $user->password !== null,
            'hasTwoFactor' => $user->two_factor_confirmed_at !== null,
            'lastLoginAt' => $user->last_login_at?->format('d.m.Y H:i'),
        ];
    }
}
