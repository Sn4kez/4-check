<?php

namespace App\Policies;

use App\InspectionPlan;
use App\User;

class InspectionPlanPolicy extends Policy
{
    /**
     * Determine whether the user can create the model.
     *
     * @param User $user
     * @param Task $task
     * @return bool
     */
    public function create(User $user, InspectionPlan $plan)
    {
        if (!$user->isAdmin()) {
            return false;
        }
    
        return $user->company->is($plan->company);
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @param Task $task
     * @return bool
     */

    public function update(User $user, InspectionPlan $plan)
    {
        if (!$user->isAdmin()) {
            return false;
        }
    
        return $user->company->is($plan->company);
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     * @param Task $task
     * @return bool
     */
    public function delete(User $user, InspectionPlan $plan)
    {
        if (!$user->isAdmin()) {
            return false;
        }
    
        return $user->company->is($plan->company);
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param User $user
     * @param Task $task
     * @return bool
     */

    public function view(User $user, InspectionPlan $plan)
    {
        if (!$user->isAdmin()) {
            return false;
        }
    
        return $user->company->is($plan->company);
    }
}
