<?php

namespace App\Listeners\Task;

use App\Events\Task\TaskOverdueEvent;
use App\Mail\Task\OverdueTaskMail;
use Illuminate\Support\Facades\Mail;

class TaskOverdueSendMailListener
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
     * @param  TaskTaskOverdueEvent  $event
     * @return void
     */
    public function handle(TaskOverdueEvent $event)
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
                'doneDue' =>  date('d.m.Y', strtotime($event->task->doneAt)),
            ];
            Mail::to($user->email)->send(new OverdueTaskMail($data));
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
