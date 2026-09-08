<?php

namespace App\Policies;

use App\Models\User;
use App\Modules\Crm\Models\Company;

/**
 * TODO(authz): scope to the acting Employee / Department once the identity model
 * exists (docs/03-security/AUTHORIZATION.md). For now every authenticated CRM
 * user may manage companies; the `auth` middleware is the only real gate.
 */
class CompanyPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Company $company): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Company $company): bool
    {
        return true;
    }

    public function delete(User $user, Company $company): bool
    {
        return true;
    }
}
