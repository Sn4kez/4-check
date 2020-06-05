<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Task;
use App\TaskState;
use App\Events\Task\TaskOverdueEvent;
use App\Mail\Checklist\ChecklistDueMail;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;

class RemindOverdueTasks extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'task:taskoverdue';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $date = Carbon::now();

        $state = TaskState::where('name', '=', 'todo')->first();

        $tasks = Task::where('doneAt', '<=', $date)->where('stateId', '=', $state->id)->get();

        foreach($tasks as $task)
        {
            $this->info('Task found: '.$task->name);
            event(new TaskOverdueEvent($task));
        }
    }
}
