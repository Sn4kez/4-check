<?php

namespace App\Listeners\Audit;

use App\Audit;
use App\Events\Task\AuditAssignedEvent;
use App\Events\Task\AuditCompleteEvent;
use App\Events\Task\AuditReleaseRequiredEvent;
use App\Notification;

class AuditReleaseRequiredSendNotificationListener
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
     * @param AuditReleaseRequiredEvent $event
     * @return void
     */
    public function handle(AuditReleaseRequiredEvent $event) {
        /** @var Audit $audit */
        $audit = $event->audit;
        $assignee = $audit->userId;

        Notification::addNotification($assignee, $assignee, "", Notification::TITLE_AUDIT_RELEASE_REQUIRED, Notification::TITLE_AUDIT_RELEASE_REQUIRED, Notification::PERMISSION_NAME_AUDIT_RELEASE_REQUIRED);
    }
}
