<?php

namespace App\Policies;

use App\User;
use App\TaskType;
use App\Company;

class TaskTypePolicy extends Policy
{
    /**
     * Determine whether the user can view the model.
     *
     * @param User $user
     * @param TaskType $taskType
     * @return bool
     */

    public function view(User $user, TaskType $taskType)
    {
        return $user->company->is($taskType->company);
    }

    /**
     * Determine whether the user can create the model.
     *
     * @param User $user
     * @param TaskType $taskType
     * @return bool
     */
    public function create(User $user, TaskType $taskType)
    {
        if (!$user->isAdmin()) {
            return false;
        }

        return $user->company->is($taskType->company);
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @param TaskType $taskType
     * @return bool
     */
    public function update(User $user, TaskType $taskType)
    {
        if (!$user->isAdmin()) {
            return false;
        }

        return $user->company->is($taskType->company);
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     * @param TaskType $taskType
     * @return bool
     */
    public function delete(User $user, TaskType $taskType)
    {
        if (!$user->isAdmin()) {
            return false;
        }

        return $user->company->is($taskType->company);
    }
}
