<?php

namespace App\Http\Controllers;

use App\TaskType;
use App\Company;
use App\Http\Resources\TaskTypeResource;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Ramsey\Uuid\Uuid;

class TaskTypeController extends Controller
{
    /**
     * @var TaskType $taskType
     */

    private $taskType;

    /**
     * Create a new controller instance.
     *
     * @param Task $loation
     */

    public function __contsruct(Task $taskType)
    {
        $this->taskType = $taskType;
    }

    /**
     * Create a new Task type.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */

    public function create(Request $request)
    {
    	$data = $this->validate($request, TaskType::rules('create'));

    	$company = Company::findOrFail($data['company']);

    	$taskType = new TaskType($data);
        $taskType->id = Uuid::uuid4()->toString();
    	$taskType->company()->associate($company);

        $this->authorize($taskType);

    	$taskType->save();

    	return TaskTypeResource::make($taskType)
    		->response()
    		->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * Update a Task type.
     *
     * @param Request $request
     * @param string $taskTypeId
     * @return \Illuminate\Http\JsonResponse
     */

    public function update(Request $request, string $taskTypeId)
    {
        $taskType = TaskType::findOrFail($taskTypeId);

        $this->authorize($taskType);

        $data = $this->validate($request, TaskType::rules('update'));

        if (array_key_exists('company', $data)) {
            $company = Company::findOrFail($data['company']);
            $taskType->company()->associate($company);
        }

        $taskType->update($data);

        return response('', Response::HTTP_NO_CONTENT);
    }

    /**
     * Create a new Task type.
     *
     * @param Request $request
     * @param string $taskTypeId
     * @return \Illuminate\Http\JsonResponse
     */

    public function delete(Request $request, string $taskTypeId)
    {
    	$taskType = TaskType::findOrFail($taskTypeId);

        $this->authorize($taskType);

    	$taskType->delete();
    	return response('', Response::HTTP_NO_CONTENT);
    }

    /**
     * List Task types of company.
     *
     * @param string $companyId
     * @return \Illuminate\Http\JsonResponse
     */

    public function index(Request $request, string $companyId)
    {
        $company = Company::findOrFail($companyId);

        $taskTypes = TaskType::where('companyId', '=', $companyId)->orWhere('companyId', '=', null);

        $itemsPerPage = config('app.items_per_page');

        if(array_key_exists('numberItems', $request)) {
            $itemsPerPage = $request['numberItems'];
        }

        $taskTypes->paginate($itemsPerPage);
        return TaskTypeResource::collection($taskTypes->get())
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    /**
     * View specified Task type.
     *
     * @param string $taskTypeId
     * @return \Illuminate\Http\JsonResponse
     */

    public function view(string $taskTypeId)
    {
        /* @var App\TaskType $taskType */
        $taskType = TaskType::findOrFail($taskTypeId);

        $this->authorize($taskType);

        return TaskTypeResource::make($taskType)
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }
}
