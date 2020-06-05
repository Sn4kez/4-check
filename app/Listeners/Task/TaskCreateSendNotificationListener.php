<?php

namespace App\Listeners\Task;

use App\Events\Task\TaskCreateEvent;

class TaskCreateSendNotificationListener
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
        //
    }
}
