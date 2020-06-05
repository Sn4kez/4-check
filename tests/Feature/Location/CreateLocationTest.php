<?php

use App\Location;
use App\LocationType;
use App\LocationState;
use App\Company;
use App\Country;
use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Ramsey\Uuid\Uuid;

class CreateLocationTest extends TestCase
{
    use DatabaseMigrations;

    public function provideInvalidAccessData()
    {
        return [
            [null, Response::HTTP_UNAUTHORIZED],
        ];
    }

    /**
     * @param string $userKey
     * @param int $statusCode
     * @dataProvider provideInvalidAccessData
     * @group locations_create
     */
    public function testInvalidAccess($userKey, $statusCode)
    {
        $this->json('POST', '/locations');
        $this->seeStatusCode($statusCode);
        $this->seeHeader('Content-Type', 'application/json');
    }

    public function provideValidAccessData()
    {
        $feedback = [];

        foreach ([self::$USER, self::$ADMIN, self::$SUPER_ADMIN] as $userKey) {
            $feedback[] = [$userKey, false];
            $feedback[] = [$userKey, null];
            $feedback[] = [$userKey, ''];
            $feedback[] = [$userKey, true];
        }

        return $feedback;
    }

    /**
     * @param string $userKey
     * @param $parent true means "get location id", every other value means false
     * @dataProvider provideValidAccessData
     * @group locations_create
     */
    public function testValidAccess($userKey, $parent)
    {
        $user = $this->getUser($userKey);
        $this->actingAs($user);

        $location = factory(Location::class)->make();
        $location->type()->associate(LocationType::where('name', '=', 'building')->first());
        $location->state()->associate(LocationState::where('name', '=', 'active')->first());
        $location->company()->associate($this->company);
        $location->country()->associate(Country::find('de'));
        $location->save();

        $hasParent = false;

        if ($parent === true) {
            $parent = $location->id;
            $hasParent = true;
        }

        /**
         * Filter list because if parentId is not set, then the test will throw an error.
         * If we do not pass the parentId, then everythings fine.
         */
        $this->json('POST', '/locations', array_filter([
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
            'parentId' => $parent
        ]));

        $this->seeStatusCode(Response::HTTP_CREATED);
        $this->seeHeader('Content-Type', 'application/json');

        $this->seeJsonStructure([
            'data' => [
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
            ],
        ])->seeJsonNotNull([
            'data' => array_merge([
                'id',
                'name',
            ], $hasParent === true ? ['parentId'] : []),
        ])->seeJsonContains(array_merge([
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
        ], $hasParent === true ? ['parentId' => $parent] : []));
    }

    public function provideInvalidEntities()
    {
        return [
            ['name', null],
            ['name', 123],
            ['name', str_repeat('123', 128)],
            ['description', 123],
            ['type', null],
            ['type', 'edit'],
            ['type', 123],
            ['state', null],
            ['state', 'edit'],
            ['state', 123],
            ['street', null],
            ['street', 123],
            ['street', str_repeat('123', 128)],
            ['streetNumber', null],
            ['streetNumber', 123],
            ['streetNumber', str_repeat('123', 128)],
            ['postalCode', null],
            ['postalCode', 123],
            ['postalCode', str_repeat('123', 128)],
            ['city', null],
            ['city', 123],
            ['city', str_repeat('123', 128)],
            ['province', null],
            ['province', 123],
            ['province', str_repeat('123', 128)],
            ['country', null],
            ['country', 123],
            ['country', str_repeat('123', 128)],
            ['company', null],
            ['company', 456],
            ['company', str_repeat('123', 128)],
        ];
    }

    /**
     * @param string $attribute
     * @param string $value
     * @dataProvider provideInvalidEntities
     * @group locations_create
     */
    public function testInvalidEntities($attribute, $value)
    {
        $this->actingAs($this->user);

        $location = factory(Location::class)->make();
        $location->type()->associate(LocationType::where('name', '=', 'building')->first());
        $location->state()->associate(LocationState::where('name', '=', 'active')->first());
        $location->company()->associate($this->company);
        $location->country()->associate(Country::find('de'));

        $data = array_merge([
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
        ], [$attribute => $value]);

        $this->json('POST', '/locations', $data);
        $this->seeStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->seeHeader('Content-Type', 'application/json');
    }
}
