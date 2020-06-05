<?php

namespace App\Policies;

use App\User;
use App\Company;
use App\TaskPriority;

class TaskPriorityPolicy extends Policy
{
    /**
     * Determine whether the user can view the model.
     *
     * @param User $user
     * @param TaskPriority $taskPriority
     * @return bool
     */

    public function view(User $user, TaskPriority $taskPriority)
    {
        return $user->company->is($taskPriority->company);
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @param Task $Task
     * @return bool
     */
    public function create(User $user, TaskPriority $taskPriority)
    {
        if (!$user->isAdmin()) {
            return false;
        }

        return $user->company->is($taskPriority->company);
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @param Task $Task
     * @return bool
     */
    public function update(User $user, TaskPriority $taskPriority)
    {
        if (!$user->isAdmin()) {
            return false;
        }

        return $user->company->is($taskPriority->company);
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     * @param Task $Task
     * @return bool
     */
    public function delete(User $user, TaskPriority $taskPriority)
    {
        if (!$user->isAdmin()) {
            return false;
        }

        return $user->company->is($taskPriority->company);
    }
}
