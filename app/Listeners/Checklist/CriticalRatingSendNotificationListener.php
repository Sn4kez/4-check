<?php

namespace App\Listeners\Checklist;

use App\Events\Checklist\ChecklistCriticalRatingEvent;

class CriticalRatingSendNotificationListener
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
     * @param  ChecklistChecklistCriticalRatingEvent  $event
     * @return void
     */
    public function handle(ChecklistChecklistCriticalRatingEvent $event)
    {
        //
    }
}
