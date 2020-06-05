<?php

use App\LocationState;
use App\Company;
use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Ramsey\Uuid\Uuid;

class UpdateLocationStateTest extends TestCase
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
        $this->json('PATCH', '/locations/states/' . $this->locationState->id);
        $this->seeStatusCode($statusCode);
        $this->seeHeader('Content-Type', 'application/json');
    }

    public function provideValidEntities()
    {
        return [
            [self::$ADMIN, 'name', 'name', 'abc'],
            [self::$SUPER_ADMIN, 'name', 'name', 'abc'],
            [self::$ADMIN, 'company', 'companyId', null],
            [self::$SUPER_ADMIN, 'company', 'companyId', null],
        ];
    }

    /**
     * @param string $attribute
     * @param string $value
     * @dataProvider provideValidEntities
     */
    public function testValidEntities($userKey, $attribute, $dbAttribute, $value)
    {
    	$user = $this->getUser($userKey);
        $this->actingAs($user);

        if($value === null) 
        {
        	$value = $this->company->id;
        }

        $data = [$attribute => $value];
        $this->json('PATCH', '/locations/states/' . $this->locationState->id, $data);
        $this->seeStatusCode(Response::HTTP_NO_CONTENT);
        $this->seeInDatabase('location_states', [
            'id' => $this->locationState->id,
            $dbAttribute => $value,
        ]);
    }

    public function provideInvalidEntities()
    {
        return [
            [self::$ADMIN, 'name', null],
            [self::$ADMIN, 'name', 123],
            [self::$ADMIN, 'name', str_repeat('123', 128)],
            [self::$ADMIN, 'company', null],
            [self::$ADMIN, 'company', 456],
            [self::$ADMIN, 'company', str_repeat('123', 128)],
        ];
    }

    /**
     * @param $userKey
     * @param $attribute
     * @param $value
     * @dataProvider provideInvalidEntities
     */
    public function testInvalidEntities($userKey, $attribute, $value)
    {
        $user = $this->getUser($userKey);
        $this->actingAs($user);
        $data = [$attribute => $value];
        $this->json('PATCH', '/locations/states/' . $this->locationState->id, $data);
        $this->seeStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->seeHeader('Content-Type', 'application/json');
    }
    
}
