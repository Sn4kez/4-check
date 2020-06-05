<?php

use App\Location;
use App\LocationType;
use App\LocationState;
use App\Company;
use App\Country;
use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Ramsey\Uuid\Uuid;

class UpdateLocationTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * @var \App\Location $location
     */
    protected $location;

    /**
     * @var Location $parentLocation
     */
    protected $parentLocation;

    /**
     * @var \App\Company $company
     */

    protected $company;

    private $allowedNullValues = [];

    public function setUp()
    {
        parent::setUp();

        $this->parentLocation = factory(Location::class)->make();
        $this->parentLocation->type()->associate(LocationType::where('name', '=', 'building')->first());
        $this->parentLocation->state()->associate(LocationState::where('name', '=', 'active')->first());
        $this->parentLocation->company()->associate($this->user->company);
        $this->parentLocation->country()->associate(Country::find('de'));
        $this->parentLocation->id = Uuid::uuid4()->toString();
        $this->parentLocation->save();

        $this->location = factory(Location::class)->make();
        $this->location->type()->associate(LocationType::where('name', '=', 'building')->first());
        $this->location->state()->associate(LocationState::where('name', '=', 'active')->first());
        $this->location->company()->associate($this->user->company);
        $this->location->country()->associate(Country::find('de'));
        $this->location->parentId = $this->parentLocation->id;
        $this->location->id = Uuid::uuid4()->toString();
        $this->location->save();

        $this->allowedNullValues = [
            'description',
            'parentId'
        ];
    }

    public function provideInvalidAccessData()
    {
        return [
            [null, Response::HTTP_UNAUTHORIZED],
            [self::$USER, Response::HTTP_UNAUTHORIZED]
        ];
    }

    /**
     * @param string $userKey
     * @param string $userIdKey
     * @param int $statusCode
     * @dataProvider provideInvalidAccessData
     * @group locations_update
     */
    public function testInvalidAccess($userKey, $statusCode)
    {
        $this->json('PATCH', '/locations/' . $this->location->id);
        $this->seeStatusCode($statusCode);
        $this->seeHeader('Content-Type', 'application/json');
    }

    public function provideValidEntities()
    {
        return [
            [self::$ADMIN, 'parentId', 'parentId', null],
            [self::$SUPER_ADMIN, 'parentId', 'parentId', null],
            [self::$ADMIN, 'parentId', 'parentId', 'parentId'],
            [self::$SUPER_ADMIN, 'parentId', 'parentId', 'parentId'],
            [self::$ADMIN, 'name', 'name', 'abc'],
            [self::$SUPER_ADMIN, 'name', 'name', 'abc'],
            [self::$ADMIN, 'description', 'description', 'abc'],
            [self::$SUPER_ADMIN, 'description', 'description', 'abc'],
            [self::$ADMIN, 'description', 'description', null],
            [self::$SUPER_ADMIN, 'description', 'description', null],
            [self::$ADMIN, 'street', 'street', 'abc'],
            [self::$SUPER_ADMIN, 'street', 'street', 'abc'],
            [self::$ADMIN, 'streetNumber', 'streetNumber', 'abc'],
            [self::$SUPER_ADMIN, 'streetNumber', 'streetNumber', 'abc'],
            [self::$ADMIN, 'postalCode', 'postalCode', 'abc'],
            [self::$SUPER_ADMIN, 'postalCode', 'postalCode', 'abc'],
            [self::$ADMIN, 'city', 'city', 'abc'],
            [self::$SUPER_ADMIN, 'city', 'city', 'abc'],
            [self::$ADMIN, 'province', 'province', 'abc'],
            [self::$SUPER_ADMIN, 'province', 'province', 'abc'],
            [self::$ADMIN, 'type', 'typeId', 'floor'],
            [self::$SUPER_ADMIN, 'type', 'typeId', 'floor'],
            [self::$ADMIN, 'state', 'stateId', 'inactive'],
            [self::$SUPER_ADMIN, 'state', 'stateId', 'inactive'],
            [self::$ADMIN, 'country', 'countryId', 'ch'],
            [self::$SUPER_ADMIN, 'country', 'countryId', 'ch'],
            [self::$ADMIN, 'company', 'companyId', null],
            [self::$SUPER_ADMIN, 'company', 'companyId', null],
        ];
    }

    /**
     * @param string $attribute
     * @param string $value
     * @dataProvider provideValidEntities
     * @group locations_update
     */
    public function testValidEntities($userKey, $attribute, $dbAttribute, $value)
    {
        $user = $this->getUser($userKey);
        $this->actingAs($user);

        if ($value === null) {
            if (!in_array($attribute, $this->allowedNullValues)) {
                $value = $this->company->id;
            }
        }

        if ($attribute == 'type') {
            $value = LocationType::where('name', '=', $value)->first()->id;
        }

        if ($attribute == 'state') {
            $value = LocationState::where('name', '=', $value)->first()->id;
        }

        if ($value === 'parentId' && $attribute === 'parentId') {
            $value = $this->parentLocation->id;
        }

        $data = [$attribute => $value];
        $this->json('PATCH', '/locations/' . $this->location->id, $data);
        $this->seeStatusCode(Response::HTTP_NO_CONTENT);
        $this->seeInDatabase('locations', [
            'id' => $this->location->id,
            $dbAttribute => $value,
        ]);
    }

    public function provideInvalidEntities()
    {
        return [
            [self::$ADMIN, 'name', null],
            [self::$ADMIN, 'name', 123],
            [self::$ADMIN, 'name', str_repeat('123', 128)],
            [self::$ADMIN, 'description', 123],
            [self::$ADMIN, 'type', null],
            [self::$ADMIN, 'type', 'edit'],
            [self::$ADMIN, 'type', 123],
            [self::$ADMIN, 'state', null],
            [self::$ADMIN, 'state', 'edit'],
            [self::$ADMIN, 'state', 123],
            [self::$ADMIN, 'street', null],
            [self::$ADMIN, 'street', 123],
            [self::$ADMIN, 'street', str_repeat('123', 128)],
            [self::$ADMIN, 'streetNumber', null],
            [self::$ADMIN, 'streetNumber', 123],
            [self::$ADMIN, 'streetNumber', str_repeat('123', 128)],
            [self::$ADMIN, 'postalCode', null],
            [self::$ADMIN, 'postalCode', 123],
            [self::$ADMIN, 'postalCode', str_repeat('123', 128)],
            [self::$ADMIN, 'city', null],
            [self::$ADMIN, 'city', 123],
            [self::$ADMIN, 'city', str_repeat('123', 128)],
            [self::$ADMIN, 'province', null],
            [self::$ADMIN, 'province', 123],
            [self::$ADMIN, 'province', str_repeat('123', 128)],
            [self::$ADMIN, 'country', null],
            [self::$ADMIN, 'country', 123],
            [self::$ADMIN, 'country', str_repeat('123', 128)],
            [self::$ADMIN, 'company', null],
            [self::$ADMIN, 'company', 456],
            [self::$ADMIN, 'company', str_repeat('123', 128)],
            [self::$ADMIN, 'parentId', 123],
            [self::$ADMIN, 'parentId', str_repeat('123', 123)],
        ];
    }

    /**
     * @param $userKey
     * @param $attribute
     * @param $value
     * @dataProvider provideInvalidEntities
     * @group locations_update
     */
    public function testInvalidEntities($userKey, $attribute, $value)
    {
        $user = $this->getUser($userKey);
        $this->actingAs($user);

        $data = [$attribute => $value];

        $this->json('PATCH', '/locations/' . $this->location->id, $data);
        $this->seeStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->seeHeader('Content-Type', 'application/json');
    }
}
