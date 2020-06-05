<?php

namespace App\Listeners\Checklist;

use App\Events\Checklist\ChecklistEscalationEvent;
use App\Mail\Checklist\ChecklistEscalationMail;
use Illuminate\Support\Facades\Mail;

class EscalationSendMailListener
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
     * @param  ChecklistChecklistEscalationEvent  $event
     * @return void
     */
    public function handle(ChecklistEscalationEvent $event)
    {
        $gender = 'Frau';
        if($event->user->gender->id == 'male') {
            $gender = 'Herr';
        }

        $data = [
            'id' => $event->checklist->id,
            'checklist' => $event->checklist->name,
            'gender' => $gender,
            'name' => $this->buildName($user),
            'audit' => $event->audit->id,
        ];

        Mail::to($event->user->email)->send(new ChecklistEscalationMail($data));
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
