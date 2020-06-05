<?php
namespace App\Listeners\NotificationPreferences;

use App\Events\User\UserCreateEvent;
use App\Events\User\InvitedUserRegistration;
use App\NotificationPreferences;
use Ramsey\Uuid\Uuid;

class CreateNotificationPreferencesListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }
    /**
     * Handle the event.
     *
     * @param  UserCreateEvent  $event
     * @return void
     */
    public function onUserRegistration(UserCreateEvent $event)
    {
        $preferences = new NotificationPreferences();
        $preferences->id = Uuid::uuid4()->toString();
        $preferences->user()->associate($event->user);
        $preferences->save();
    }

    /**
     * Handle the event.
     *
     * @param  UserCreateEvent  $event
     * @return void
     */
    public function onUserInvitation(InvitedUserRegistration $event)
    {
        $preferences = new NotificationPreferences();
        $preferences->id = Uuid::uuid4()->toString();
        $preferences->user()->associate($event->user);
        $preferences->save();
    }
}
