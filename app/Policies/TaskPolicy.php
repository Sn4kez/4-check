<?php

namespace App\Policies;

use App\User;
use App\Task;

class TaskPolicy extends Policy
{
    /**
     * Determine whether the user can create the model.
     *
     * @param User $user
     * @param Task $task
     * @return bool
     */
    public function create(User $user, Task $task)
    {
        return $user->company->is($task->company);
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @param Task $task
     * @return bool
     */

    public function update(User $user, Task $task)
    {
        return ($task->issuer($user) || $user->assignee($user) || $user->isAdmin());
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     * @param Task $task
     * @return bool
     */
    public function delete(User $user, Task $task)
    {
        if (!$user->isAdmin()) {
            return false;
        }
        return $user->company->is($task->company);
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param User $user
     * @param Task $task
     * @return bool
     */

    public function view(User $user, Task $task)
    {
        return $user->company->is($task->company);
    }
}
