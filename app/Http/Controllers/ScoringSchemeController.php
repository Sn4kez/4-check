<?php

namespace App\Http\Controllers;

use App\Checklist;
use App\Company;
use App\Http\Resources\ScoringSchemeResource;
use App\ScoringScheme;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Illuminate\Support\Carbon;
use App\Events\Checklist\ChecklistEntryUpdatedEvent;

class ScoringSchemeController extends Controller {
    /**
     * @var ScoringScheme
     */
    protected $scoringScheme;

    /**
     * @var Company
     */
    protected $company;
    /**
     * @var Checklist
     */
    protected $checklist;

    /**
     * @var array
     */
    protected $scopeModels;

    /**
     * Create a new controller instance.
     *
     * @param ScoringScheme $scoringScheme
     * @param Company $company
     * @param Checklist $checklist
     */
    public function __construct(ScoringScheme $scoringScheme, Company $company, Checklist $checklist) {
        $this->scoringScheme = $scoringScheme;
        $this->company = $company;
        $this->checklist = $checklist;
        $this->scopeModels = [
            $this->company,
            $this->checklist,
        ];
    }

    /**
     * Create a new scoring scheme.
     *
     * @param Request $request
     * @param string $scopeId
     * @return \Illuminate\Http\JsonResponse|HttpException
     */
    public function create(Request $request, string $scopeId) {
        $scope = $this->findScope($scopeId);
        if (is_null($scope)) {
            throw new NotFoundHttpException('Not found: ' . $scopeId);
        }
        $this->authorize('update', $scope);
        $data = $this->validate($request, ScoringScheme::rules('create'));
        /** @var ScoringScheme $scheme */
        $scheme = $this->scoringScheme->newModelInstance($data);
        $scope->scoringSchemes()->save($scheme);
        $user = $request->user();
        event(new ChecklistEntryUpdatedEvent($scheme->id, $user->id, Carbon::Now()));
        return ScoringSchemeResource::make($scheme)->response()->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * View a scoring scheme.
     *
     * @param string $schemeId
     * @return \Illuminate\Http\JsonResponse
     */
    public function view(string $schemeId) {
        /* @var ScoringScheme $scheme */
        $scheme = $this->scoringScheme->findOrFail($schemeId);
        $this->authorize($scheme);
        return ScoringSchemeResource::make($scheme)->response()->setStatusCode(Response::HTTP_OK);
    }

    /**
     * List a subject's access grants.
     *
     * @param string $scopeId
     * @return \Illuminate\Http\JsonResponse
     */
    public function indexScope(string $scopeId) {
        $scope = $this->findScope($scopeId);

        if (is_null($scope)) {
            throw new NotFoundHttpException('Not found: ' . $scopeId);
        }

        return ScoringSchemeResource::collection($scope->scoringSchemes->where('isListed', '=', 1))->response()->setStatusCode(Response::HTTP_OK);
    }

    /**
     * Update a scoring scheme.
     *
     * @param Request $request
     * @param string $schemeId
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, string $schemeId) {
        /* @var ScoringScheme $scheme */
        $scheme = $this->scoringScheme->findOrFail($schemeId);
        $this->authorize($scheme);
        $data = $this->validate($request, ScoringScheme::rules('update'));
        if (array_key_exists('scopeId', $data)) {
            $this->authorize($scheme->scope);
            $newScope = $this->findScope($data['scopeId']);
            if (is_null($newScope)) {
                throw new UnprocessableEntityHttpException('Invalid \'scopeId\': ' . $data['scopeId'] . '.');
            }
            $this->authorize($newScope);
            $scheme->scope()->associate($newScope);
        }
        $scheme->update($data);
        $user = $request->user();
        event(new ChecklistEntryUpdatedEvent($scheme->id, $user->id, Carbon::Now()));
        return response('', Response::HTTP_NO_CONTENT);
    }

    /**
     * Delete a scoring scheme.
     *
     * @param string $schemeId
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(Request $request, string $schemeId) {
        /* @var ScoringScheme $schemeId */
        $scheme = $this->scoringScheme->findOrFail($schemeId);
        $this->authorize('delete', $scheme);
        $scheme->delete();
        $user = $request->user();
        event(new ChecklistEntryUpdatedEvent($scheme->id, $user->id, Carbon::Now()));
        return response('', Response::HTTP_NO_CONTENT);
    }

    /**
     * Finds a grant subject by id.
     *
     * @param string $id
     * @return Company|Checklist
     */
    private function findScope($id) {
        $subject = null;
        foreach ($this->scopeModels as $model) {
            $subject = $model->find($id);
            if (!is_null($subject)) {
                break;
            }
        }
        return $subject;
    }
}
