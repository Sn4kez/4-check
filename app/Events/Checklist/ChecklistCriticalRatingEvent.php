<?php

namespace App\Events\Checklist;

use App\Checklist;
use App\Audit;
use App\User;
use App\Events\Event;

class ChecklistCriticalRatingEvent extends Event
{
    public $audit;
	public $checklist;
    public $user;
    public $ratings;

    /**
     * Create a new event instance.
     *
     * @param Checklist $checklist
     */
    public function __construct(Checklist $checklist, User $user, Audit $audit,  $ratings)
    {
        $this->audit = $audit;
        $this->checklist = $checklist;
        $this->user = $user;
        $this->ratings = $ratings;
    }
}
