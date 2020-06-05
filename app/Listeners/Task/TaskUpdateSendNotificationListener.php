<?php

namespace App\Listeners\Task;

use App\Events\Task\TaskUpdateEvent;
use App\Notification;
use App\Task;

class TaskUpdateSendNotificationListener
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
     * @param TaskUpdateEvent $event
     * @return void
     */
    public function handle(TaskUpdateEvent $event)
    {
        /** @var Task $task */
        $task = $event->task;
        $assignee = $task->assigneeId;
        $message = sprintf(Notification::MESSAGE_TASK_UPDATED, $task->name);

        Notification::addNotification($assignee, $assignee, "", Notification::TITLE_TASK_UPDATED, $message, Notification::PERMISSION_NAME_TASK_UPDATED);
    }
}
