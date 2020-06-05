<?php

use App\Location;
use App\LocationType;
use App\LocationState;
use App\Company;
use App\Country;
use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;

class ViewLocationTest extends TestCase {
    use DatabaseMigrations;

    /**
     * @var \App\Location $location
     */
    protected $location;

    /**
     * @var string
     */
    private $parentLocationId = '';

    public function setUp() {
        parent::setUp();

        $location1 = factory(Location::class)->make();
        $location1->type()->associate(LocationType::where('name', '=', 'building')->first());
        $location1->state()->associate(LocationState::where('name', '=', 'active')->first());
        $location1->company()->associate($this->user->company);
        $location1->country()->associate(Country::find('de'));
        $location1->name = 'parent';
        $location1->id = \Ramsey\Uuid\Uuid::uuid4()->toString();
        $location1->save();

        $this->location = factory(Location::class)->make();
        $this->location->type()->associate(LocationType::where('name', '=', 'building')->first());
        $this->location->state()->associate(LocationState::where('name', '=', 'active')->first());
        $this->location->company()->associate($this->company);
        $this->location->country()->associate(Country::find('de'));
        $this->location->parentId = $location1->id;
        $this->location->name = 'child';
        $this->location->save();

        $this->parentLocationId = $location1->id;
    }

    public function provideInvalidAccessData() {
        return [
            [
                null,
                Response::HTTP_UNAUTHORIZED
            ]
        ];
    }

    /**
     * @param string $userKey
     * @param string $userIdKey
     * @param int $statusCode
     * @dataProvider provideInvalidAccessData
     */
    public function testInvalidAccess($userKey, $statusCode) {
        $this->json('GET', '/locations/' . $this->location->id);
        $this->seeStatusCode($statusCode);
        $this->seeHeader('Content-Type', 'application/json');
    }

    public function provideValidAccessData() {
        return [
            [self::$USER],
            [self::$ADMIN],
            [self::$SUPER_ADMIN],
        ];
    }

    /**
     * @param $userKey
     * @dataProvider provideValidAccessData
     */
    public function testValidAccess($userKey) {
        $user = $this->getUser($userKey);
        $this->actingAs($user);
        $this->json('GET', '/locations/' . $this->location->id);
        $this->seeStatusCode(Response::HTTP_OK);
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
                'company',
                'parentId',
                'parent'
            ],
        ])->seeJsonNotNull([
            'data' => [
                'id',
                'name',
                'parentId',
                'parent'
            ],
        ])->seeJsonContains([
            'name' => $this->location->name,
            'description' => $this->location->description,
            'street' => $this->location->street,
            'streetNumber' => $this->location->streetNumber,
            'city' => $this->location->city,
            'postalCode' => $this->location->postalCode,
            'province' => $this->location->province,
            'type' => $this->location->type->id,
            'state' => $this->location->state->id,
            'country' => $this->location->country->id,
            'company' => $this->location->company->id,
            'parentId' => $this->parentLocationId,
            'parent' => 'parent'
        ]);
    }
}
