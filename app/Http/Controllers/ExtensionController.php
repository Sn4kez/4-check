<?php

namespace App\Http\Controllers;

use App\Checklist;
use App\Checkpoint;
use App\Extension;
use App\Http\Resources\ExtensionResource;
use App\LocationExtension;
use App\ParticipantExtension;
use App\PictureExtension;
use App\Section;
use App\Services\ExtensionService;
use App\TextfieldExtension;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\App;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Support\Carbon;
use App\Events\Checklist\ChecklistEntryUpdatedEvent;

class ExtensionController extends Controller
{
    /**
     * @var Extension
     */
    protected $extension;

    /**
     * @var Checklist
     */
    protected $checklist;

    /**
     * @var Section
     */
    protected $section;

    /**
     * @var Checkpoint
     */
    protected $checkpoint;

    /**
     * @var array
     */
    protected $parentModels;

    /**
     * Create a new controller instance.
     *
     * @param Extension $extension
     * @param Checklist $checklist
     * @param Section $section
     * @param Checkpoint $checkpoint
     */
    public function __construct(Extension $extension, Checklist $checklist,
                                Section $section, Checkpoint $checkpoint)
    {
        $this->extension = $extension;
        $this->checklist = $checklist;
        $this->section = $section;
        $this->checkpoint = $checkpoint;
        $this->parentModels = [
            $this->checklist,
            $this->section,
            $this->checkpoint,
        ];
    }

    /**
     * Create a new extension.
     *
     * @param Request $request
     * @param string $parentId
     * @return \Illuminate\Http\JsonResponse
     */
    public function create(Request $request, string $parentId)
    {
        // Fetch the checkpoint's parent (Checklist, Checkpoint or Section).
        /** @var Checklist|Checkpoint|Section $parent */
        $parent = $this->findParent($parentId);
        if (is_null($parent)) {
            throw new NotFoundHttpException(
                'Invalid \'parentId\': ' . $parentId . '.');
        }
        $this->authorize('update', $parent);

        // Validate the extension's wrapper data.
        $data = $this->validate($request, Extension::rules('create'));

        // Create the extension object using the specific service.
        $service = $this->getExtensionService($data['type']);
        /** @var TextfieldExtension|LocationExtension|ParticipantExtension|PictureExtension $object */
        $object = $service->create($request, $data['data']);
        $this->authorize('create', $object);
        $object->save();

        // Associate the extension object to the parent.
        $extension = $parent->extension($object);
        $extension->index = 0;

        if(array_key_exists('index', $data) && !is_null($data['index']))
        {
           $extension->index = $data['index'];
        }

        $extension->save();
        $user = $request->user();
        event(new ChecklistEntryUpdatedEvent($extension->id, $user->id, Carbon::Now()));
        $parent->entry($extension)->save();

        // Returned the extension object, wrapped as extension.
        return ExtensionResource::make($extension)
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * List a checkpoints's extensions.
     *
     * @param string $parentId
     * @return \Illuminate\Http\JsonResponse
     */
    public function indexExtensions(string $parentId)
    {
        // Fetch the checkpoint's parent (Checklist, Checkpoint or Section).
        /** @var Checklist|Checkpoint|Section $parent */
        $parent = $this->findParent($parentId);
        if (is_null($parent)) {
            throw new NotFoundHttpException(
                'Invalid \'parentId\': ' . $parentId . '.');
        }
        $this->authorize('index', $parent);

        // Get all extensions that have the requested parent.
        $extensions = $this->extension->whereHas('parentEntry', function(Builder $query) use ($parent) {
            $query->where('parentId', '=', $parent->id);
        })->get();

        // Verify access to all extensions (could be denied by subscription).
        foreach ($extensions as $extension) {
            $this->authorize('view', $extension->object);
        }
        return ExtensionResource::collection($extensions)
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    /**
     * View an extension.
     *
     * @param string $extensionId
     * @return \Illuminate\Http\JsonResponse
     */
    public function view(string $extensionId)
    {
        /* @var Extension $extension */
        $extension = $this->extension->findOrFail($extensionId);
        $this->authorize($extension->object);
        // Returned the extension object, wrapped as extension.
        return ExtensionResource::make($extension)
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    /**
     * Update an extension.
     *
     * @param Request $request
     * @param string $extensionId
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, string $extensionId)
    {
        /* @var Extension $extension */
        $extension = $this->extension->findOrFail($extensionId);

        $data = $this->validate($request, Extension::rules('update'));

        //return response($data, Response::HTTP_OK);

        $this->authorize($extension->object);
        // Update the extension object.
        $service = $this->getExtensionService($extension->objectType);
        $tmp = $service->update($request, $request->all(), $extension->object);
        
        if(!is_null($request->input('index')))
        {
           $extension->index = $request->input('index');
        }

        $extension->save();

        $user = $request->user();
        event(new ChecklistEntryUpdatedEvent($extensionId, $user->id, Carbon::Now()));
        
        return response('', Response::HTTP_NO_CONTENT);
    }

    /**
     * Delete an extension.
     *
     * @param string $extensionId
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(Request $request, string $extensionId)
    {
        /* @var Extension $extension */
        $extension = $this->extension->findOrFail($extensionId);
        $this->authorize($extension->object);
        $extension->delete();
        $user = $request->user();
        event(new ChecklistEntryUpdatedEvent($extension->id, $user->id, Carbon::Now()));
        $extension->parentEntry->delete();
        return response('', Response::HTTP_NO_CONTENT);
    }

    /**
     * Returns the extension service for a given extension type.
     *
     * @param string $type
     * @return ExtensionService
     */
    private function getExtensionService($type)
    {
        $class = Extension::TYPES[$type]['service'];
        return App::make($class);
    }

    /**
     * Finds a parent by id.
     *
     * @param string $parentId
     * @return Checklist|Section
     */
    private function findParent($parentId)
    {
        return $this->findIn($parentId, $this->parentModels);
    }

    /**
     * Finds a model by id.
     *
     * @param string $id
     * @param \Illuminate\Database\Eloquent\Model[] $models
     * @return Checklist|Section
     */
    private function findIn($id, $models)
    {
        $subject = null;
        foreach ($models as $model) {
            $subject = $model->find($id);
            if (!is_null($subject)) {
                break;
            }
        }
        return $subject;
    }
}
