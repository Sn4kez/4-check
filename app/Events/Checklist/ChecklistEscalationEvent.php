<?php

namespace App\Events\Checklist;

use App\Checklist;
use App\User;
use App\Audit;
use App\Events\Event;

class ChecklistEscalationEvent extends Event
{
	  public $checklist;
    public $user;
    public $audit;
    
    /**
     * Create a new event instance.
     *
     */
    public function __construct(Checklist $checklist, User $user, Audit $audit)
    {
       $this->checklist = $checklist;
       $this->user = $user;
       $this->audit = $audit;

    }
}
