<?php

namespace App\Listeners\Checklist;

use App\Events\Checklist\ChecklistDueEvent;

class DueSendNotificationListener
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
     * @param  ChecklistChecklistDueEvent  $event
     * @return void
     */
    public function handle(ChecklistChecklistDueEvent $event)
    {
        //
    }
}
