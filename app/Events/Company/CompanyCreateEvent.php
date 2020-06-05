<?php

namespace App\Events\Company;

use App\Events\Event;
use App\Company;

class CompanyCreateEvent extends Event
{
	public $user;

    /**
     * Create a new event instance.
     *
     * @param User $user
     */
    public function __construct(Company $company)
    {
        $this->company = $company;
    }
}
