<?php

namespace App\Events\PasswordReset;

use App\Events\Event;
use App\User;

class PasswordResetTokenSetEvent extends Event
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
