<?php

namespace App\Http\Controllers;

use App\AccessGrant;
use App\Checklist;
use App\Directory;
use App\Group;
use App\Http\Resources\AccessGrantResource;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class AccessGrantController extends Controller
{
    /**
     * @var AccessGrant
     */
    protected $grant;

    /**
     * @var User
     */
    protected $user;

    /**
     * @var Group
     */
    protected $group;

    /**
     * @var Directory
     */
    protected $directory;

    /**
     * @var Checklist
     */
    protected $checklist;

    /**
     * @var array
     */
    protected $subjectModels;

    /**
     * @var array
     */
    protected $objectModels;

    /**
     * Create a new controller instance.
     *
     * @param AccessGrant $grant
     * @param User $user
     * @param Group $group
     * @param Directory $directory
     * @param Checklist $checklist
     */
    public function __construct(AccessGrant $grant, User $user, Group $group,
                                Directory $directory, Checklist $checklist)
    {
        $this->grant = $grant;
        $this->user = $user;
        $this->group = $group;
        $this->directory = $directory;
        $this->checklist = $checklist;
        $this->subjectModels = [
            $this->user,
            $this->group,
        ];
        $this->objectModels = [
            $this->directory,
            $this->checklist,
        ];
    }

    /**
     * Create a new access grant.
     *
     * @param Request $request
     * @param string $objectId
     * @return \Illuminate\Http\JsonResponse|HttpException
     */
    public function create(Request $request, string $objectId)
    {
        /* @var Directory $object */
        $object = $this->findObject($objectId);
        if (is_null($object)) {
            return Response('', Response::HTTP_NOT_FOUND);  
        }

        $this->authorize('update', $object);
        $data = $this->validate($request, AccessGrant::rules('create'));

        // Make sure that the subject exists.
        $subject = $this->findSubject($data['subjectId']);
        if (is_null($subject)) {
            throw new UnprocessableEntityHttpException(
                'Invalid \'subjectId\': ' . $data['subjectId'] . '.');
        }

        // Make sure that there's no access grant for the subject/object pair yet.
        $exists = $this->grant->where([
            ['objectId', '=', $objectId],
            ['subjectId', '=', $data['subjectId']],
        ])->exists();
        if ($exists) {
            throw new BadRequestHttpException('Cannot create access grant.');
        }

        // TODO(thertweck): Authorize against the subject itself in the future.
        $this->authorize('update', $subject->company);

        // Create the access grant.
        /** @var AccessGrant $grant */
        $grant = $this->grant->newModelInstance($data);
        $grant->subject()->associate($subject);
        $grant->object()->associate($object);
        $grant->save();
        return AccessGrantResource::make($grant)
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * View an access grant.
     *
     * @param string $grantId
     * @return \Illuminate\Http\JsonResponse
     */
    public function view(string $grantId)
    {
        /* @var AccessGrant $grant */
        $grant = $this->grant->findOrFail($grantId);
        $this->authorize('view', $grant->object);
        return AccessGrantResource::make($grant)
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    /**
     * List a subject's access grants.
     *
     * @param string $subjectId
     * @return \Illuminate\Http\JsonResponse
     */
    public function indexSubject(string $subjectId)
    {
        $subject = $this->findSubject($subjectId);
        if (is_null($subject)) {
            throw new NotFoundHttpException('Not found: ' . $subjectId);
        }
        // TODO(thertweck): Authorize against the subject itself in the future.
        $this->authorize('update', $subject->company);
        return AccessGrantResource::collection($subject->grants)
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    /**
     * List an objects's access grants.
     *
     * @param string $objectId
     * @return \Illuminate\Http\JsonResponse
     */
    public function indexObject(string $objectId)
    {
        $object = $this->findObject($objectId);
        if (is_null($object)) {
            throw new NotFoundHttpException('Not found: ' . $objectId);
        }
        $this->authorize('view', $object);
        return AccessGrantResource::collection($object->grants)
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    /**
     * Update an access grant.
     *
     * @param Request $request
     * @param string $grantId
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, string $grantId)
    {
        /* @var AccessGrant $grant */
        $grant = $this->grant->findOrFail($grantId);
        $data = $this->validate($request, AccessGrant::rules('update'));
        $this->authorize('update', $grant->object);
        $grant->update($data);
        return response('', Response::HTTP_NO_CONTENT);
    }

    /**
     * Delete an access grant.
     *
     * @param string $grantId
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(string $grantId)
    {
        /* @var AccessGrant $grant */
        $grant = $this->grant->findOrFail($grantId);
        $this->authorize('update', $grant->object);
        $grant->delete();
        return response('', Response::HTTP_NO_CONTENT);
    }

    /**
     * Finds a grant subject by id.
     *
     * @param string $subjectId
     * @return User|Group
     */
    private function findSubject($subjectId)
    {
        return $this->findIn($subjectId, $this->subjectModels);
    }

    /**
     * Finds a grant object by id.
     *
     * @param string $objectId
     * @return User|Group
     */
    private function findObject($objectId)
    {
        return $this->findIn($objectId, $this->objectModels);
    }

    /**
     * Finds a grant subject by id.
     *
     * @param string $id
     * @param \Illuminate\Database\Eloquent\Model[] $models
     * @return User|Group
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
