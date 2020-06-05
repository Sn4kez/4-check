<?php

namespace App\Http\Controllers;

use App\TaskPriority;
use App\Company;
use App\Http\Resources\TaskPriorityResource;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Ramsey\Uuid\Uuid;

class TaskPriorityController extends Controller
{
    /**
     * @var TaskPriority $taskPriority
     */

    private $taskPriority;

    /**
     * Create a new controller instance.
     *
     * @param Task $loation
     */

    public function __contsruct(Task $taskPriority)
    {
        $this->taskPriority = $taskPriority;
    }

    /**
     * Create a new Task Priority.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */

    public function create(Request $request)
    {
    	$data = $this->validate($request, TaskPriority::rules('create'));

    	$company = Company::findOrFail($data['company']);

    	$taskPriority = new TaskPriority($data);
        $taskPriority->id = Uuid::uuid4()->toString();
    	$taskPriority->company()->associate($company);

        $this->authorize($taskPriority);

    	$taskPriority->save();

    	return TaskPriorityResource::make($taskPriority)
    		->response()
    		->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * Update a Task Priority.
     *
     * @param Request $request
     * @param string $taskPriorityId
     * @return \Illuminate\Http\JsonResponse
     */

    public function update(Request $request, string $taskPriorityId)
    {
        $taskPriority = TaskPriority::findOrFail($taskPriorityId);

        $this->authorize($taskPriority);

        $data = $this->validate($request, TaskPriority::rules('update'));

        if (array_key_exists('company', $data)) {
            $company = Company::findOrFail($data['company']);
            $taskPriority->company()->associate($company);
        }

        $taskPriority->update($data);

        return response('', Response::HTTP_NO_CONTENT);
    }

    /**
     * Create a new Task Priority.
     *
     * @param Request $request
     * @param string $taskPriorityId
     * @return \Illuminate\Http\JsonResponse
     */

    public function delete(Request $request, string $taskPriorityId)
    {
    	$taskPriority = TaskPriority::findOrFail($taskPriorityId);

        $this->authorize($taskPriority);

    	$taskPriority->delete();
    	return response('', Response::HTTP_NO_CONTENT);
    }

    /**
     * List Task Prioritys of company.
     *
     * @param string $companyId
     * @return \Illuminate\Http\JsonResponse
     */

    public function index(Request $request, string $companyId)
    {
        $company = Company::findOrFail($companyId);

        $taskPrioritys = TaskPriority::where('companyId', '=', $companyId)->orWhere('companyId', '=', null);

        $itemsPerPage = config('app.items_per_page');

        if(array_key_exists('numberItems', $request)) {
            $itemsPerPage = $request['numberItems'];
        }

        $taskPrioritys->paginate($itemsPerPage);
        return TaskPriorityResource::collection($taskPrioritys->get())
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    /**
     * View specified Task Priority.
     *
     * @param string $taskPriorityId
     * @return \Illuminate\Http\JsonResponse
     */

    public function view(string $taskPriorityId)
    {
        /* @var App\TaskPriority $taskPriority */
        $taskPriority = TaskPriority::findOrFail($taskPriorityId);

        $this->authorize($taskPriority);

        return TaskPriorityResource::make($taskPriority)
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }
}
