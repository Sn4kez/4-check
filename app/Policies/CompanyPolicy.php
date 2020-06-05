<?php

namespace App\Policies;

use App\Company;
use App\User;

class CompanyPolicy extends Policy
{
    /**
     * Determine whether the user can view the model.
     *
     * @param User $user
     * @param Company $company
     * @return bool
     */
    public function view(User $user, Company $company)
    {
        return $user->company->is($company);
    }

    /**
     * Determine whether the user can view a list of the model.
     *
     * @param User $user
     * @param Company $company
     * @return bool
     */
    public function index(User $user, Company $company)
    {
        return $user->company->is($company);
    }

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
        if($user->isAdmin()) {
            return $user->company->is($company);
        }

        if($user->isSuperAdmin()) {
            return true;
        }

        return false;
    }
}
