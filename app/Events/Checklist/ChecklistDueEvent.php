<?php

namespace App\Events\Checklist;

use App\Audit;
use App\Checklist;
use App\User;
use App\Events\Event;

class ChecklistDueEvent extends Event
{
	public $audit;

    /**
     * Create a new event instance.
     *
     * @param Checklist $checklist
     */
    public function __construct(Checklist $checklist, User $user)
    {
        $this->checklist = $checklist;
        $this->user = $user;
    }
}
