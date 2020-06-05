<?php

namespace App\Events\Task;

use App\Events\Event;
use App\Audit;

class AuditCompleteEvent extends Event
{
    public $audit;

    /**
     * Create a new event instance.
     *
     * @param Audit $audit
     */
    public function __construct(Audit $audit) {
        $this->audit = $audit;
    }
}
