<?php

namespace App\Listeners\Registration;

use App\Events\Registration\NewUserInvitationEvent;
use App\Mail\Registration\UserInvitationMail;
use Illuminate\Support\Facades\Mail;

class UserInvitationSendMailListener
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
     * @param  RegistrationNewUserInvitationEvent  $event
     * @return void
     */
    public function handle(NewUserInvitationEvent $event)
    {
        $gender = 'Frau';
        if($event->admin->gender->id == 'male') {
            $gender = 'Herr';
        }
        $data = [
            'adminGender' => $gender,
            'adminName' => $this->buildName($event->admin),
            'token' => $event->invitation->token
        ];

        Mail::to($event->invitation->email)->send(new UserInvitationMail($data));
    }

    private function buildName($user)
    {
        $name = '';

        if(!is_null($user->firstName))
        {
            $name .= $this->getSpacer($name) . $user->firstName;
        }

        if(!is_null($user->middleName))
        {
            $name .= $this->getSpacer($name) . $user->middleName;
        }

        if(!is_null($user->lastName))
        {
            $name .= $this->getSpacer($name) . $user->lastName;
        }

        return $name;
    }

    private function getSpacer(string $name)
    {
        if(strlen($name) > 0)
        {
            return ' ';
        }
        return '';
    }
}
