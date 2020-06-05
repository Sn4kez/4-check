<?php

namespace App\Listeners\Task;

use App\Events\Task\TaskUpdateEvent;
use App\Mail\Task\UpdateTaskMail;
use Illuminate\Support\Facades\Mail;
use App\NotificationPreferences;

class TaskUpdateSendMailListener
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
     * @param  TaskTaskUpdateEvent  $event
     * @return void
     */
    public function handle(TaskUpdateEvent $event)
    {
        $users = [
            $event->task->assignee,
        ];

        $oldDescription = '';
        $oldPriority = '';
        $oldDoneDue = '';
        $oldLocation = '';
        $location = '';

        if($event->task->name != $event->oldTask->name) {
            $oldDescription = $event->oldTask->name;
        }

        if($event->task->priority->name != $event->oldTask->priority->name) {
            $oldPriority = $event->oldTask->priority->name;
        }

        if($event->task->doneAt != $event->oldTask->doneAt) {
            $oldDoneDue = $event->oldTask->doneAt;
        }

        if($event->task->location()->exists()) {
            $location = $event->task->location->name;

            if($event->task->location->id != $event->oldTask->location->id) {
                $oldLocation = $event->oldTask->location->name;
            }
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
                'oldDescription' => $oldDescription,
                'oldPriority' => $oldPriority,
                'oldDoneDue' => date('d.m.Y', strtotime($oldDoneDue)),
                'oldLocation' => $oldLocation,
            ];
            $preferences = NotificationPreferences::where('userId', '=', $user->id)->first();;

            if(is_null($preferences) || $preferences->taskUpdatedMail == 1) {
                 Mail::to($user->email)->send(new UpdateTaskMail($data));
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
