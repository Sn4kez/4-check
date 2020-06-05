<?php

namespace App\Policies;

use App\Checklist;
use App\User;

class ChecklistPolicy extends Policy
{
    /**
     * Determine whether the user can index the model.
     *
     * @param User $user
     * @param Checklist $checklist
     * @return bool
     */
    public function index(User $user, Checklist $checklist)
    {
        return $this->verifyAccessGrant($user, $checklist, 'index');
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param User $user
     * @param Checklist $checklist
     * @return bool
     */
    public function view(User $user, Checklist $checklist)
    {
        return $this->verifyAccessGrant($user, $checklist, 'view');
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @param Checklist $checklist
     * @return bool
     */
    public function update(User $user, Checklist $checklist)
    {
        return $this->verifyAccessGrant($user, $checklist, 'update');
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     * @param Checklist $checklist
     * @return bool
     */
    public function delete(User $user, Checklist $checklist)
    {
        return $this->verifyAccessGrant($user, $checklist, 'delete');
    }
}
