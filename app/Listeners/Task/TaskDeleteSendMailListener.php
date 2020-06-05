<?php

namespace App\Listeners\Task;

use App\Events\Task\TaskDeleteEvent;
use App\Mail\Task\DeleteTaskMail;
use Illuminate\Support\Facades\Mail;
use App\NotificationPreferences;

class TaskDeleteSendMailListener
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
     * @param  TaskTaskDeleteEvent  $event
     * @return void
     */
    public function handle(TaskDeleteEvent $event)
    {
        $users = [
            $event->task->assignee,
        ];

        foreach($users as $user)
        {
            $data = [
                'gender' => __($user->gender->id . 'Salutation'),
                'name' => $this->buildName($user),
                'description' => $event->task->name,
            ];

            $preferences = NotificationPreferences::where('userId', '=', $user->id)->first();

            if(is_null($preferences) || $preferences->taskDeletedMail == 1) {
                 Mail::to($user->email)->send(new DeleteTaskMail($data));
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
