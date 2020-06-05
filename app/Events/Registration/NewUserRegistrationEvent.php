<?php

namespace App\Events\Registration;

use App\Events\Event;

class NewUserRegistrationEvent extends Event
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
