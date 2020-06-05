<?php

namespace App\Policies;

use App\CompanySubscription;
use App\User;

class CompanySubscriptionPolicy extends Policy
{
    /**
     * Determine whether the user can view the model.
     *
     * @param User $user
     * @param CompanySubscription $subscription
     * @return bool
     */
    public function view(User $user, CompanySubscription $subscription)
    {
        if (!$user->isAdmin() && !$user->isSuperAdmin() ) {
            return false;
        }
        return $user->company->is($subscription->company);
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @param CompanySubscription $subscription
     * @return bool
     */
    public function update(User $user, CompanySubscription $subscription)
    {
        // Only super-admins can update subscriptions.
        return false;
    }
}
