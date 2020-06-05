<?php

namespace App\Policies;

use App\User;
use App\Company;
use App\TaskState;

class TaskStatePolicy extends Policy
{
    /**
     * Determine whether the user can view the model.
     *
     * @param User $user
     * @param TaskState $taskState
     * @return bool
     */

    public function view(User $user, TaskState $taskState)
    {
        return $user->company->is($taskState->company);
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @param Task $Task
     * @return bool
     */
    public function create(User $user, TaskState $taskState)
    {
        if (!$user->isAdmin()) {
            return false;
        }

        return $user->company->is($taskState->company);
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @param Task $Task
     * @return bool
     */
    public function update(User $user, TaskState $taskState)
    {
        if (!$user->isAdmin()) {
            return false;
        }

        return $user->company->is($taskState->company);
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     * @param Task $Task
     * @return bool
     */
    public function delete(User $user, TaskState $taskState)
    {
        if (!$user->isAdmin()) {
            return false;
        }

        return $user->company->is($taskState->company);
    }
}
