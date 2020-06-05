<?php

namespace App\Listeners\Audit;

use App\Audit;
use App\Events\Task\AuditAssignedEvent;
use App\Notification;

class AuditAssignedSendNotificationListener
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
     * @param  AuditAssignedEvent $event
     * @return void
     */
    public function handle(AuditAssignedEvent $event) {
        /** @var Audit $audit */
        $audit = $event->audit;
        $assignee = $audit->userId;

        Notification::addNotification($assignee, $assignee, "", Notification::TITLE_AUDIT_ASSIGNED, Notification::TITLE_AUDIT_ASSIGNED, Notification::PERMISSION_NAME_AUDIT_ASSIGNED);
    }
}
