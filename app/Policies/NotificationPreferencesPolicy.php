<?php

namespace App\Policies;

use App\NotificationPreferences;
use App\User;

class NotificationPreferencesPolicy extends Policy {

    /**
     * Determine whether the user can view the model or not.
     *
     * @param User $user the User class object
     * @param Notification $notification the notification object
     * @return bool
     */
    public function view(User $user, NotificationPreferences $preferences) {
        return $preferences->user->is($user);
    }

    /**
     * Determine whether the user can update the model or not.
     *
     * @param User $user the User class object
     * @param Notification $notification the notification object
     * @return bool
     */
    public function update(User $user, NotificationPreferences $preferences) {
        return $preferences->user->is($user);
    }

}
