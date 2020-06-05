<?php

namespace App\Events\PasswordReset;

use App\Events\Event;
use App\User;

class PasswordResetConfirmationEvent extends Event
{
	public $user;
	 
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(User $user)
    {
        $this->user = $user;
    }
}
