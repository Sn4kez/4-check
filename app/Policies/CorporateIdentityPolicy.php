<?php

namespace App\Policies;

use App\CorporateIdentity;
use App\User;

class CorporateIdentityPolicy extends Policy
{
    /**
     * Determine whether the user can view the model.
     *
     * @param User $user
     * @param Company $company
     * @return bool
     */
    public function view(User $user, CorporateIdentity $ci)
    {
        if (!$user->isAdmin()) {
            return false;
        }
        return $user->company->is($ci->company);
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @param Company $company
     * @return bool
     */
    public function update(User $user, CorporateIdentity $ci)
    {
        if (!$user->isAdmin()) {
            return false;
        }
        return $user->company->is($ci->company);
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     * @param Company $company
     * @return bool
     */
    public function delete(User $user, CorporateIdentity $ci)
    {
        if (!$user->isAdmin()) {
            return false;
        }
        return $user->company->is($ci->company);
    }
}
