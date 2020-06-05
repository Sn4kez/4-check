<?php

use App\Location;
use App\LocationType;
use App\LocationState;
use App\Company;
use App\Country;
use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Ramsey\Uuid\Uuid;

class FilterLocationTest extends TestCase {
    use DatabaseMigrations;

    /**
     * @var \App\Location $locations
     */

    protected $locations;

    /**
     * The test element which acts as 'selected'
     *
     * @var null
     */
    protected $parentLocationId = null;

    /**
     * @var LocationType
     */
    private $typeBuilding;

    /**
     * @var LocationType
     */
    private $typeRoom;

    /**
     * @var LocationType
     */
    private $typeArea;

    public function setUp() {
        parent::setUp();

        $this->typeBuilding = LocationType::where('name', '=', 'building')->first();
        $this->typeRoom = LocationType::where('name', '=', 'room')->first();
        $this->typeArea = LocationType::where('name', '=', 'area')->first();

        $location1 = factory(Location::class)->make();
        $location1->type()->associate($this->typeBuilding);
        $location1->name = "test1";
        $location1->state()->associate(LocationState::where('name', '=', 'active')->first());
        $location1->company()->associate($this->user->company);
        $location1->country()->associate(Country::find('de'));
        $location1->save();

        $location2 = factory(Location::class)->make();
        $location2->name = "test2";
        $location2->type()->associate($this->typeRoom);
        $location2->state()->associate(LocationState::where('name', '=', 'inactive')->first());
        $location2->company()->associate($this->user->company);
        $location2->country()->associate(Country::find('ch'));
        $location2->save();

        $location3 = factory(Location::class)->make();
        $location3->type()->associate($this->typeRoom);
        $location3->state()->associate(LocationState::where('name', '=', 'inactive')->first());
        $location3->company()->associate($this->user->company);
        $location3->country()->associate(Country::find('ch'));
        $location3->id = Uuid::uuid4()->toString();
        $location3->name = 'test';
        $location3->description = 'test';
        $location3->street = 'Teststrasse';
        $location3->streetNumber = '123';
        $location3->city = 'Hamburg';
        $location3->postalCode = '22222';
        $location3->province = 'Hamburg';
        $location3->parent()->associate($location2);

        $location4 = clone $location3;
        $location5 = clone $location4;
        $location6 = clone $location5;

        $location3->save();

        $location4->id = Uuid::uuid4()->toString();
        $location4->parentId = $location3->id;
        $location4->name = 'Child-Objekt Ebene 2';
        $location4->type()->associate($this->typeArea);
        $location4->save();

        $location5->id = Uuid::uuid4()->toString();
        $location5->parentId = $location4->id;
        $location5->name = 'Child-Objekt Ebene 3';
        $location5->type()->associate($this->typeArea);
        $location5->save();

        $location6->id = Uuid::uuid4()->toString();
        $location6->parentId = $location5->id;
        $location6->name = 'Child-Objekt Ebene 4';
        $location6->type()->associate($this->typeArea);
        $location6->save();

        $this->locations = [
            $location1,
            $location2,
            $location3,
            $location4,
            $location5,
            $location6
        ];

        $this->parentLocationId = $location3->id;
    }

    public function testMultipleTypeFilter() {
        $this->actingAs($this->getUser(self::$ADMIN));

        $url = sprintf("/locations/company/%s?type[]=%s&type[]=%s&type[]=%s", $this->user->company->id, $this->typeRoom->id, $this->typeBuilding->id, $this->typeArea->id);
        $this->json('GET', $url);
        $this->seeStatusCode(Response::HTTP_OK);
        $this->countData(6);

        $url = sprintf("/locations/company/%s?type[]=%s&type[]=%s", $this->user->company->id, $this->typeRoom->id, $this->typeBuilding->id);
        $this->json('GET', $url);
        $this->seeStatusCode(Response::HTTP_OK);
        $this->countData(3);

        $url = sprintf("/locations/company/%s?type[]=%s", $this->user->company->id, $this->typeRoom->id);
        $this->json('GET', $url);
        $this->seeStatusCode(Response::HTTP_OK);
        $this->countData(2);

        $url = sprintf("/locations/company/%s?type[]=%s", $this->user->company->id, $this->typeBuilding->id);
        $this->json('GET', $url);
        $this->seeStatusCode(Response::HTTP_OK);
        $this->countData(1);

        $url = sprintf("/locations/company/%s?type[]=%s", $this->user->company->id, $this->typeArea->id);
        $this->json('GET', $url);
        $this->seeStatusCode(Response::HTTP_OK);
        $this->countData(3);
    }

    public function provideValidAccessData() {
        return [
            [
                self::$USER,
                'type',
                'building',
                0,
                false
            ],
            [
                self::$USER,
                'name',
                'Child-Objekt Ebene 2',
                3,
                false
            ],
            [
                self::$USER,
                'name',
                'Child-Objekt Ebene 3',
                4,
                false
            ],
            [
                self::$USER,
                'name',
                'Child-Objekt Ebene 4',
                5,
                false
            ],
            [
                self::$USER,
                'state',
                'active',
                0,
                false
            ],
            [
                self::$USER,
                'type',
                'room',
                1,
                false
            ],
            [
                self::$USER,
                'state',
                'inactive',
                1,
                false
            ],
            [
                self::$USER,
                'description',
                'e',
                2,
                true
            ],
            [
                self::$USER,
                'street',
                'a',
                2,
                true
            ],
            [
                self::$USER,
                'streetNumber',
                '1',
                2,
                true
            ],
            [
                self::$USER,
                'city',
                'a',
                2,
                true
            ],
            [
                self::$USER,
                'postalCode',
                '2',
                2,
                true
            ],
            [
                self::$USER,
                'province',
                'a',
                2,
                true
            ]
        ];
    }

    /**
     * @param $userKey
     * @param $attribute
     * @param $value
     * @param $locationArrayId
     * @param $withSelected
     * @dataProvider provideValidAccessData
     */
    public function testValidAccess($userKey, $attribute, $value, $locationArrayId, $withSelected) {
        $user = $this->getUser($userKey);
        $this->actingAs($user);

        if ($attribute === 'type') {
            $type = LocationType::where('name', '=', $value)->first();
            $value = $type->id;
        }

        $uri = '/locations/company/' . $this->user->company->id . '?' . $attribute . '=' . $value;

        if ($withSelected === true) {
            $uri .= '&selected=' . $this->parentLocationId;
        }

        $this->json('GET', $uri);
        $this->seeStatusCode(Response::HTTP_OK);
        $this->seeHeader('Content-Type', 'application/json');
        $this->seeJsonStructure([
            'data' => [
                array_merge([
                    'id',
                    'name',
                    'description',
                    'street',
                    'streetNumber',
                    'city',
                    'postalCode',
                    'province',
                    'type',
                    'state',
                    'country',
                    'company'
                ], $withSelected === false ? [] : ['parent', 'parentId'])
            ],
        ]);

        $location = $this->locations[$locationArrayId];

        $this->seeJsonContains([
            'name' => $location->name,
            'description' => $location->description,
            'street' => $location->street,
            'streetNumber' => $location->streetNumber,
            'city' => $location->city,
            'postalCode' => $location->postalCode,
            'province' => $location->province,
            'type' => $location->type->id,
            'state' => $location->state->id,
            'country' => $location->country->id,
            'company' => $location->company->id
        ]);
    }
}
