<?php

namespace App\Listeners\Checklist;

use App\Events\Checklist\ChecklistCriticalRatingEvent;
use App\Mail\Checklist\ChecklistCriticalRatingMail;
use Illuminate\Support\Facades\Mail;
use App\User;
use App\NotificationPreferences;

class CriticalRatingSendMailListener
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
     * @param  ChecklistChecklistCriticalRatingEvent  $event
     * @return void
     */
    public function handle(ChecklistCriticalRatingEvent $event)
    {
        $exec = User::find($event->audit->userId);
        $gender = 'Frau';
        if($event->user->gender->id == 'male') {
            $gender = 'Herr';
        }

        $execGender = 'Frau';
        if($exec->gender->id == 'male') {
            $execGender = 'Herr';
        }        

        $data = [
            'id' => $event->checklist->id,
            'checklist' => $event->checklist->name,
            'gender' => $gender,
            'name' => $this->buildName($event->user),
            'ratings' => $event->ratings,
            'audit' => $event->audit->id,
            'execGender' => $execGender,
            'execName' => $this->buildName($exec)
        ];

        $preferences = NotificationPreferences::where('userId', '=', $event->user->id)->first();

        if(is_null($preferences) || $preferences->checklistCriticalRatingMail == 1) {
            Mail::to($event->user->email)->send(new ChecklistCriticalRatingMail($data));
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
