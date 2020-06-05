<?php

namespace App\Listeners\Task;

use App\Events\Task\TaskOverdueEvent;

class TaskOverdueSendNotificationListener
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
        //
    }
}
