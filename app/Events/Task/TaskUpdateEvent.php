<?php

namespace App\Events\Task;

use App\Events\Event;
use App\Task;

class TaskUpdateEvent extends Event
{
	public $task;
	public $oldTask;

    /**
     * Create a new event instance.
     *
     * @param Task $task
     */
    public function __construct(Task $task, Task $oldTask)
    {
        $this->task = $task;
        $this->oldTask = $oldTask;
    }
}
