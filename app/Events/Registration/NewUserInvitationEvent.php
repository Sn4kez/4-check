<?php

namespace App\Events\Registration;

use App\Events\Event;
use App\UserInvitation;
use App\User;

class NewUserInvitationEvent extends Event
{
	public $invitation;
    public $admin;

    /**
     * Create a new event instance.
     *
     * @param UserInvitation $invitation
     */
    public function __construct(UserInvitation $invitation, User $user)
    {
        $this->invitation = $invitation;
        $this->admin = $user;
    }
}
