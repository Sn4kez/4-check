<?php

use App\LocationState;
use App\Company;
use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Ramsey\Uuid\Uuid;

class DeleteLocationStateTest extends TestCase
{
	use DatabaseMigrations;
    
    /**
     * @var \App\LocationState $locationState
     */
    protected $locationState;

    public function setUp()
    {
    	parent::setUp();

    	$this->locationState = factory(LocationState::class)->make();
        $this->locationState->id = Uuid::uuid4()->toString();
        $this->locationState->company()->associate($this->company);
        $this->locationState->save();
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
        $this->json('DELETE', '/locations/states/' . $this->locationState->id);
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
        $this->json('DELETE', '/locations/states/' . $this->locationState->id);
        $this->seeStatusCode(Response::HTTP_NO_CONTENT);
        $this->assertSoftDeleted(
            'location_states',
            ['id' => $this->locationState->id],
            LocationState::DELETED_AT);
    }
}
