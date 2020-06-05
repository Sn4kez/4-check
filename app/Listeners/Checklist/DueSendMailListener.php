<?php

namespace App\Listeners\Checklist;

use App\Events\Checklist\ChecklistDueEvent;
use App\Mail\Checklist\ChecklistDueMail;
use App\NotificationPreferences;
use Illuminate\Support\Facades\Mail;

class DueSendMailListener
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
     * @param  ChecklistChecklistDueEvent  $event
     * @return void
     */
    public function handle(ChecklistDueEvent $event)
    {
        $gender = 'Frau';
        if($event->user->gender->id == 'male') {
            $gender = 'Herr';
        }

        $data = [
            'id' => $event->checklist->id,
            'checklist' => $event->checklist->name,
            'checklistId' => $event->checklist->id,
            'gender' => $gender,
            'name' => $this->buildName($event->user),
        ];

        $preferences = NotificationPreferences::where('userId', '=', $event->user->id)->first();

        if(is_null($preferences) || $preferences->checklistDueMail == 1) {
            Mail::to($event->user->email)->send(new ChecklistDueMail($data));
        }
       
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
