<?php

namespace App\Http\Controllers;

use App\TaskState;
use App\Company;
use App\Http\Resources\TaskStateResource;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Ramsey\Uuid\Uuid;

class TaskStateController extends Controller
{
    /**
     * @var TaskState $taskState
     */

    private $taskState;

    /**
     * Create a new controller instance.
     *
     * @param Task $loation
     */

    public function __contsruct(Task $taskState)
    {
        $this->taskState = $taskState;
    }

    /**
     * Create a new Task State.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */

    public function create(Request $request)
    {
    	$data = $this->validate($request, TaskState::rules('create'));

    	$company = Company::findOrFail($data['company']);

    	$taskState = new TaskState($data);
        $taskState->id = Uuid::uuid4()->toString();
    	$taskState->company()->associate($company);

        $this->authorize($taskState);

    	$taskState->save();

    	return TaskStateResource::make($taskState)
    		->response()
    		->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * Update a Task State.
     *
     * @param Request $request
     * @param string $taskStateId
     * @return \Illuminate\Http\JsonResponse
     */

    public function update(Request $request, string $taskStateId)
    {
        $taskState = TaskState::findOrFail($taskStateId);

        $this->authorize($taskState);

        $data = $this->validate($request, TaskState::rules('update'));

        if (array_key_exists('company', $data)) {
            $company = Company::findOrFail($data['company']);
            $taskState->company()->associate($company);
        }

        $taskState->update($data);

        return response('', Response::HTTP_NO_CONTENT);
    }

    /**
     * Create a new Task State.
     *
     * @param Request $request
     * @param string $taskStateId
     * @return \Illuminate\Http\JsonResponse
     */

    public function delete(Request $request, string $taskStateId)
    {
    	$taskState = TaskState::findOrFail($taskStateId);

        $this->authorize($taskState);

    	$taskState->delete();
    	return response('', Response::HTTP_NO_CONTENT);
    }

    /**
     * List Task States of company.
     *
     * @param string $companyId
     * @return \Illuminate\Http\JsonResponse
     */

    public function index(Request $request, string $companyId)
    {
        $company = Company::findOrFail($companyId);

        $taskStates = TaskState::where('companyId', '=', $companyId)->orWhere('companyId', '=', null);

        $itemsPerPage = config('app.items_per_page');

        if(array_key_exists('numberItems', $request)) {
            $itemsPerPage = $request['numberItems'];
        }

        $taskStates->paginate($itemsPerPage);
        return TaskStateResource::collection($taskStates->get())
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    /**
     * View specified Task State.
     *
     * @param string $taskStateId
     * @return \Illuminate\Http\JsonResponse
     */

    public function view(string $taskStateId)
    {
        /* @var App\TaskState $taskState */
        $taskState = TaskState::findOrFail($taskStateId);

        $this->authorize($taskState);

        return TaskStateResource::make($taskState)
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }
}
