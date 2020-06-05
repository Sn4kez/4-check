<?php

namespace App\Http\Controllers;

use App\Directory;
use App\Http\Resources\DirectoryEntryResource;
use App\Http\Resources\DirectoryResource;
use App\DirectoryEntry;
use App\AccessGrant;
use App\UserGroup;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class DirectoryController extends Controller
{
    /**
     * @var Directory
     */
    protected $directory;

    /**
     * Create a new controller instance.
     *
     * @param Directory $directory
     */
    public function __construct(Directory $directory)
    {
        $this->directory = $directory;
    }

    /**
     * Create a new directory.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function create(Request $request)
    {
        $data = $this->validate($request, Directory::rules('create'));
        /** @var Directory $parent */
        $parent = $this->directory->findOrFail($data['parentId']);
        $this->authorize('update', $parent);
        $directory = new Directory($data);
        $directory->createdByUser()->associate($request->user());
        $directory->lastUpdatedByUser()->associate($request->user());
        $directory->save();
        $parent->entry($directory)->save();
        return DirectoryResource::make($directory)
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * List a directory's entries.
     *
     * @param string $directoryId
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request,string $directoryId)
    {
        /* @var Directory $directory */
        $directory = $this->directory->findOrFail($directoryId);
        //$this->authorize('index', $directory);

        $result = collect([]);

        $entries = $directory->entries()->where('archived', '=', 0)->get();

        $user = $request->user();

        if(!$user->isAdmin() && ! $user->isSuperAdmin()) {
            foreach ($entries as $entry) {
                if($this->hasAccess($user->id, $entry->object->id, $entry->objectType)) {
                    $result->push($entry);
                } else {
                    if($entry->objectType === 'directory' && $this->checkSubstructureForAccess($user, $entry->object->id)) {
                        $result->push($entry);
                    } else {
                        $directoryEntry = DirectoryEntry::where('objectId', '=', $entry->object->id)->first();
                        if(!is_null($directoryEntry) && $this->checkParentStructureForAccess($user, $directoryEntry->parentId)) {
                            $result->push($entry);
                        }
                    }
                }
            }
        } else {
            $result = $entries;
        }

        return DirectoryEntryResource::collection($result)
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    private function hasAccess($user, $object, $objectType) {
        $userGroups = UserGroup::where('userId', '=', $user)->get(['groupId']);

        $grants = null;

        if($userGroups->count() == 0) {
            $grants = AccessGrant::where('objectType', '=', $objectType)
                        ->where('objectId', '=', $object)
                        ->where('subjectType', '=', 'user')
                        ->where('subjectId', '=', $user)->get();
        } else {
            $grants = AccessGrant::where('objectType', '=', $objectType)
                        ->where('objectId', '=', $object)
                        ->where('subjectType', '=', 'group')
                        ->whereIn('subjectId', $userGroups->toArray())->get();
        }

        if(!is_null($grants) && $grants->count() > 0) {
            return true;
        }   

        return false;
    }

    private function checkSubstructureForAccess($user, $entryId) {

        $entries = DirectoryEntry::where('parentId', '=', $entryId)->where('archived', '=', 0)->get();

        foreach($entries as $entry) {
            if($this->hasAccess($user->id, $entry->object->id, $entry->objectType)) {
                return true;
            }
            if($entry->objectType === 'directory' && $this->checkSubstructureForAccess($user, $entry->object->id)) {
                return true;
            }
        }

        return false;
    }

    private function checkParentStructureForAccess($user, $parentId) {
        $directoryEntry = DirectoryEntry::where('objectId', '=', $parentId)->first();

        if($parentId === null || $directoryEntry === null) {
            return false;
        }

        if($this->hasAccess($user->id, $parentId, 'directory')) {
            return true;
        }

        return $this->checkParentStructureForAccess($user, $directoryEntry->parentId);
    }

    /**
     * View a directory.
     *
     * @param string $directoryId
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function view(string $directoryId, Request $request)
    {
        /* @var Directory $directory */
        $directory = $this->directory->findOrFail($directoryId);

        //$this->authorize('view', $directory);

        $user = $request->user();

	if(!$user->isAdmin() && !$user->isSuperAdmin()) {
        if(!$this->hasAccess($user->id, $directoryId, 'directory') && !$this->checkSubstructureForAccess($user, $directoryId)) {
            return response(['message' => "This action is unauthorized."], Response::HTTP_FORBIDDEN);
        }
	}

        return DirectoryResource::make($directory)
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    /**
     * Update a directory.
     *
     * @param Request $request
     * @param string $directoryId
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, string $directoryId)
    {
        /* @var Directory $directory */
        $directory = $this->directory->findOrFail($directoryId);
        $this->authorize('update', $directory);
        if (is_null($directory->parentEntry)) {
            throw new AccessDeniedHttpException(
                "Cannot update the root directory.");
        }
        $data = $this->validate($request, Directory::rules('update'));
        if (array_key_exists('parentId', $data)) {
            /** @var Directory $newParent */
            $newParent = $this->directory->findOrFail($data['parentId']);
            $this->authorize('update', $newParent);
            $directory->parentEntry()->delete();
            $newParent->entry($directory)->save();
        }
        $directory->lastUpdatedByUser()->associate($request->user());
        $directory->lastUsedByUser()->associate($request->user());
        $directory->update($data);
        return response('', Response::HTTP_NO_CONTENT);
    }

    /**
     * Delete a directory.
     *
     * @param string $directoryId
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(Request $request, string $directoryId)
    {
        /* @var Directory $directory */
        $directory = $this->directory->findOrFail($directoryId);
        $this->authorize('delete', $directory);
        if (is_null($directory->parentEntry)) {
            throw new AccessDeniedHttpException(
                "Cannot delete the root directory.");
        }
        $directory->lastUpdatedByUser()->associate($request->user());
        $directory->delete();
        $directory->parentEntry->delete();
        return response('', Response::HTTP_NO_CONTENT);
    }

    /**
     * Deletes a set of directories.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function deleteSet(Request $request)
    {
        /* @var Directory $directory */
        $data = $request->all();

        foreach($data['directories'] as $directoryId) {
            $directory = $this->directory->findOrFail($directoryId);
            $this->authorize('delete', $directory);
            if (is_null($directory->parentEntry)) {
                throw new AccessDeniedHttpException(
                    "Cannot delete the root directory.");
            }
            $directory->lastUpdatedByUser()->associate($request->user());
            $directory->delete();
            $directory->parentEntry->delete();
        }

        return response('', Response::HTTP_NO_CONTENT);
    }
}
