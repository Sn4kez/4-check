<?php

namespace App\Http\Controllers;

use App\Group;
use App\Http\Resources\UserResource;
use App\User;
use App\Http\Resources\GroupResource;
use App\UserGroup;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class UserGroupController extends Controller
{
    /**
     * @var User
     */
    protected $user;

    /**
     * @var Group
     */
    protected $group;

    /**
     * Create a new controller instance.
     *
     * @param User $user
     * @param Group $group
     */
    public function __construct(User $user, Group $group)
    {
        $this->user = $user;
        $this->group = $group;
    }

    /**
     * List a groups's members.
     *
     * @param string $groupId
     * @return \Illuminate\Http\JsonResponse
     */
    public function indexGroup(string $groupId)
    {
        /* @var Group $group */
        $group = $this->group->findOrFail($groupId);
        $this->authorize('update', $group->company);
        return UserResource::collection($group->users)
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    /**
     * List a users's groups.
     *
     * @param string $userId
     * @return \Illuminate\Http\JsonResponse
     */
    public function indexUser(string $userId)
    {
        /* @var User $user */
        $user = $this->user->findOrFail($userId);
        $this->authorize('view', $user);
        return GroupResource::collection($user->groups)
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    /**
     * Adds a user to a group.
     *
     * @param Request $request
     * @param string $groupId
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, string $groupId)
    {
        /* @var Group $group */
        $group = $this->group->findOrFail($groupId);
        $company = $group->company;
        $this->authorize('update', $company);
        $data = $this->validate($request, UserGroup::rules('update'));
        /* @var User $user */
        $user = $company->users()->findOrFail($data['id']);
        $group->users()->attach($user->id);
        return response('', Response::HTTP_NO_CONTENT);
    }

    /**
     * Remove a user from a group.
     *
     * @param Request $request
     * @param string $groupId
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(Request $request, string $groupId)
    {
        /* @var Group $group */
        $group = $this->group->findOrFail($groupId);
        $this->authorize('update', $group->company);
        $data = $this->validate($request, UserGroup::rules('update'));
        /* @var User $user */
        $user = $group->users()->findOrFail($data['id']);
        $group->users()->detach($user->id);
        return response('', Response::HTTP_NO_CONTENT);
    }
}
