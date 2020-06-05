<?php

namespace App\Http\Controllers;

use App\LocationType;
use App\Company;
use App\Http\Resources\LocationTypeResource;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Ramsey\Uuid\Uuid;

class LocationTypeController extends Controller
{
    /**
     * @var LocationType $locationType
     */

    private $locationType;

    /**
     * Create a new controller instance.
     *
     * @param Location $loation
     */

    public function __contsruct(Task $locationType)
    {
        $this->locationType = $locationType;
    }

    /**
     * Create a new location type.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */

    public function create(Request $request)
    {
    	$data = $this->validate($request, LocationType::rules('create'));

    	$company = Company::findOrFail($data['company']);

    	$locationType = new LocationType($data);
        $locationType->id = Uuid::uuid4()->toString();
    	$locationType->company()->associate($company);

        $this->authorize($locationType);

    	$locationType->save();

    	return LocationTypeResource::make($locationType)
    		->response()
    		->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * Update a location type.
     *
     * @param Request $request
     * @param string $locationTypeId
     * @return \Illuminate\Http\JsonResponse
     */

    public function update(Request $request, string $locationTypeId)
    {
        $locationType = LocationType::findOrFail($locationTypeId);

        $this->authorize($locationType);

        $data = $this->validate($request, LocationType::rules('update'));

        if (array_key_exists('company', $data)) {
            $company = Company::findOrFail($data['company']);
            $locationType->company()->associate($company);
        }

        $locationType->update($data);

        return response('', Response::HTTP_NO_CONTENT);
    }

    /**
     * Create a new location type.
     *
     * @param Request $request
     * @param string $locationTypeId
     * @return \Illuminate\Http\JsonResponse
     */

    public function delete(Request $request, string $locationTypeId)
    {
    	$locationType = LocationType::findOrFail($locationTypeId);

        $this->authorize($locationType);

    	$locationType->delete();
    	return response('', Response::HTTP_NO_CONTENT);
    }

    /**
     * List location types of company.
     *
     * @param string $companyId
     * @return \Illuminate\Http\JsonResponse
     */

    public function index(Request $request, string $companyId)
    {
        $company = Company::findOrFail($companyId);

        $locationTypes = LocationType::where('companyId', '=', $companyId)->orWhere('companyId', '=', null);

        $itemsPerPage = config('app.items_per_page');

        if(array_key_exists('numberItems', $request)) {
            $itemsPerPage = $request['numberItems'];
        }

        $locationTypes->paginate($itemsPerPage);
        return LocationTypeResource::collection($locationTypes->get())
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    /**
     * View specified location type.
     *
     * @param string $locationTypeId
     * @return \Illuminate\Http\JsonResponse
     */

    public function view(string $locationTypeId)
    {
        /* @var App\LocationType $locationType */
        $locationType = LocationType::findOrFail($locationTypeId);

        $this->authorize($locationType);

        return LocationTypeResource::make($locationType)
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }
}
