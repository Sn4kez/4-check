<?php

namespace App\Http\Controllers;

use App\Task;
use App\TaskType;
use App\TaskPriority;
use App\TaskState;
use App\User;
use App\Location;
use App\Company;
use App\NotificationPreferences;
use App\Events\Task\TaskCreateEvent;
use App\Events\Task\TaskUpdateEvent;
use App\Events\Task\TaskDeleteEvent;
use App\Events\Task\TaskCompleteEvent;
use App\Events\Task\TaskAssignedEvent;
use App\Http\Resources\TaskResource;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Ramsey\Uuid\Uuid;
use App\Media;

class TaskController extends Controller
{

    /**
     * @var Task
     */

    private $task;


    private $oldState;
    private $oldAssignee;
    /**
     * Create a new controller instance.
     *
     * @param Task $task
     */

    public function __contsruct(Task $task) {
        $this->task = $task;
    }

    /**
     * Create a new task.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */

    public function create(Request $request) {
        $createdBy = $request->user();

        $data = $this->validate($request, Task::rules('create'));

        $location = NULL;
        $type = NULL;
        $priority = NULL;
        $state = NULL;

        if (array_key_exists('type', $data)) {
            $type = TaskType::find($data['type']);
        }
        if (array_key_exists('priority', $data)) {
            $priority = TaskPriority::find($data['priority']);
        }
        if (array_key_exists('state', $data)) {
            $state = TaskState::find($data['state']);
        }
        $issuer = User::findOrFail($data['issuer']);
        $assignee = User::findOrFail($data['assignee']);
        if (array_key_exists('location', $data)) {
            $location = Location::find($data['location']);
        }
        $company = Company::findOrFail($data['company']);

        $data = Media::processRequest($data);

        /* @var Task $task */
        $task = new Task($data);
        $task->id = Uuid::uuid4()->toString();
        if (!is_null($type)) {
            $task->type()->associate($type);
        }
        if (!is_null($priority)) {
            $task->priority()->associate($priority);
        }
        if (!is_null($state)) {
            $task->state()->associate($state);
        }
        $task->issuer()->associate($issuer);
        $task->assignee()->associate($assignee);
        if (!is_null($location)) {
            $task->location()->associate($location);
        }
        $task->company()->associate($company);

        $this->authorize('create',$task);

        $task->save();
        $this->fireTaskCreatedEvent($task);
        $this->fireTaskAssignedEvent($task);

        return TaskResource::make($task)
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * Update a task.
     *
     * @param Request $request
     * @param string $taskId
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */

    public function update(Request $request, string $taskId) {
        /* @var Task $task */
        $task = Task::findOrFail($taskId);

        $oldTask = $task;
        $this->oldState = $task->stateId;
        $this->oldAssignee = $task->assigneeId;

        $this->authorize($task);
        $data = $this->validate($request, Task::rules('update'));

        if (array_key_exists('type', $data)) {
            $type = TaskType::find($data['type']);
            $task->type()->associate($type);
        }

        if (array_key_exists('state', $data)) {
            $state = TaskState::find($data['state']);
            $task->state()->associate($state);
        }

        if (array_key_exists('priority', $data)) {
            $priority = TaskPriority::find($data['priority']);
            $task->priority()->associate($priority);
        }

        if (array_key_exists('issuer', $data)) {
            $issuer = User::findOrFail($data['issuer']);
            $task->issuer()->associate($issuer);
        }

        if (array_key_exists('assignee', $data)) {
            $assignee = User::findOrFail($data['assignee']);
            $task->assignee()->associate($assignee);
        }

        if (array_key_exists('location', $data)) {
            $location = Location::find($data['location']);
            $task->location()->associate($location);
        }

        if (array_key_exists('company', $data)) {
            $company = Company::findOrFail($data['company']);
            $task->company()->associate($company);
        }

        $data = Media::processRequest($data);

        $task->update($data);

        if ($this->isTaskNowDone($task)) {
            $this->fireTaskCompletedEventIfEventIsNowDone($task);
        } else {
            $this->fireTaskUpdateEvent($task, $oldTask);
        }
        $this->fireTaskAssignedEventIfAssigneeHasChanged($task);

        return response('', Response::HTTP_NO_CONTENT);
    }

    /**
     * Sets status of a set of tasks to done.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function setSetDone(Request $request) {
        $taskIds = $request->input('items');

        $state = TaskState::where('name', '=', 'done')->first();

        foreach ($taskIds as $taskId) {
            $task = Task::findOrFail($taskId);

            $this->authorize('update', $task);

            $task->state()->associate($state);
            $task->save();

            $this->fireTaskCompletedEvent($task);
        }

        return response('', Response::HTTP_NO_CONTENT);
    }

    /**
     * Returns if the current tasks state is set to DONE now!
     *
     * @param Task $task
     * @return bool%
     */
    private function isTaskNowDone(Task $task) {
        $stateIdBefore = $this->oldState;
        $stateId = $task->stateId;
        $stateIdDone = TaskState::where('name', '=', TaskState::NAME_DONE)->first()->id;

        return $stateId === $stateIdDone && $stateIdBefore !== $stateIdDone;
    }

    /**
     * Fires the completed notification event, when task is now done and was not before
     *
     * @param Task $task
     */
    private function fireTaskCompletedEventIfEventIsNowDone(Task $task) {
        $this->fireTaskCompletedEvent($task);
    }

    /**
     * Returns if the task assignee has changed or not
     *
     * @param Task $task
     * @return bool
     */
    private function hasTaskAssigneeChanged(Task $task) {
        $assigneeNow = $task->assigneeId;
        $assigneeBefore = $this->oldAssignee;

        return $assigneeBefore !== $assigneeNow;
    }

    /**
     * Fires the task update event
     *
     * @param Task $task
     */
    private function fireTaskAssignedEventIfAssigneeHasChanged(Task $task) {
        if ($this->hasTaskAssigneeChanged($task)) {
            $this->fireTaskAssignedEvent($task);
        }
    }

    /**
     * Fires the completed event
     *
     * @param Task $task
     */
    private function fireTaskAssignedEvent(Task $task) {
        event(new TaskAssignedEvent($task));
    }

    /**
     * Fires the completed event
     *
     * @param Task $task
     */
    private function fireTaskUpdateEvent(Task $task, Task $oldTask) {
        event(new TaskUpdateEvent($task, $oldTask));
    }

    /**
     * Fires the completed event
     *
     * @param Task $task
     */
    private function fireTaskDeletedEvent(Task $task) {
        event(new TaskDeleteEvent($task));
    }

    /**
     * Fires the completed event
     *
     * @param Task $task
     */
    private function fireTaskCompletedEvent(Task $task) {
        event(new TaskCompleteEvent($task));
    }

    /**
     * Fires the created event
     *
     * @param Task $task
     */

    private function fireTaskCreatedEvent(Task $task) {
        $settings = NotificationPreferences::where('userId', '=', $task->assignee->id)->first();

        if($settings->taskAssignedMail) {
            event(new TaskCreateEvent($task));
        }
    }

    /**
     * Deletes a task.
     *
     * @param Request $request
     * @param string $taskId
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */

    public function delete(Request $request, string $taskId) {
        /* @var Task $task */
        $task = Task::findOrFail($taskId);

        $this->authorize($task);

        $this->fireTaskDeletedEvent($task);

        $this->deleteItem($task);

        return response('', Response::HTTP_NO_CONTENT);
    }

    /**
     * Deletes a set of tasks.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */

    public function deleteSet(Request $request) {
        $taskIds = $request->input('items');

        foreach ($taskIds as $taskId) {
            $task = Task::findOrFail($taskId);

            $this->authorize('delete', $task);

            $this->fireTaskDeletedEvent($task);

            $this->deleteItem($task);
        }

        return response('', Response::HTTP_NO_CONTENT);
    }

    /**
     * Deletes a task.
     *
     */
    private function deleteItem(Task $task) {
        $task->delete();
    }

    /**
     * List a user's tasks.
     *
     * @param Request $request
     * @param string|null $companyId
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request, string $companyId = NULL) {
        /* @var User $user */
        $user = $request->user();

        $company = Company::findOrFail($user->company->id);

        if (!is_null($companyId)) {
            $company = Company::findOrFail($companyId);
        }

        $tasks = Task::where('companyId', '=', $company->id);

        $tasks = $this->filterRequest($request->all(), $tasks, $user);

        if (is_null($tasks)) {
            $tasks = Task::where('companyId', '=', 'null');
        }

        $tasks->each(function ($item) {
            $temp = Media::changeImageFilenameToBase64String($item);
            $item->update(['image' => $temp->image]);
        });

        return TaskResource::collection($tasks->get())
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    /**
     * filters a users Tasks
     *
     * @param $request
     * @param $tasks
     * @param $user
     * @return  $tasks
     */
    private function filterRequest($request, $tasks, $user) {
        if (array_key_exists('state', $request)) {
            $tasks = $this->filterByState($request['state'], $tasks);
        }

        if (array_key_exists('type', $request)) {
            $tasks = $this->filterByType($request['type'], $tasks);
        }

        if (array_key_exists('priority', $request)) {
            $tasks = $this->filterByPriority($request['priority'], $tasks);
        }

        if (array_key_exists('location', $request)) {
            $tasks = $this->filterByLocation($request['location'], $tasks);
        }

        if (array_key_exists('name', $request)) {
            $tasks = $this->filterByName($request['name'], $tasks);
        }

        if (array_key_exists('start', $request) && array_key_exists('end', $request)) {
            $tasks = $this->filterByTimePeriod($request['start'], $request['end'], $tasks);
        }

        if ($user->isAdmin() || $user->isSuperAdmin()) {
            if (array_key_exists('issuer', $request)) {
                $tasks = $this->filterByIssuer($request['issuer'], $tasks);
            }

            if (array_key_exists('assignee', $request)) {
                $tasks = $this->filterByAssignee($request['assignee'], $tasks);
            }

            return $tasks;
        }

        if (!array_key_exists('issuer', $request) && !array_key_exists('assignee', $request)) {
            $tasks = $this->filterUserTasks($user, $tasks);
        }

        if (array_key_exists('issuer', $request)) {
            $tasks = $this->filterByIssuer($request['issuer'], $tasks);
            $tasks = $this->filterByAssignee($user->id, $tasks);
        }

        if (array_key_exists('assignee', $request)) {
            $tasks = $this->filterByIssuer($user->id, $tasks);
            $tasks = $this->filterByAssignee($request['assignee'], $tasks);
        }

        if (array_key_exists('issuer', $request) && array_key_exists('assignee', $request)) {
            $tasks = NULL;
        }

        return $tasks;
    }

    private function filterUserTasks($user, $tasks) {
        return $tasks->where(function ($query) use ($user) {
            $query->where('issuerId', '=', $user->id);
            $query->orWhere('assigneeId', '=', $user->id);
        });
    }

    private function filterByState($state, $tasks) {
        return $tasks->where('stateId', '=', $state);
    }

    private function filterByType($type, $tasks) {
        return $tasks->where('typeId', '=', $type);
    }

    private function filterByPriority($priority, $tasks) {
        return $tasks->where('priorityId', '=', $priority);
    }

    private function filterByIssuer($issuer, $tasks) {
        return $tasks->where('issuerId', '=', $issuer);
    }

    private function filterByAssignee($assignee, $tasks) {
        return $tasks->where('assigneeId', '=', $assignee);
    }

    private function filterByLocation($location, $tasks) {
        return $tasks->where('locationId', '=', $location);
    }

    private function filterByName($name, $tasks) {
        return $tasks->where('name', 'LIKE', '%' . $name . '%');
    }

    private function filterByTimePeriod($start, $end, $tasks) {
        return $tasks->where(function ($query) use ($start, $end) {
            $query->whereBetween('doneAt', [$start, $end]);
            $query->orWhereBetween('createdAt', [$start, $end]);
        });

        return $tasks->whereBetween('doneAt', [$start, $end]);
    }

    /**
     * View a task.
     *
     * @param string $taskId
     * @return \Illuminate\Http\JsonResponse
     */
    public function view(string $taskId) {
        /* @var Task $task */
        $task = Media::changeImageFilenameToBase64String(Task::findOrFail($taskId));

        $this->authorize($task);

        return TaskResource::make($task)
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }
}
