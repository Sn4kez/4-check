<?php

namespace App\Http\Controllers;

use App\Checklist;
use App\Directory;
use App\User;
use App\Group;
use App\InspectionPlan;
use App\PlannedAudit;
use App\Http\Resources\ChecklistEntryResource;
use App\Http\Resources\ChecklistResource;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Carbon;
use App\Events\Checklist\ChecklistUpdatedEvent;

class ChecklistController extends Controller {
    /**
     * @var Checklist
     */
    protected $checklist;

    /**
     * @var Directory
     */
    protected $directory;

    /**
     * Create a new controller instance.
     *
     * @param Checklist $checklist
     * @param Directory $directory
     */
    public function __construct(Checklist $checklist, Directory $directory) {
        $this->checklist = $checklist;
        $this->directory = $directory;
    }

    /**
     * Create a new checklist.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function create(Request $request) {
        $data = $this->validate($request, Checklist::rules('create'));
        $parent = null;

        /** @var Directory $parent */
        if (!is_null($data['parentId'])) {
            $parent = $this->directory->find($data['parentId']);
            $this->authorize('update', $parent);
        }

        $checklist = new Checklist($data);
        $checklist->company()->associate(Auth::user()->company);
        $checklist->createdByUser()->associate($request->user());
        $checklist->lastUpdatedByUser()->associate($request->user());

        if ($checklist->numberQuestions > 0) {
            $checklist->chooseRandom = 1;
        }

        $checklist->save();

        if ($parent !== null) {
            $parent->entry($checklist)->save();
        }

        if ($request->has('approvers')) {
            $approvers = $request->input('approvers');

            foreach ($approvers as $approver) {
                $object = null;
                if ($approver['objectType'] == 'user') {
                    $object = User::findOrFail($approver['objectId']);
                } else if ($approver['objectType'] == 'group') {
                    $object = UserGroup::findOrFail($approver['objectId']);
                }
                $object->approvables()->attach($checklist);
            }
        }

        return ChecklistResource::make($checklist)->response()->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * List a checklist's entries.
     *
     * @param string $checklistId
     * @return \Illuminate\Http\JsonResponse
     */
    public function indexEntries(string $checklistId) {
        /* @var Checklist $checklist */
        $checklist = $this->checklist->findOrFail($checklistId);
        $this->authorize('index', $checklist);
        return ChecklistEntryResource::collection($checklist->entries)->response()->setStatusCode(Response::HTTP_OK);
    }

    /**
     * View a checklist.
     *
     * @param string $checklistId
     * @return \Illuminate\Http\JsonResponse
     */
    public function view(string $checklistId) {
        /* @var Checklist $checklist */
        $checklist = $this->checklist->findOrFail($checklistId);
        $this->authorize('view', $checklist);
        return ChecklistResource::make($checklist)->response()->setStatusCode(Response::HTTP_OK);
    }

    /**
     * Update a checklist.
     *
     * @param Request $request
     * @param string $checklistId
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, string $checklistId) {
        /* @var Checklist $checklist */
        $checklist = $this->checklist->findOrFail($checklistId);
        $this->authorize('update', $checklist);
        $data = $this->validate($request, Checklist::rules('update'));

        if (array_key_exists('parentId', $data)) {
            $this->authorize('update', $checklist->parentEntry->parent);
            $newParent = $this->directory->findOrFail($data['parentId']);
            $this->authorize('update', $newParent);
            $checklist->parentEntry()->delete();
            $newParent->entry($checklist)->save();
        }

        $user = $request->user();
        $checklist->lastUpdatedByUser()->associate($user);
        $updateTime = Carbon::now();
        $checklist->updatedAt = $updateTime;

        if (array_key_exists('numberQuestions', $data)) {
            if ($data['numberQuestions'] > 0) {
                $checklist->chooseRandom = 1;
            } else {
                $checklist->chooseRandom = 0;
            }
        }
        $checklist->update($data);

        if ($request->has('approvers')) {
            $approvers = $request->input('approvers');

            $approvingUsers = $checklist->approvingUsers;
            $approvingGroups = $checklist->approvingUserGroups;


            foreach ($approvers as $approver) {
                $object = null;

                if ($approver['objectType'] == 'user') {
                    if (!$approvingUsers->contains('id', $approver['objectId'])) {
                        $object = User::findOrFail($approver['objectId']);
                        $object->approvables()->attach($checklist);
                    } else {
                        $approvingUsers->forget($approver['objectId']);
                    }
                } else if ($approver['objectType'] == 'group') {
                    if (!$approvingGroups->contains('id', $approver['objectId'])) {
                        $object = UserGroup::findOrFail($approver['objectId']);
                        $object->approvables()->attach($checklist);
                    } else {
                        $approvingGroups->forget($approver['objectId']);
                    }
                }
            }

            foreach ($approvingUsers as $user) {
                $user->approvables()->detach($checklist);
            }

            foreach ($approvingGroups as $group) {
                $group->approvables()->detach($checklist);
            }
        }

        event(new ChecklistUpdatedEvent($checklist->id, $user->id, $updateTime));

        return response('', Response::HTTP_NO_CONTENT);
    }

    /**
     * Delete a checklist.
     *
     * @param string $checklistId
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(string $checklistId) {
        /* @var Checklist $checklist */
        $checklist = $this->checklist->findOrFail($checklistId);
        $this->authorize('delete', $checklist);
        $checklist->delete();
        $checklist->parentEntry()->delete();

        PlannedAudit::where('checklistId', '=', $checklistId)->delete();
        InspectionPlan::where('checklistId', '=', $checklistId)->delete();

        return response('', Response::HTTP_NO_CONTENT);
    }

    /**
     * Delete a set of checklists.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */

    public function deleteSet(Request $request) {
        $data = $request->all();

        foreach ($data['checklists'] as $checklistId) {
            /* @var Checklist $checklist */
            $checklist = $this->checklist->findOrFail($checklistId);

            $this->authorize('delete', $checklist);
            $checklist->delete();
            $checklist->parentEntry()->delete();
        }

        return response('', Response::HTTP_NO_CONTENT);
    }
}
