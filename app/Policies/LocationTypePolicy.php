<?php

namespace App\Policies;

use App\User;
use App\LocationType;
use App\Company;

class LocationTypePolicy extends Policy
{
    /**
     * Determine whether the user can view the model.
     *
     * @param User $user
     * @param LocationType $locationType
     * @return bool
     */

    public function view(User $user, LocationType $locationType)
    {
        return $user->company->is($locationType->company);
    }

    /**
     * Determine whether the user can create the model.
     *
     * @param User $user
     * @param LocationType $locationType
     * @return bool
     */
    public function create(User $user, LocationType $locationType)
    {
        if (!$user->isAdmin()) {
            return false;
        }

        return $user->company->is($locationType->company);
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @param LocationType $locationType
     * @return bool
     */
    public function update(User $user, LocationType $locationType)
    {
        if (!$user->isAdmin()) {
            return false;
        }

        return $user->company->is($locationType->company);
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     * @param LocationType $locationType
     * @return bool
     */
    public function delete(User $user, LocationType $locationType)
    {
        if (!$user->isAdmin()) {
            return false;
        }

        return $user->company->is($locationType->company);
    }
}
