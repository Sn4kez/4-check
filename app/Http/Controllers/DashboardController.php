<?php

namespace App\Http\Controllers;

use App\Audit;
use App\AuditState;
use App\PlannedAudit;
use App\Http\Resources\AuditResource;
use App\Http\Resources\TaskResource;
use App\Http\Resources\PlannedAuditResource;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Carbon\Carbon;

use App\Task;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use App\Dashboard;

class DashboardController extends Controller
{

    /**
     * @var Request
     */
    private $request;

    /**
     * @var string
     */
    private $filterDateFrom;

    /**
     * @var string
     */
    private $filterDateTo;

    /**
     * @var int
     */
    private $currentPage = 1;

    /**
     * @var int
     */
    const QUERY_LIMIT = 5;

    /**
     * @var string
     */
    const ERR_MESSAGE_INVALID_OBJECT = "invalid object given";

    private $nextAudits;
    private $lastAudits;
    private $tasks;

    public function index(Request $request)
    {
        $this->request = $request;

        $this->initFilters();

        $data = [
            'last_audits' => $this->getLastAudits(),
            'last_audits_total' => $this->lastAudits->total(),
            'last_audits_count' => $this->lastAudits->count(),
            'last_audits_pages' => $this->getPages($this->lastAudits->total()),

            'next_audits' => $this->getNextAudits(),
            'next_audits_total' => $this->nextAudits->total(),
            'next_audits_count' => $this->nextAudits->count(),
            'next_audits_pages' => $this->getPages($this->nextAudits->total()),

            'tasks' => $this->getTasks(),
            'tasks_total' => $this->tasks->total(),
            'tasks_count' => $this->tasks->count(),
            'tasks_pages' => $this->getPages($this->tasks->total()),

            'informations' => $this->getInformations()
        ];

        return response(['data' => $data], Response::HTTP_OK);
    }

    /**
     * Returns the count of pages by number of total objects
     *
     * @param $totalNumberOfObjects
     * @return int|mixed
     */
    private function getPages($totalNumberOfObjects)
    {
        $pages = max(floor($totalNumberOfObjects / self::QUERY_LIMIT), 1);

        if (($pages * self::QUERY_LIMIT) < $totalNumberOfObjects) {
            return ($pages + 1);
        }

        return $pages;
    }

    public function reload(Request $request, $object)
    {
        $this->request = $request;
        $this->initFilters();
        $validObjects = ["next_audits", "last_audits", "tasks"];

        if (in_array($object, $validObjects)) {
            $data = [];

            switch ($object) {
                case "next_audits":
                    $data['next_audits'] = $this->getNextAudits();
                    $data['next_audits_count'] = $this->nextAudits->count();
                    $data['next_audits_total'] = $this->nextAudits->total();
                    $data['next_audits_pages'] = $this->getPages($this->nextAudits->total());
                    break;
                case "last_audits":
                    $data['last_audits'] = $this->getLastAudits();
                    $data['last_audits_count'] = $this->lastAudits->count();
                    $data['last_audits_total'] = $this->lastAudits->total();
                    $data['last_audits_pages'] = $this->getPages($this->lastAudits->total());
                    break;
                case "tasks":
                    $data['tasks'] = $this->getTasks();
                    $data['tasks_count'] = $this->tasks->count();
                    $data['tasks_total'] = $this->tasks->total();
                    $data['tasks_pages'] = $this->getPages($this->tasks->total());
                    break;
            }

            return response(['data' => $data], Response::HTTP_OK);
        } else {
            throw new UnprocessableEntityHttpException(self::ERR_MESSAGE_INVALID_OBJECT);
        }
    }

    /**
     *
     */
    private function initFilters()
    {
        $this->filterDateFrom = null;
        $this->filterDateTo = null;
        $this->currentPage = 1;

        if ($this->request->has(Dashboard::REQUEST_PARAM_NAME_DATE_FROM) && $this->request->has(Dashboard::REQUEST_PARAM_NAME_DATE_TO)) {
            $this->filterDateFrom = $this->request->input(Dashboard::REQUEST_PARAM_NAME_DATE_FROM);
            $this->filterDateTo = date("Y-m-d 23:59:59.999", strtotime($this->request->input(Dashboard::REQUEST_PARAM_NAME_DATE_TO)));
        }

        if ($this->request->has(Dashboard::REQUEST_PARAM_NAME_PAGE)) {
            $this->currentPage = $this->request->input(Dashboard::REQUEST_PARAM_NAME_PAGE);
        }
    }

    /**
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    private function getLastAudits()
    {
        $state1 = AuditState::where('name', '=', 'finished')->first();
        $state2 = AuditState::where('name', '=', 'approved')->first();
        $state3 = AuditState::where('name', '=', 'awaiting approval')->first();

        $this->lastAudits = Audit::where('companyId', '=', $this->getUserCompanyId());
        $this->lastAudits->whereIn('stateId', [$state1->id, $state2->id, $state3->id]);


        if (!is_null($this->filterDateFrom) && !is_null($this->filterDateTo)) {
            $this->lastAudits = $this->lastAudits->whereBetween(Dashboard::COLUMN_NAME_RESPONSIBLE_FOR_DATE_FILTER, [$this->filterDateFrom, $this->filterDateTo]);
        }

        $this->lastAudits = $this->lastAudits->paginate(self::QUERY_LIMIT, ['*'], 'page', $this->currentPage);

        return AuditResource::collection($this->lastAudits);
    }

    /**
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    private function getNextAudits()
    {
        $this->nextAudits = PlannedAudit::whereHas('checklist', function($query){
            $query->where('companyId', '=', $this->getUserCompanyId());
        })
            ->where('date', '>', Carbon::now()->toDateString())
            ->orWhere(function($query) {
                $query->where('date', '=', Carbon::now()->toDateString())
                    ->where('endTime', '>=', Carbon::now()->toTimeString());
            })
            ->where('wasExecuted', '=', 0)
            ->orderBy('endTime', 'asc')
            ->orderBy('date', 'asc')
            ->paginate(self::QUERY_LIMIT, ['*'], 'page', $this->currentPage);

        return PlannedAuditResource::collection($this->nextAudits);
    }

    /**
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    private function getTasks()
    {
        $this->tasks = Task::where('assigneeId', '=', $this->getUserId())
            ->paginate(self::QUERY_LIMIT, ['*'], 'page', $this->currentPage);

        return TaskResource::collection($this->tasks);
    }

    private function getInformations()
    {
        return [];
    }

    private function getUserId()
    {
        return $this->request->user()->id;
    }

    private function getUserCompanyId()
    {
        return $this->request->user()->companyId;
    }
}
