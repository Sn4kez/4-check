<?php

namespace App\Listeners\Checklist;

use App\Events\Checklist\ChecklistEscalationEvent;

class EscalationSendNotificationListener
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
     * @param  ChecklistChecklistEscalationEvent  $event
     * @return void
     */
    public function handle(ChecklistChecklistEscalationEvent $event)
    {
        //
    }
}
