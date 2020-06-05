<?php

namespace App\Policies;

use App\User;
use App\UserInvitation;
use App\Company;

class UserInvitationPolicy extends Policy
{
    /**
     * Determine whether the user can create the model.
     *
     * @param User $user
     * @param Company $company
     * @return bool
     */
    public function create(User $user, UserInvitation $invitation)
    {
        if (!$user->isAdmin()) {
            return false;
        }
    
        return $user->company->is($invitation->company);
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @param UserInvitation $invitation
     * @return bool
     */

    public function update(User $user, UserInvitation $invitation)
    {
        if (!$user->isAdmin()) {
            return false;
        }
    
        return $user->company->is($invitation->company);
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     * @param UserInvitation $invitation
     * @return bool
     */
    public function delete(User $user, UserInvitation $invitation)
    {
        if (!$user->isAdmin()) {
            return false;
        }
    
        return $user->company->is($invitation->company);
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param User $user
     * @param UserInvitation $invitation
     * @return bool
     */

    public function view(User $user, UserInvitation $invitation)
    {
        if (!$user->isAdmin()) {
            return false;
        }
    
        return $user->company->is($invitation->company);
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param User $user
     * @param UserInvitation $invitation
     * @return bool
     */

    public function index(User $user, UserInvitation $invitation)
    {
        if (!$user->isAdmin()) {
            return false;
        }
    
        return $user->company->is($invitation->company);
    }
}
