<?php

namespace App\Policies;

use App\Audit;
use App\User;

class AuditPolicy extends Policy
{
    /**
     * Determine whether the user can index the model.
     *
     * @param User $user
     * @param Audit $audit
     * @return bool
     */
    public function index(User $user, Audit $audit)
    {
        return $user->company->is($audit->company);
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param User $user
     * @param Audit $audit
     * @return bool
     */
    public function view(User $user, Audit $audit)
    {
        return $user->company->is($audit->company);

    }

    /**
     * Determine whether the user can create the model.
     *
     * @param User $user
     * @param Audit $audit
     * @return bool
     */
    public function create(User $user, Audit $audit)
    {
        return $user->company->is($audit->company);

    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @param Audit $audit
     * @return bool
     */
    public function update(User $user, Audit $audit)
    {
        return $user->company->is($audit->company);

    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     * @param Audit $audit
     * @return bool
     */
    public function delete(User $user, Audit $audit)
    {
        if (!$user->isAdmin()) {
            return false;
        }
        
        return $user->company->is($audit->company);
    }
}
