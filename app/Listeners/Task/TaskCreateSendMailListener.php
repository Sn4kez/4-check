<?php

namespace App\Listeners\Task;

use App\Events\Task\TaskCreateEvent;
use App\Mail\Task\CreateTaskMail;
use Illuminate\Support\Facades\Mail;

class TaskCreateSendMailListener
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
     * @param  TaskCreateEvent  $event
     * @return void
     */
    public function handle(TaskCreateEvent $event)
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
                'gender' => __($user->gender->id . 'Salutation'),
                'name' => $this->buildName($user),
                'creatorGender' => __($event->task->issuer->gender->id . 'Salutation'),
                'creatorName' => $this->buildName($event->task->issuer),
                'description' => $event->task->name,
                'priority' => __($event->task->priority->name),
                'doneDue' =>  date('d.m.Y', strtotime($event->task->doneAt)),
                'location' => $location,
            ];
            
            Mail::to($user->email)->send(new CreateTaskMail($data));
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
