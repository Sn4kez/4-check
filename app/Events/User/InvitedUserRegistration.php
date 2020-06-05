<?php

namespace App\Events\User;

use App\Events\Event;
use App\User;

class InvitedUserRegistration extends Event
{
	public $user;

    /**
     * Create a new event instance.
     *
     * @param User $user
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }
}
