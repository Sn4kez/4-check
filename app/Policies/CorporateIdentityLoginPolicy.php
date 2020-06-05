<?php

namespace App\Policies;

use App\Company;
use App\User;

class CorporateIdentityLoginPolicy extends Policy
{
    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @param Company $company
     * @return bool
     */
    public function update(User $user, Company $company)
    {
        if (!$user->isAdmin()) {
            return false;
        }
        return $user->company->is($company);
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     * @param Company $company
     * @return bool
     */
    public function delete(User $user, Company $company)
    {
        if (!$user->isAdmin()) {
            return false;
        }
        return $user->company->is($company);
    }
}
