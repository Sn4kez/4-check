<?php

namespace App\Http\Controllers;

use App\ArchiveDirectory;
use App\Http\Resources\ArchiveEntryResource;
use App\Http\Resources\ArchiveDirectoryResource;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class ArchiveDirectoryController extends Controller
{
    /**
     * @var ArchiveDirectory
     */
    protected $directory;

    /**
     * Create a new controller instance.
     *
     * @param ArchiveDirectory $directory
     */
    public function __construct(ArchiveDirectory $directory)
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
        $data = $this->validate($request, ArchiveDirectory::rules('create'));
        /** @var Directory $parent */
        $parent = $this->directory->findOrFail($data['parentId']);
        $this->authorize('update', $parent);
        $directory = new ArchiveDirectory($data);
        $directory->createdByUser()->associate($request->user());
        $directory->lastUpdatedByUser()->associate($request->user());
        $directory->save();
        $parent->entry($directory)->save();
        return ArchiveDirectoryResource::make($directory)
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * List a directory's entries.
     *
     * @param string $directoryId
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(string $directoryId)
    {

        /* @var ArchiveDirectory $directory */
        $directory = $this->directory->findOrFail($directoryId);

        $this->authorize('index', $directory);
        return ArchiveEntryResource::collection($directory->entries)
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    /**
     * View a directory.
     *
     * @param string $directoryId
     * @return \Illuminate\Http\JsonResponse
     */
    public function view(string $directoryId)
    {
        /* @var ArchiveDirectory $directory */
        $directory = $this->directory->findOrFail($directoryId);
        $this->authorize('view', $directory);
        return ArchiveDirectoryResource::make($directory)
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
        /* @var ArchiveDirectory $directory */
        $directory = $this->directory->findOrFail($directoryId);
        $this->authorize('update', $directory);
        if (is_null($directory->parentEntry)) {
            throw new AccessDeniedHttpException(
                "Cannot update the root directory.");
        }
        $data = $this->validate($request, ArchiveDirectory::rules('update'));
        if (array_key_exists('parentId', $data)) {
            /** @var ArchiveDirectory $newParent */
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
        /* @var ArchiveDirectory $directory */
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
        /* @var ArchiveDirectory $directory */
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
