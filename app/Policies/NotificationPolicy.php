<?php

namespace App\Policies;

use App\Notification;
use App\User;

class NotificationPolicy extends Policy {

    /**
     * Determine whether the user can view the model or not.
     *
     * @param User $user the User class object
     * @param Notification $notification the notification object
     * @return bool
     */
    public function view(User $user, Notification $notification) {
        if ($notification->userId == $user->userId) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can update the model or not.
     *
     * @param User $user the User class object
     * @param Notification $notification the notification object
     * @return bool
     */
    public function update(User $user, Notification $notification) {
        /**
         * Everyone who can VIEW the notification also can edit it. Because
         * the only thing ever will be edited is the "set as read" flag.
         * 
         * No one can delete it or change values, excepting the read flag.
         */
        return $this->view($user, $notification);
    }

    /**
     * Determine whether the user can delete the model or not 
     *
     * @return bool
     */
    public function delete() {
        /**
         * NOBODY can delete notifications! 
         * 
         * Maybe later: Admins can delete notifications, so replace the
         * "return false;" with "return $user->isAdmin();"
         */
        return false;
    }

}
