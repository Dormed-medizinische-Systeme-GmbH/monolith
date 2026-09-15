<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Modules\Core\Models\Role;
use Illuminate\Database\Seeder;

/**
 * Die fuenf Abteilungen (D-125, revidiert D-031).
 *
 * KEIN Demo-Seed: `employees.role_id` ist NOT NULL (D-124), ohne diese Zeilen
 * laesst sich kein Mitarbeiter anlegen. Laeuft deshalb auch in Produktion.
 *
 * Hart definiert ohne Dynamik (Nutzer). `accounting` und `it` sind gegenueber
 * D-031 ersatzlos gestrichen — Billing wandert zu `backoffice`, IT wird durch
 * den `is_admin`-Bypass ersetzt (D-028).
 */
final class RoleSeeder extends Seeder
{
    /**
     * @var list<array{key: string, name: string}>
     */
    private const ROLES = [
        ['key' => 'geschaeftsfuehrung', 'name' => 'Geschäftsführung'],
        ['key' => 'management', 'name' => 'Management'],
        ['key' => 'backoffice', 'name' => 'Backoffice'],
        ['key' => 'sales', 'name' => 'Vertrieb'],
        ['key' => 'service', 'name' => 'Service'],
    ];

    public function run(): void
    {
        foreach (self::ROLES as $role) {
            Role::query()->updateOrCreate(
                ['key' => $role['key']],
                ['name' => $role['name'], 'is_active' => true],
            );
        }
    }
}
