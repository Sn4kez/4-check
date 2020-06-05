<?php

namespace App\Events\Checklist;
use App\Events\Event;

class ChecklistEntryUpdatedEvent extends Event
{
	public $objectId;
	public $userId;
    public $time;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(string $objectId, string $userId, $time)
    {
        $this->objectId = $objectId;
        $this->userId = $userId;
        $this->time = $time;
    }
}
