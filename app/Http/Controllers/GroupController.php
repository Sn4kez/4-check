<?php

namespace App\Http\Controllers;

use App\Company;
use App\Group;
use App\UserGroup;
use App\Http\Resources\GroupResource;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Media;

class GroupController extends Controller
{
    /**
     * @var Group
     */
    protected $group;

    /**
     * @var Company
     */
    protected $company;

    /**
     * Create a new controller instance.
     *
     * @param Group $group
     * @param Company $company
     */
    public function __construct(Group $group, Company $company)
    {
        $this->group = $group;
        $this->company = $company;
    }

    /**
     * Create a new group.
     *
     * @param Request $request
     * @param string $companyId
     * @return \Illuminate\Http\JsonResponse
     */
    public function create(Request $request, string $companyId)
    {
        $company = $this->company->findOrFail($companyId);
        $this->authorize('update', $company);
        $data = $this->validate($request, Group::rules('create'));
        $data = Media::processRequest($data);
        $group = new Group($data);
        $group->company()->associate($company);
        $group->save();
        return GroupResource::make($group)
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * List a company's groups.
     *
     * @param string $companyId
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(string $companyId)
    {
        /* @var Company $company */
        $company = $this->company->findOrFail($companyId);
        $this->authorize('update', $company);
        return GroupResource::collection($company->groups)
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    /**
     * View a group.
     *
     * @param string $groupId
     * @return \Illuminate\Http\JsonResponse
     */
    public function view(string $groupId)
    {
        /* @var Group $group */
        $group = Media::changeImageFilenameToBase64String($this->group->findOrFail($groupId));
        $this->authorize('update', $group->company);
        return GroupResource::make($group)
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    /**
     * Update a group.
     *
     * @param Request $request
     * @param string $groupId
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, string $groupId)
    {
        /* @var Group $group */
        $group = $this->group->findOrFail($groupId);
        $this->authorize($group->company);
        $data = $this->validate($request, Group::rules('update'));
        $data = Media::processRequest($data);
        $group->update($data);
        return response('', Response::HTTP_NO_CONTENT);
    }

    /**
     * Delete a group.
     *
     * @param string $groupId
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(string $groupId)
    {
        /* @var Group $group */
        $group = $this->group->findOrFail($groupId);
        $this->authorize($group->company);
        $group->delete();

        UserGroup::where('groupId', '=', $groupId)->delete();

        return response('', Response::HTTP_NO_CONTENT);
    }
}
