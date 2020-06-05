<?php

use App\Location;
use App\LocationType;
use App\LocationState;
use App\Country;
use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;

class DeleteSetLocationTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * @var \App\Location $locations
     */
    protected $locations;


    public function setUp()
    {
        parent::setUp();

        $this->generateLocations();
    }

    /**
     * generates a few locations we can delete
     * @param int $maximumLocations
     */
    private function generateLocations($maximumLocations = 10)
    {
        $this->locations = [];

        for ($i = 0; $i < $maximumLocations; $i++) {
            $location = factory(Location::class)->make();
            $location->id = \Ramsey\Uuid\Uuid::uuid4()->toString();
            $location->type()->associate(LocationType::where('name', '=', 'building')->first());
            $location->state()->associate(LocationState::where('name', '=', 'active')->first());
            $location->company()->associate($this->company);
            $location->country()->associate(Country::find('de'));
            $location->save();

            $this->locations[] = $location;
        }
    }

    public function provideInvalidAccessData()
    {
        return [
            [null, Response::HTTP_UNAUTHORIZED],
            [self::$USER, Response::HTTP_UNAUTHORIZED],
        ];
    }

    /**
     * @param string $userKey
     * @param string $userIdKey
     * @param int $statusCode
     * @dataProvider provideInvalidAccessData
     */
    public function testInvalidAccess($userKey, $statusCode)
    {
        $this->json('PATCH', '/locations/delete');
        $this->seeStatusCode($statusCode);
        $this->seeHeader('Content-Type', 'application/json');
    }

    public function provideValidAccessData()
    {
        return [
            [self::$ADMIN, 10],
            [self::$SUPER_ADMIN, 10],
            [self::$SUPER_ADMIN, 1],
            [self::$SUPER_ADMIN, 100],
            [self::$SUPER_ADMIN, 1000],
        ];
    }

    /**
     * @param $userKey
     * @param $countLocations
     * @dataProvider provideValidAccessData
     */
    public function testValidAccess($userKey, $countLocations)
    {
        $user = $this->getUser($userKey);
        $this->actingAs($user);

        $this->generateLocations($countLocations);

        $this->json('PATCH', '/locations/delete', ['items' => $this->getItemsIds()]);
        $this->seeStatusCode(Response::HTTP_NO_CONTENT);

        for ($i = 0; $i < count($this->locations); $i++) {
            $this->assertSoftDeleted('locations', ['id' => $this->locations[$i]->id,], Location::DELETED_AT);
        }
    }

    /**
     * Returns all items ids
     */
    private function getItemsIds()
    {
        $items = [];

        foreach ($this->locations as $location) {
            $items[] = $location->id;
        }

        return $items;
    }
}
