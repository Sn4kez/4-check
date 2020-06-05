<?php

namespace App\Http\Controllers;

use App\Location;
use App\LocationType;
use App\LocationState;
use App\Company;
use App\Country;
use App\Http\Resources\LocationResource;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Ramsey\Uuid\Uuid;

class LocationController extends Controller {
    const PARAM_NAME_SELECTED = 'selected';

    /**
     * @var Location
     */

    private $location;

    /**
     * Create a new controller instance.
     *
     * @param Location $loation
     */

    public function __contsruct(Location $location) {
        $this->location = $location;
    }

    /**
     * Create a new location.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */

    public function create(Request $request) {
        $data = $this->validate($request, Location::rules('create'));

        $type = LocationType::findOrFail($data['type']);
        $state = LocationState::findOrFail($data['state']);
        $company = Company::findOrFail($data['company']);
        $country = Country::findOrFail($data['country']);
        $parentLocation = null;

        //fill new location with data
        $location = new Location($data);
        $location->id = Uuid::uuid4()->toString();
        $location->type()->associate($type);
        $location->state()->associate($state);
        $location->company()->associate($company);
        $location->country()->associate($country);

        if (array_key_exists('parentId', $data)) {
            $parentLocation = Location::find($data['parentId']);
        }

        if (!is_null($parentLocation)) {
            $parentLocation->parent()->associate($parentLocation);
        }

        $location->save();

        return LocationResource::make($location)->response()->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * Update a location.
     *
     * @param Request $request
     * @param string $locationId
     * @return \Illuminate\Http\JsonResponse
     */

    public function update(Request $request, string $locationId) {
        $location = Location::findOrFail($locationId);

        $this->authorize($location);

        $data = $this->validate($request, Location::Rules('update'));

        if (array_key_exists('type', $data)) {
            $type = LocationType::findOrFail($data['type']);
            $location->type()->associate($type);
        }

        if (array_key_exists('state', $data)) {
            $state = LocationState::findOrFail($data['state']);
            $location->state()->associate($state);
        }

        if (array_key_exists('company', $data)) {
            $company = Company::findOrFail($data['company']);
            $location->company()->associate($company);
        }

        if (array_key_exists('country', $data)) {
            $country = Country::findOrFail($data['country']);
            $location->country()->associate($country);
        }

        $location->update($data);

        return response('', Response::HTTP_NO_CONTENT);
    }

    /**
     * Deletes a location.
     *
     * @param Request $request
     * @param string $locationId
     * @return \Illuminate\Http\JsonResponse
     */

    public function delete(Request $request, string $locationId) {
        /* @var Location $location */
        $location = Location::findOrFail($locationId);

        $this->authorize($location);

        $location->delete();

        return response('', Response::HTTP_NO_CONTENT);
    }

    /**
     * Deletes a set of locations.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */

    public function deleteSet(Request $request) {
        $locationIds = $request->input('items');

        if (!is_null($locationIds)) {
            if (is_array($locationIds)) {
                foreach ($locationIds as $locationId) {
                    $location = Location::findOrFail($locationId);

                    $this->authorize('delete', $location);

                    $this->deleteItem($location);
                }
            }
        }

        return response('', Response::HTTP_NO_CONTENT);
    }

    /**
     * Deletes a location.
     * @param Location $location
     * @throws \Exception
     */
    private function deleteItem(Location $location) {
        $location->delete();
    }

    /**
     * List locations of company.
     *
     * @param string $companyId
     * @return \Illuminate\Http\JsonResponse
     */

    public function index(Request $request, string $companyId) {
        Company::findOrFail($companyId);

        $locations = Location::where('companyId', '=', $companyId);

        $this->filterRequest($request, $locations);

        $itemsPerPage = config('app.items_per_page');

        if (array_key_exists('numberItems', $request)) {
            $itemsPerPage = $request['numberItems'];
        }

        $foldedItem = null;

        if ($request->has(self::PARAM_NAME_SELECTED)) {
            $currentId = $request->input(self::PARAM_NAME_SELECTED);
        } else {
            $currentId = null;
        }

        while (!is_null($currentId) && is_null($foldedItem)) {
            $currentLocation = Location::find($currentId);

            if (!is_null($currentLocation)) {
                $currentLocationParent = $currentLocation->parentId;

                if (!is_null($currentLocationParent)) {
                    $currentId = $currentLocationParent;
                } else {
                    $currentId = null;
                    $foldedItem = $currentLocation;
                }
            } else {
                $currentId = null;
            }
        }

        $locations->paginate($itemsPerPage);

        $locationResult = $locations->get();

        if ($foldedItem !== null) {
            foreach ($locationResult as &$item) {
                if ($item->id === $foldedItem->id) {
                    $this->generateChildren($item);
                }
            }
        }

        return LocationResource::collection($locationResult)->response()->setStatusCode(Response::HTTP_OK);
    }

    /**
     * Generates all children as property into a location.
     * Also then calls itself while it finds children
     *
     * @param LocationResource|Location $parentLocation
     * @return LocationResource|Location
     */
    private function generateChildren($parentLocation) {
        $parentLocation->children = [];
        $allChildren = [];
        $childLocations = Location::where('parentId', '=', $parentLocation->id)->get();

        $sorted = $childLocations->sortBy('street')->sortBy('postalCode')->sortBy('name')->sortBy('typeName');

        $childLocations = $sorted->values()->all();

        if (count($childLocations) > 0) {
            foreach ($childLocations as $child) {
                $child = LocationResource::make($child);
                $child = $this->generateChildren($child);
                $allChildren[] = $child;
            }
        }

        $parentLocation->children = $allChildren;

        return $parentLocation;
    }

    /**
     * @param Request $request
     * @param Builder $locations
     * @return mixed
     */
    private function filterRequest($request, $locations) {
        $filtered = false;

        if ($request->has('type')) {
            $types = $request->input('type');
            $locations = $this->filterByType($types, $locations);
            $filtered = true;
        }

        if (array_key_exists('state', $request)) {
            $locations = $this->filterByState($request['state'], $locations);
            $filtered = true;
        }

        /**
         * filter full text search
         */
        if ($request->get('name') !== null) {
            $locations->where(function ($query) use($request) {
                $query->where('name', $request->get('name'))
                    ->orWhere('description', $request->get('name'))
                    ->orWhere('street', $request->get('name'))
                    ->orWhere('streetNumber', $request->get('name'))
                    ->orWhere('city', $request->get('name'))
                    ->orWhere('postalCode', $request->get('name'))
                    ->orWhere('province', $request->get('name'));
                    });
            $filtered = true;
        }

        if ($filtered === false) {
            /**
             * Just load objects from the first layer
             */
            $locations->whereNull('parentId');
        }

        return $locations;
    }

    /**
     * @param array $types ids of types
     * @param $locations
     * @return mixed
     */
    private function filterByType($types, $locations) {
        return $locations->whereIn('typeId', is_array($types) ? $types : [$types]);
    }

    private function filterByState($state, $locations) {
        return $locations->where('stateId', '=', $state);
    }

    /**
     * View a location.
     *
     * @param string $locationId
     * @return \Illuminate\Http\JsonResponse
     */

    public function view(string $locationId) {
        /* @var Location $location */
        $location = Location::findOrFail($locationId);

        $this->authorize($location);

        return LocationResource::make($location)->response()->setStatusCode(Response::HTTP_OK);
    }
}
