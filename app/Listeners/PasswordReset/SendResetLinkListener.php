<?php

namespace App\Listeners\PasswordReset;

use App\Events\PasswordReset\PasswordResetTokenSetEvent;
use App\Mail\LostPassword\ChangePasswordLinkMail;
use Illuminate\Support\Facades\Mail;

class SendResetLinkListener
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
     * @param PasswordResetTokenSetEvent  $event
     * @return void
     */
    public function handle(PasswordResetTokenSetEvent $event)
    {
        $gender = 'Frau';
        if($event->user->gender->id == 'male') {
            $gender = 'Herr';
        }
        $data = [
            'token' => $event->user->token,
            'gender' => $gender,
            'name' => $this->buildName($event->user)
        ];

        Mail::to($event->user->email)->send(new ChangePasswordLinkMail($data));
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
