<?php

namespace App\Services;

use App\Checklist;
use App\Http\Resources\SectionResource;
use App\Location;
use App\LocationExtension;
use App\Section;
use App\TextfieldExtension;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class LocationExtensionService extends ExtensionService
{
    /**
     * @var LocationExtension
     */
    protected $locationExtension;

    /**
     * @var Location
     */
    protected $location;

    /**
     * Create a new controller instance.
     *
     * @param LocationExtension $locationExtension
     */
    public function __construct(LocationExtension $locationExtension, Location $location)
    {
        $this->locationExtension = $locationExtension;
        $this->location = $location;
    }

    /**
     * Create a new location extension.
     *
     * @param Request $request
     * @param array $data
     * @return LocationExtension
     */
    public function create(Request $request, array $data)
    {
        $data = $this->validateArray($request, $data, LocationExtension::rules('create'));
        $extension = new LocationExtension($data);
        if (array_key_exists('locationId', $data) and !is_null($data['locationId'])) {
            $location = $this->location->findOrFail($data['locationId']);
            $this->authorize('update', $location);
            $extension->location()->associate($location);
        }
        $extension->save();
        return $extension;
    }

    /**
     * Update a location extension.
     *
     * @param Request $request
     * @param array $data
     * @param Model $extension
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, array $data, Model $extension)
    {
        /** @var LocationExtension $extension */
        $data = $this->validateArray($request, $data['data'], LocationExtension::rules('update'));

        if(array_key_exists('fixed', $data)) {
            if($data['fixed'] == 'true' || $data['fixed'] == true || $data['fixed'] == 1) {
                $extension->fixed = 1;
            } else if($data['fixed'] == 'false' || $data['fixed'] == false || $data['fixed'] == 0) {
                $extension->fixed = 0;
            }
        }
        if (array_key_exists('locationId', $data)) {
            $oldLocation = $extension->location;
            //$this->authorize('update', $oldLocation);
            if (is_null($data['locationId'])) {
                $extension->location()->dissociate();
            } else {
                $newLocation = $this->location->findOrFail($data['locationId']);
                //$this->authorize('update', $newLocation);
                $extension->location()->associate($newLocation);
            }
        }

        $extension->save();

        return $extension;
    }

    public function __toString() {
        return 'LocationExtensionService';
    }
}
