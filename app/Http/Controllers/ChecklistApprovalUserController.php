<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Resources\UserResource;
use App\Checklist;
use App\User;
use App\Group;

class ChecklistApprovalUserController extends Controller
{
    public function add(Request $request, string $checklistId, string $userId)
    {
    	$checklist = Checklist::findOrFail($checklistId);
    	$this->authorize('update', $checklist);

    	$user = User::findOrFail($userId);

        if(!$this->checkPermissions($user, $checklist)) {
            return Response('', Response::HTTP_FORBIDDEN);
        }

    	$checklist->approvingByUsers()->attach($user);

    	return Response('', Response::HTTP_OK);
    }

    public function multiAdd(Request $request, string $checklistId)
    {
        $checklist = Checklist::findOrFail($checklistId);
        $this->authorize('update', $checklist);

        $users = $request['approvers'];
        foreach($users as $userId) {
            $user = User::findOrFail($userId);

            if(!$this->checkPermissions($user, $checklist)) {
                return Response('User ' . $user->firstName . ' ' . $user->lastName .' is not allowed to approve audits', Response::HTTP_FORBIDDEN);
            }
        }

        foreach($users as $userId) {
            $user = User::findOrFail($userId);
            $checklist->approvingByUsers()->attach($user);
        }
        
        return Response('', Response::HTTP_OK);
    }

    public function addGroup(Request $request, string $checklistId, string $groupId)
    {
        $checklist = Checklist::findOrFail($checklistId);
        $this->authorize('update', $checklist);

        $group = Group::findOrFail($groupId);

        foreach($group->users as $user) {
            if(!$this->checkPermissions($user, $checklist)) {
                return Response('User ' . $user->firstName . ' ' . $user->lastName .' is not allowed to approve audits', Response::HTTP_FORBIDDEN);
            }
        }

        foreach($group->users as $user) {
            $checklist->approvingByUsers()->attach($user);
        }

        return Response('', Response::HTTP_OK);
    }

    public function remove(Request $request, string $checklistId, string $userId)
    {
    	$checklist = Checklist::findOrFail($checklistId);
    	$this->authorize('update', $checklist);

    	$user = User::findOrFail($userId);

    	$checklist->approvingByUsers()->detach($user);

    	return Response('', Response::HTTP_OK);
    }

    public function multiRemove(Request $request, string $checklistId)
    {
        $checklist = Checklist::findOrFail($checklistId);
        $this->authorize('update', $checklist);

        $users = $request->input('approvers');

        foreach($users as $userId) {
            $user = User::findOrFail($userId);

            $checklist->approvingByUsers()->detach($user);
        }
        
        return Response('', Response::HTTP_OK);
    }

    public function removeGroup(Request $request, string $checklistId, string $groupId)
    {
        $checklist = Checklist::findOrFail($checklistId);
        $this->authorize('update', $checklist);

        $group = Group::findOrFail($groupId);

        foreach($group->users as $user) {
            $checklist->approvingByUsers()->detach($user);
        }

        return Response('', Response::HTTP_OK);
    }

    public function index(Request $request, string $checklistId) {
        $checklist = Checklist::findOrFail($checklistId);

        $this->authorize('update', $checklist);

        return UserResource::collection($checklist->approvingByUsers)
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    private function checkPermissions($user, $checklist) {
    	return $user->company->is($checklist->company);
    }
}
