<?php

namespace App\Http\Controllers;

use App\LocationState;
use App\Company;
use App\Http\Resources\LocationStateResource;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Ramsey\Uuid\Uuid;

class LocationStateController extends Controller
{
    /**
     * @var LocationState $locationState
     */

    private $locationState;

    /**
     * Create a new controller instance.
     *
     * @param Location $loation
     */

    public function __contsruct(Task $locationState)
    {
        $this->locationState = $locationState;
    }

    /**
     * Create a new location State.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */

    public function create(Request $request)
    {
    	$data = $this->validate($request, LocationState::rules('create'));

    	$company = Company::findOrFail($data['company']);

    	$locationState = new LocationState($data);
        $locationState->id = Uuid::uuid4()->toString();
    	$locationState->company()->associate($company);

        $this->authorize($locationState);

    	$locationState->save();

    	return LocationStateResource::make($locationState)
    		->response()
    		->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * Update a location State.
     *
     * @param Request $request
     * @param string $locationStateId
     * @return \Illuminate\Http\JsonResponse
     */

    public function update(Request $request, string $locationStateId)
    {
        $locationState = LocationState::findOrFail($locationStateId);

        $this->authorize($locationState);

        $data = $this->validate($request, LocationState::rules('update'));

        if (array_key_exists('company', $data)) {
            $company = Company::findOrFail($data['company']);
            $locationState->company()->associate($company);
        }

        $locationState->update($data);

        return response('', Response::HTTP_NO_CONTENT);
    }

    /**
     * Create a new location State.
     *
     * @param Request $request
     * @param string $locationStateId
     * @return \Illuminate\Http\JsonResponse
     */

    public function delete(Request $request, string $locationStateId)
    {
    	$locationState = LocationState::findOrFail($locationStateId);

        $this->authorize($locationState);

    	$locationState->delete();
    	return response('', Response::HTTP_NO_CONTENT);
    }

    /**
     * List location States of company.
     *
     * @param string $companyId
     * @return \Illuminate\Http\JsonResponse
     */

    public function index(Request $request, string $companyId)
    {
        $company = Company::findOrFail($companyId);

        $locationStates = LocationState::where('companyId', '=', $companyId)->orWhere('companyId', '=', null);

        $itemsPerPage = config('app.items_per_page');

        if(array_key_exists('numberItems', $request)) {
            $itemsPerPage = $request['numberItems'];
        }

        $locationStates->paginate($itemsPerPage);
        return LocationStateResource::collection($locationStates->get())
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    /**
     * View specified location State.
     *
     * @param string $locationStateId
     * @return \Illuminate\Http\JsonResponse
     */

    public function view(string $locationStateId)
    {
        /* @var App\LocationState $locationState */
        $locationState = LocationState::findOrFail($locationStateId);

        $this->authorize($locationState);

        return LocationStateResource::make($locationState)
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }
}
