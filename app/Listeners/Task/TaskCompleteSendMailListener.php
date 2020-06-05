<?php

namespace App\Listeners\Task;

use App\Events\Task\TaskCompleteEvent;
use App\Mail\Task\CompleteTaskMail;
use Illuminate\Support\Facades\Mail;
use App\NotificationPreferences;

class TaskCompleteSendMailListener
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
     * @param  TaskTaskCompleteEvent  $event
     * @return void
     */
    public function handle(TaskCompleteEvent $event)
    {
        $users = [
            $event->task->assignee,
        ];

        $location = '';

        if($event->task->location()->exists()) {
            $location = $event->task->location->name;
        }

        foreach($users as $user)
        {
            $data = [
                'gender' => __($event->task->issuer->gender->id . 'Salutation'),
                'name' => $this->buildName($event->task->issuer),
                'creatorGender' => __($user->gender->id . 'Salutation'),
                'creatorName' => $this->buildName($user),
                'description' => $event->task->name,
                'priority' => __($event->task->priority->name),
                'doneDue' =>  date('d.m.Y', strtotime($event->task->doneAt)),
                'location' => $location,
            ];
            $preferences = NotificationPreferences::where('userId', '=', $event->task->issuer->id)->first();

            if(is_null($preferences) || $preferences->taskCompletedMail == 1) {
                 Mail::to($event->task->issuer->email)->send(new CompleteTaskMail($data));
            }
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
