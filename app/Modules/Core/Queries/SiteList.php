<?php

declare(strict_types=1);

namespace App\Modules\Core\Queries;

use App\Modules\Core\Models\Site;
use Illuminate\Database\Eloquent\Builder;

/**
 * Die Liste der eigenen Standorte.
 *
 * Kein Join: die Anschrift steht in `sites` selbst (siehe Model), und die Zahl
 * der Mitarbeiter wird gezaehlt, nicht gejoint — ein Join auf `users` machte
 * aus einem Standort mit sechs Mitarbeitern sechs Zeilen.
 */
final class SiteList
{
    /**
     * @var array<string, string>
     */
    public const SORTABLE = [
        'name' => 'sites.name',
        'city' => 'sites.city',
        'employees' => 'employees_count',
    ];

    /**
     * @var list<string>
     */
    public const SEARCHABLE = [
        'sites.name',
        'sites.street',
        'sites.postal_code',
        'sites.city',
    ];

    /**
     * @return Builder<Site>
     */
    public static function query(): Builder
    {
        return Site::query()->withCount('employees');
    }

    /**
     * @return array<string, mixed>
     */
    public static function row(Site $site): array
    {
        return [
            'id' => $site->id,
            'name' => $site->name,
            'addressLine' => $site->address_line,
            'city' => $site->city,
            'employeeCount' => (int) $site->getAttribute('employees_count'),
            'photoUrl' => $site->photo_url,
        ];
    }
}
