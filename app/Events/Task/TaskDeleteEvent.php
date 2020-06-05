<?php

namespace App\Events\Task;

use App\Events\Event;
use App\Task;

class TaskDeleteEvent extends Event
{
	public $task;

    /**
     * Create a new event instance.
     *
     * @param Task $task
     */
    public function __construct(Task $task)
    {
        $this->task = $task;
    }
}
