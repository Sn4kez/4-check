<?php

namespace App\Policies;

use App\User;

class UserPolicy extends Policy
{
    /**
     * Determine whether the user can view the model.
     *
     * @param User $loggedInUser
     * @param User $user
     * @return bool
     */
    public function view(User $loggedInUser, User $user)
    {
        $authorized = $loggedInUser->is($user);
        if (!$authorized && $loggedInUser->isAdmin()) {
            $authorized = $loggedInUser->company->is($user->company);
        }
        return $authorized;
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $loggedInUser
     * @param User $user
     * @return bool
     */
    public function update(User $loggedInUser, User $user)
    {
        $authorized = $loggedInUser->is($user);
        if (!$authorized && $loggedInUser->isAdmin()) {
            $authorized = $loggedInUser->company->is($user->company);
        }
        return $authorized;
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $loggedInUser
     * @param User $user
     * @return bool
     */
    public function delete(User $loggedInUser, User $user)
    {
        $authorized = $loggedInUser->is($user);
        if (!$authorized && $loggedInUser->isAdmin()) {
            $authorized = $loggedInUser->company->is($user->company);
        }
        return $authorized;
    }
}
