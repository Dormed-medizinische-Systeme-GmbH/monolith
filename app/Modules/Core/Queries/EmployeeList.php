<?php

declare(strict_types=1);

namespace App\Modules\Core\Queries;

use App\Modules\Core\Models\Employee;
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
final class EmployeeList
{
    /**
     * @var array<string, string>
     */
    public const SORTABLE = [
        'name' => 'employees.last_name',
        'email' => 'employees.email',
        'role' => 'roles.name',
        'site' => 'sites.name',
        'lastLogin' => 'employees.last_login_at',
    ];

    /**
     * @var list<string>
     */
    public const SEARCHABLE = [
        "employees.first_name || ' ' || employees.last_name",
        'employees.email',
        'roles.name',
        'sites.name',
    ];

    /**
     * @return Builder<Employee>
     */
    public static function query(): Builder
    {
        return Employee::query()
            ->select('employees.*')
            ->addSelect(['roles.name as role_name', 'sites.name as site_name'])
            ->leftJoin('roles', function (JoinClause $join): void {
                $join->on('roles.id', '=', 'employees.role_id');
            })
            ->leftJoin('sites', function (JoinClause $join): void {
                $join->on('sites.id', '=', 'employees.site_id')
                    ->whereNull('sites.deleted_at');
            });
    }

    /**
     * @return array<string, mixed>
     */
    public static function row(Employee $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->getAttribute('role_name'),
            'site' => $user->getAttribute('site_name'),
            'isActive' => $user->is_active,
            'isAdmin' => $user->is_admin,
            'hasPassword' => $user->password !== null,
            'hasTwoFactor' => $user->two_factor_confirmed_at !== null,
            'lastLoginAt' => $user->last_login_at?->format('d.m.Y H:i'),
        ];
    }
}
