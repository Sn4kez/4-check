<?php

namespace App\Console\Commands;

use App\InspectionPlan;
use Illuminate\Console\Command;
use App\Audit;
use App\Checklist;
use App\User;
use App\PlannedAudit;
use App\Events\Checklist\ChecklistDueEvent;
use App\Mail\Checklist\ChecklistDueMail;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;

class RemindChecklistDue extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'task:checklistdue';

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

        $plannedAudits = PlannedAudit::where('date', '=', $date->format('Y-m-d'))->groupBy('planId')->get();

        foreach($plannedAudits as $planned)
        {
            $this->info('Checklist found: '.$planned->checklistId);
            $checklist = Checklist::find($planned->checklistId);
            $plan = InspectionPlan::find($planned->planId);
            $user = User::find($plan->userId);
            if(!is_null($checklist) && !is_null($user)) {
                event(new ChecklistDueEvent($checklist, $user));
            }
        }
    }
}
