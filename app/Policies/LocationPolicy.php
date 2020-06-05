<?php

namespace App\Policies;

use App\Location;
use App\User;

class LocationPolicy extends Policy
{
    /**
     * Determine whether the user can view the model.
     *
     * @param User $user
     * @param Location $location
     * @return bool
     */

    public function view(User $user, Location $location)
    {
        return $user->company->is($location->company);
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @param Location $location
     * @return bool
     */
    public function update(User $user, Location $location)
    {
        if (!$user->isAdmin()) {
            return false;
        }

        return $user->company->is($location->company);
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     * @param Location $location
     * @return bool
     */
    public function delete(User $user, Location $location)
    {
        if (!$user->isAdmin()) {
            return false;
        }

        return $user->company->is($location->company);
    }
}
