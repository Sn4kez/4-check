<?php

namespace App\Http\Controllers;

use App\InspectionPlan;
use App\Checklist;
use App\User;
use App\PlannedAudit;
use App\Company;
use App\Audit;
use App\AuditState;
use App\Http\Resources\InspectionPlanResource;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Ramsey\Uuid\Uuid;
use Recurr\Rule;
use Recurr\Transformer\ArrayTransformer;
use Recurr\Transformer\ArrayTransformerConfig;
use \Datetime;
use \DateInterval;

class InspectionPlanController extends Controller
{
    /**
     * @var InspectionPlan
     */

    private $plan;

    /**
     * Create a new controller instance.
     *
     * @param InspectionPlan $plan
     */

    public function __contsruct(InspectionPlan $plan) {
        $this->plan = $plan;
    }

    /**
     * Create a new inspection plan.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */

    public function create(Request $request) {
    	$data = $this->validate($request, InspectionPlan::rules('create'));

    	$plan = new InspectionPlan($data);
        $checklist = Checklist::findOrFail($data['checklist']);
        $user = User::findOrFail($data['user']);
        $company = Company::findOrFail($data['company']);
    	$plan->checklist()->associate($checklist);
    	$plan->user()->associate($user);
    	$plan->company()->associate($company);

    	$this->authorize($plan);

    	$plan->save();

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

        $startDate = new DateTime($plan->startDate.' 0:00:00');
        if(!is_null($plan->endDate)) {
            $endDate = new DateTime($plan->endDate.' 0:00:00');
        } else {
            $endDate = null;
        }

        $rule = new Rule();
        $rule->setFreq($freq);
        $rule->setStartDate($startDate);

        if(!is_null($endDate)) {
            $rule->setUntil($endDate);
        } else {
            $untilDate = new DateTime($startDate->format('Y-m-d'));
            $untilDate->modify('+1 month');
            $rule->setUntil($untilDate);

            $endDate = $untilDate;
        }

        if(count($days) > 0) {
            $rule->setByDay($days);
        }

        $config = new ArrayTransformerConfig();
        $config->enableLastDayOfMonthFix();

        $transformer = new ArrayTransformer();
        $transformer->setConfig($config);
        $dateArray = $transformer->transform($rule)->toArray();

        $nextDate = null;

        foreach($dateArray as $date) {
            $nextDate = $date->getStart()->format('Y-m-d');

            $plannedAudit = new PlannedAudit();
            $plannedAudit->checklistId = $plan->checklistId;
            $plannedAudit->planId = $plan->id;
            $plannedAudit->date = $nextDate;
            $plannedAudit->startTime = $plan->startTime;
            $plannedAudit->endTime = $plan->endTime;
            $plannedAudit->save();

        }

        $plan->lastGeneratedDate = $nextDate;
        $plan->save();

    	return InspectionPlanResource::make($plan)
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * Update a inspection plan.
     *
     * @param Request $request
     * @param string $planId
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */

    public function update(Request $request, string $planId)
    {
    	$plan = InspectionPlan::findOrFail($planId);

    	$this->authorize($plan);

    	$data = $this->validate($request, InspectionPlan::rules('update'));

    	if (array_key_exists('checklist', $data)) {
    		$checklist = Checklist::findOrFail($data['checklist']);
            $plan->checklist()->associate($checklist);
        }

        if (array_key_exists('user', $data)) {
        	$user = User::findOrFail($data['user']);
            $plan->user()->associate($user);
        }

        $plan->update($data);

        return response('', Response::HTTP_NO_CONTENT);
    }

    /**
     * Deletes a task.
     *
     * @param Request $request
     * @param string $planId
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */

    public function delete(Request $request, string $planId)
    {
    	$plan = InspectionPlan::findOrFail($planId);

    	$this->authorize($plan);

    	$plan->delete();

    	PlannedAudit::where("planId", '=', $planId)->delete();

    	return response('', Response::HTTP_NO_CONTENT);
    }

    /**
     * List a companies plan.
     *
     * @param Request $request
     * @param string|null $companyId
     * @return \Illuminate\Http\JsonResponse
     */

    public function index(Request $request, string $companyId = NULL)
    {
    	$user = $request->user();
        $data = $request->all();

        $company = Company::findOrFail($user->company->id);

        if (!is_null($companyId)) {
            $company = Company::findOrFail($companyId);
        }

        $plans = InspectionPlan::where('companyId', '=', $company->id);

        $this->authorize('view', $company);

        $plans = InspectionPlan::where('companyId', '=', $company->id);

        if(array_key_exists('checklist', $data)) {
            $plans->where('checklistId', '=', $data['checklist']);
        }

        return InspectionPlanResource::collection($plans->get())
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    /**
     * View a task.
     *
     * @param string $planId
     * @return \Illuminate\Http\JsonResponse
     */

    public function view(Request $request, string $planId)
    {
    	$plan = InspectionPlan::findOrFail($planId);

    	$this->authorize($plan);

    	return InspectionPlanResource::make($plan)
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }
}
