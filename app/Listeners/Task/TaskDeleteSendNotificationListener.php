<?php

namespace App\Listeners\Task;

use App\Events\Task\TaskDeleteEvent;
use App\Task;
use App\Notification;

class TaskDeleteSendNotificationListener
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
     * @param TaskDeleteEvent $event
     * @return void
     */
    public function handle(TaskDeleteEvent $event)
    {
        /** @var Task $task */
        $task = $event->task;
        $assignee = $task->assigneeId;
        $message = sprintf(Notification::MESSAGE_TASK_DELETED, $task->name);

        Notification::addNotification($assignee, $assignee, "", Notification::TITLE_TASK_DELETED, $message, Notification::PERMISSION_NAME_TASK_DELETED);
    }
}
