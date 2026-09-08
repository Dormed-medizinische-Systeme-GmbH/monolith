<?php

namespace App\Policies;

use App\Models\User;
use App\Modules\Crm\Models\Person;

/**
 * TODO(authz): scope to the acting Employee / Department once the identity model
 * exists (docs/03-security/AUTHORIZATION.md). For now every authenticated CRM
 * user may manage people; the `auth` middleware is the only real gate.
 */
class PersonPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Person $person): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Person $person): bool
    {
        return true;
    }

    public function delete(User $user, Person $person): bool
    {
        return true;
    }
}
