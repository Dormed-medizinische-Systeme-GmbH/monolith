<?php

declare(strict_types=1);

namespace App\Modules\Core\Queries;

use App\Modules\Core\Models\Employee;
use App\Modules\Core\Models\Site;

/**
 * Die Detailansicht eines eigenen Standorts.
 */
final class SiteProfile
{
    /**
     * @return array<string, mixed>
     */
    public static function for(Site $site): array
    {
        $site->load(['employees' => fn ($query) => $query->with('role')->orderBy('last_name')]);

        return [
            'id' => $site->id,
            'name' => $site->name,
            'notes' => $site->notes,
            'photoUrl' => $site->photo_url,

            'address' => [
                'street' => $site->street,
                'postalCode' => $site->postal_code,
                'city' => $site->city,
                'line' => $site->address_line,
            ],

            /*
             * Wer hier sitzt. Steht in der Ansicht, weil es die Frage
             * beantwortet, die vor dem Stilllegen kommt — und weil ein Standort
             * mit Mitarbeitern nicht geloescht werden kann.
             */
            'employees' => $site->employees
                ->map(fn (Employee $user): array => [
                    'id' => $user->id,
                    'name' => $user->name,
                    'role' => $user->role?->name,
                    'photoUrl' => $user->photo_url,
                    'isActive' => $user->is_active,
                ])
                ->all(),
        ];
    }
}
