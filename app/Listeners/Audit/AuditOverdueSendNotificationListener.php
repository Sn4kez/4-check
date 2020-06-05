<?php

namespace App\Listeners\Audit;

use App\Audit;
use App\Events\Task\AuditAssignedEvent;
use App\Events\Task\AuditCompleteEvent;
use App\Events\Task\AuditOverdueEvent;
use App\Notification;

class AuditOverdueSendNotificationListener
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
     * @param AuditOverdueEvent $event
     * @return void
     */
    public function handle(AuditOverdueEvent $event) {
        /** @var Audit $audit */
        $audit = $event->audit;
        $assignee = $audit->userId;

        Notification::addNotification($assignee, $assignee, "", Notification::TITLE_AUDIT_OVERDUE, Notification::TITLE_AUDIT_OVERDUE, Notification::PERMISSION_NAME_AUDIT_OVERDUE);
    }
}
