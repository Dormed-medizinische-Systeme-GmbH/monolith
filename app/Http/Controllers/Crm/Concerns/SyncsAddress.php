<?php

namespace App\Http\Controllers\Crm\Concerns;

use App\Modules\Crm\Models\Company;
use App\Modules\Crm\Models\Location;

trait SyncsAddress
{
    /**
     * Create, update or remove the model's single address from validated input.
     *
     * @param  array<string, string|null>|null  $address
     */
    protected function syncAddress(Company|Location $model, ?array $address): void
    {
        $address = array_filter($address ?? [], static fn ($value) => filled($value));

        if ($address === []) {
            $model->address()->delete();

            return;
        }

        $model->address()->updateOrCreate([], $address);
    }
}
