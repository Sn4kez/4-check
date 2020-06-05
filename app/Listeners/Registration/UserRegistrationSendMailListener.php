<?php

namespace App\Listeners\Registration;

use App\Events\User\UserCreateEvent;
use App\Mail\Registration\NewClientValidationMail;
use Illuminate\Support\Facades\Mail;

class UserRegistrationSendMailListener
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
     * @param  RegistrationNewUserRegistrationEvent  $event
     * @return void
     */
    public function handle(UserCreateEvent $event)
    {
        $data = [
            'token' => $event->user->token
        ];
        Mail::to($event->user->email)->send(new NewClientValidationMail($data));
    }
}
