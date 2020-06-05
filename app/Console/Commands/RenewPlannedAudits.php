<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\InspectionPlan;
use App\PlannedAudit;
use Recurr\Rule;
use Recurr\Transformer\ArrayTransformer;
use Recurr\Transformer\ArrayTransformerConfig;
use App\Mail\Checklist\ChecklistDueMail;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use \Datetime;

class RenewPlannedAudits extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'task:plannedaudits';

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
        $date = Carbon::now()->addMonths(1);

        $plans = InspectionPlan::where('lastGeneratedDate', '<', $date)->get();

        foreach($plans as $plan)
        {
            $freq = 'HOURLY';

            if($plan->type == 'daily') {
                $freq = 'DAILY';
            } else if($plan->type == 'weekly') {
                $freq = 'WEEKLY';
            } else if ($plan->type == 'monthly') {
                $freq = 'MONTHLY';
            }

            $days = [];

            if($plan->monday == 1) {
                $days[] = 'MO';
            }

            if($plan->tuesday == 1) {
                $days[] = 'TU';
            }

            if($plan->wednesday == 1) {
                $days[] = 'WE';
            }

            if($plan->thursday == 1) {
                $days[] = 'TH';
            }

            if($plan->friday == 1) {
                $days[] = 'FR';
            }

            if($plan->saturday == 1) {
                $days[] = 'SA';
            }

            if($plan->sunday == 1) {
                $days[] = 'SU';
            }

            $startDate = new DateTime($plan->lastGeneratedDate.' 0:00:00');
            $today = new DateTime();
            $today->setTime(0,0,0);

            if($startDate < $today) {
                $startDate = $today;
            }

            $rule = new Rule();
            $rule->setFreq($freq);
            $rule->setStartDate($startDate);

            $untilDate = new DateTime($startDate->format('Y-m-d'));
            $untilDate->modify('+1 month');

            //ToDo: check if end date of date is before $untildate and use the one that happens earlier
            $rule->setUntil($untilDate);



            $endDate = $untilDate;

            if(count($days) > 0) {
                $rule->setByDay($days);
            }

            $config = new ArrayTransformerConfig();
            $config->enableLastDayOfMonthFix();

            $transformer = new ArrayTransformer();
            $transformer->setConfig($config);
            $dateArray = $transformer->transform($rule);

            $lastDate = null;
            $nextDate = null;
            foreach($dateArray as $date) {
                $nextDate = $date->getStart()->format('Y-m-d H:i:s');

                if($nextDate != $startDate->format('Y-m-d H:i:s'))
                {
                    $plannedAudit = new PlannedAudit();
                    $plannedAudit->checklistId = $plan->checklistId;
                    $plannedAudit->planId = $plan->id;
                    $plannedAudit->date = $nextDate;
                    $plannedAudit->startTime = $plan->startTime;
                    $plannedAudit->endTime = $plan->endTime;
                    $plannedAudit->save();

                    $lastDate = $nextDate;
                }
            }

            $plan->lastGeneratedDate = $lastDate;
            $plan->save(); 
        }
    }
}
