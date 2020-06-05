<?php

namespace App\Listeners\Audit;

use App\Audit;
use App\Events\Task\AuditAssignedEvent;
use App\Events\Task\AuditCompleteEvent;
use App\Notification;

class AuditCompletedSendNotificationListener
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct() {

    }

    /**
     * Handle the event.
     *
     * @param  AuditCompleteEvent $event
     * @return void
     */
    public function handle(AuditCompleteEvent $event) {
        /** @var Audit $audit */
        $audit = $event->audit;
        $assignee = $audit->userId;

        Notification::addNotification($assignee, $assignee, "", Notification::TITLE_AUDIT_COMPLETED, Notification::TITLE_AUDIT_COMPLETED, Notification::PERMISSION_NAME_AUDIT_COMPLETED);
    }
}
