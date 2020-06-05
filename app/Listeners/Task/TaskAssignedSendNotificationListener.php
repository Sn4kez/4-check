<?php

namespace App\Listeners\Task;

use App\Events\Task\TaskAssignedEvent;
use App\Notification;
use App\Task;

class TaskAssignedSendNotificationListener
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
     * @param TaskAssignedEvent $event
     * @return void
     */
    public function handle(TaskAssignedEvent $event)
    {
        /** @var Task $task */
        $task = $event->task;
        $assignee = $task->assigneeId;
        $message = sprintf(Notification::MESSAGE_TASK_ASSIGNED, $task->name);

        Notification::addNotification($assignee, $assignee, "", Notification::TITLE_TASK_ASSIGNED, $message, Notification::PERMISSION_NAME_TASK_ASSIGNED);
    }
}
