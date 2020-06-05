<?php

use App\Location;
use App\LocationType;
use App\LocationState;
use App\Company;
use App\Country;
use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Ramsey\Uuid\Uuid;

class ListLocationTest extends TestCase
{
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

    public function setUp()
    {
        parent::setUp();

        $location1 = factory(Location::class)->make();
        $location1->type()->associate(LocationType::where('name', '=', 'building')->first());
        $location1->state()->associate(LocationState::where('name', '=', 'active')->first());
        $location1->company()->associate($this->user->company);
        $location1->country()->associate(Country::find('de'));
        $location1->save();

        $location2 = factory(Location::class)->make();
        $location2->type()->associate(LocationType::where('name', '=', 'room')->first());
        $location2->state()->associate(LocationState::where('name', '=', 'inactive')->first());
        $location2->company()->associate($this->user->company);
        $location2->country()->associate(Country::find('ch'));
        $location2->save();

        $location3 = factory(Location::class)->make();
        $location3->type()->associate(LocationType::where('name', '=', 'room')->first());
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

        $location4 = clone $location3;
        $location5 = clone $location4;
        $location6 = clone $location5;
        $location7 = clone $location6;

        $location3->save();

        $location4->id = Uuid::uuid4()->toString();
        $location4->parentId = $location3->id;
        $location4->name = 'Child-Objekt Ebene 2';
        $location4->save();

        $location5->id = Uuid::uuid4()->toString();
        $location5->parentId = $location4->id;
        $location5->name = 'Child-Objekt Ebene 3';
        $location5->save();

        $location6->id = Uuid::uuid4()->toString();
        $location6->parentId = $location5->id;
        $location6->name = 'Child-Objekt 1 Ebene 4';
        $location6->save();

        $location7->id = Uuid::uuid4()->toString();
        $location7->parentId = $location5->id;
        $location7->name = 'Child-Objekt 2 Ebene 4';
        $location7->save();

        $this->locations = [
            $location1,
            $location2
        ];

        $this->parentLocationId = $location3->id;
    }

    public function provideInvalidAccessData()
    {
        return [
            [null, 'random', Response::HTTP_UNAUTHORIZED],
            [null, 'company', Response::HTTP_UNAUTHORIZED],
        ];
    }

    /**
     * @param string $userKey
     * @param string $userIdKey
     * @param int $statusCode
     * @dataProvider provideInvalidAccessData
     */
    public function testInvalidAccess($userKey, $companyIdKey, $statusCode)
    {

        if ($companyIdKey === 'company') {
            $uri = '/locations/company/' . $this->user->company->id;
        } else {
            $uri = '/locations/company/' . Uuid::uuid4()->toString();
        }
        $this->json('GET', $uri);
        $this->seeStatusCode($statusCode);
        $this->seeHeader('Content-Type', 'application/json');
    }

    public function provideValidAccessData()
    {
        return [
            [self::$ADMIN, 'company', false, true],
            [self::$SUPER_ADMIN, 'company', false, true],
            [self::$OTHER_ADMIN, 'otherCompany', true, true],
            [self::$ADMIN, 'company', false, false],
            [self::$SUPER_ADMIN, 'company', false, false],
            [self::$OTHER_ADMIN, 'otherCompany', true, false]
        ];
    }

    /**
     * @param $userKey
     * @param $companyKey
     * @param $expectEmpty
     * @dataProvider provideValidAccessData
     */
    public function testValidAccess($userKey, $companyKey, $expectEmpty, $withSelected)
    {
        $user = $this->getUser($userKey);
        $this->actingAs($user);
        if ($companyKey === 'company') {
            $uri = '/locations/company/' . $this->user->company->id;
        } else {
            $uri = '/locations/company/' . $this->otherUser->company->id;
        }

        if ($withSelected === true) {
            $uri .= '?selected=' . $this->parentLocationId;
        }

        $this->json('GET', $uri);
        $this->seeStatusCode(Response::HTTP_OK);
        $this->seeHeader('Content-Type', 'application/json');
        if ($expectEmpty) {
            $this->seeJsonStructure([
                'data' => [],
            ]);
        } else {
            $this->seeJsonStructure([
                'data' => [
                    [
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
                        'company',
                        'children',
                        'parentId',
                        'parent'
                    ],
                    [
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
                        'company',
                        'children',
                        'parentId',
                        'parent'
                    ],
                ],
            ]);
            foreach ($this->locations as $location) {
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
                    'company' => $location->company->id,
                    'parentId' => $location->parentId
                ]);
            }
        }
    }
}
