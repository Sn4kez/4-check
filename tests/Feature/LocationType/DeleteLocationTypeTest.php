<?php

use App\LocationType;
use App\Company;
use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Ramsey\Uuid\Uuid;

class DeleteLocationTypeTest extends TestCase
{
	use DatabaseMigrations;
    
    /**
     * @var \App\LocationType $locationType
     */
    protected $locationType;

    public function setUp()
    {
    	parent::setUp();

    	$this->locationType = factory(LocationType::class)->make();
        $this->locationType->id = Uuid::uuid4()->toString();
        $this->locationType->company()->associate($this->company);
        $this->locationType->save();
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
     */
    public function testInvalidAccess($userKey, $statusCode)
    {
        $this->json('DELETE', '/locations/types/' . $this->locationType->id);
        $this->seeStatusCode($statusCode);
        $this->seeHeader('Content-Type', 'application/json');
    }

    public function provideValidAccessData()
    {
        return [
            [self::$ADMIN],
            [self::$SUPER_ADMIN],
        ];
    }

    /**
     * @param $userKey
     * @dataProvider provideValidAccessData
     */
    public function testValidAccess($userKey)
    {
        $user = $this->getUser($userKey);
        $this->actingAs($user);
        $this->json('DELETE', '/locations/types/' . $this->locationType->id);
        $this->seeStatusCode(Response::HTTP_NO_CONTENT);
        $this->assertSoftDeleted(
            'location_types',
            ['id' => $this->locationType->id],
            LocationType::DELETED_AT);
    }
}