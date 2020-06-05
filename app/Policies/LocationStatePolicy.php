<?php

namespace App\Policies;

use App\User;
use App\Company;
use App\LocationState;

class LocationStatePolicy extends Policy
{
    /**
     * Determine whether the user can view the model.
     *
     * @param User $user
     * @param LocationState $locationState
     * @return bool
     */

    public function view(User $user, LocationState $locationState)
    {
        return $user->company->is($locationState->company);
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @param Location $location
     * @return bool
     */
    public function create(User $user, LocationState $locationState)
    {
        if (!$user->isAdmin()) {
            return false;
        }

        return $user->company->is($locationState->company);
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @param Location $location
     * @return bool
     */
    public function update(User $user, LocationState $locationState)
    {
        if (!$user->isAdmin()) {
            return false;
        }

        return $user->company->is($locationState->company);
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     * @param Location $location
     * @return bool
     */
    public function delete(User $user, LocationState $locationState)
    {
        if (!$user->isAdmin()) {
            return false;
        }

        return $user->company->is($locationState->company);
    }
}
