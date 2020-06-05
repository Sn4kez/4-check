<?php

namespace App\Listeners\Task;

use App\Events\Task\TaskCompleteEvent;
use App\Notification;
use App\Task;

class TaskCompleteSendNotificationListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct() {
        //
    }

    /**
     * Handle the event.
     *
     * @param TaskCompleteEvent $event
     * @return void
     */
    public function handle(TaskCompleteEvent $event) {
        /** @var Task $task */
        $task = $event->task;
        $assignee = $task->assigneeId;
        $message = sprintf(Notification::MESSAGE_TASK_COMPLETED, $task->name);

        Notification::addNotification($assignee, $assignee, "", Notification::TITLE_TASK_COMPLETED, $message, Notification::PERMISSION_NAME_TASK_COMPLETED);
    }
}
