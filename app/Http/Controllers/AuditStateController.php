<?php

namespace App\Http\Controllers;

use App\AuditState;
use App\Company;
use App\Http\Resources\AuditStateResource;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Ramsey\Uuid\Uuid;

class AuditStateController extends Controller
{
    /**
     * @var AuditState $auditState
     */

    private $auditState;

    /**
     * Create a new controller instance.
     *
     * @param Audit $loation
     */

    public function __contsruct(Audit $auditState)
    {
        $this->auditState = $auditState;
    }

    /**
     * Create a new Audit State.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */

    public function create(Request $request)
    {
    	$data = $this->validate($request, AuditState::rules('create'));

    	$company = Company::findOrFail($data['company']);

    	$auditState = new AuditState($data);
        $auditState->id = Uuid::uuid4()->toString();
    	$auditState->company()->associate($company);

        $this->authorize($auditState);

    	$auditState->save();

    	return AuditStateResource::make($auditState)
    		->response()
    		->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * Update a Audit State.
     *
     * @param Request $request
     * @param string $auditStateId
     * @return \Illuminate\Http\JsonResponse
     */

    public function update(Request $request, string $auditStateId)
    {
        $auditState = AuditState::findOrFail($auditStateId);

        $this->authorize($auditState);

        $data = $this->validate($request, AuditState::rules('update'));

        if (array_key_exists('company', $data)) {
            $company = Company::findOrFail($data['company']);
            $auditState->company()->associate($company);
        }

        $auditState->update($data);

        return response('', Response::HTTP_NO_CONTENT);
    }

    /**
     * Create a new Audit State.
     *
     * @param Request $request
     * @param string $auditStateId
     * @return \Illuminate\Http\JsonResponse
     */

    public function delete(Request $request, string $auditStateId)
    {
    	$auditState = AuditState::findOrFail($auditStateId);

        $this->authorize($auditState);

    	$auditState->delete();
    	return response('', Response::HTTP_NO_CONTENT);
    }

    /**
     * List Audit States of company.
     *
     * @param string $companyId
     * @return \Illuminate\Http\JsonResponse
     */

    public function index(Request $request, string $companyId)
    {
        $company = Company::findOrFail($companyId);

        $auditStates = AuditState::where('companyId', '=', $companyId)->orWhere('companyId', '=', null);

        $itemsPerPage = config('app.items_per_page');

        if(array_key_exists('numberItems', $request)) {
            $itemsPerPage = $request['numberItems'];
        }

        $auditStates->paginate($itemsPerPage);
        return AuditStateResource::collection($auditStates->get())
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    /**
     * View specified Audit State.
     *
     * @param string $auditStateId
     * @return \Illuminate\Http\JsonResponse
     */

    public function view(string $auditStateId)
    {
        /* @var App\AuditState $auditState */
        $auditState = AuditState::findOrFail($auditStateId);

        $this->authorize($auditState);

        return AuditStateResource::make($auditState)
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }
}
