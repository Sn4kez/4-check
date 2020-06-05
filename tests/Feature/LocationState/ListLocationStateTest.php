<?php

use App\LocationState;
use App\Company;
use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Ramsey\Uuid\Uuid;

class ListLocationStateTest extends TestCase
{
    use DatabaseMigrations;

    /* @var App\LocationState $locationStates */
    protected $locationStates;

    public function setUp()
    {
    	parent::setUp();

    	$locationState1 = factory(LocationState::class)->make();
        $locationState1->id = Uuid::uuid4()->toString();
    	$locationState1->company()->associate($this->company);
    	$locationState1->save();

    	$locationState2 = factory(LocationState::class)->make();
        $locationState2->id = Uuid::uuid4()->toString();
    	$locationState2->company()->associate($this->company);
    	$locationState2->save();

    	$this->locationStates = [
    		LocationState::where('name', '=', 'active')->first(),
    		LocationState::where('name', '=', 'inactive')->first(),
    		$locationState1,
    		$locationState2
    	];    	
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
            $uri = '/locations/states/company/' . $this->user->company->id;
        } else {
        	$uri = '/locations/states/company/' . Uuid::uuid4()->toString();
        }
        $this->json('GET', $uri);
        $this->seeStatusCode($statusCode);
        $this->seeHeader('Content-Type', 'application/json');
    }

    public function provideValidAccessData()
    {
        return [
            [self::$ADMIN, 'company', false],
            [self::$SUPER_ADMIN, 'company', false],
            [self::$OTHER_ADMIN, 'otherCompany', true],
        ];
    }

    /**
     * @param $userKey
     * @param $companyKey
     * @param $expectEmpty
     * @dataProvider provideValidAccessData
     */
    public function testValidAccess($userKey, $companyKey, $expectEmpty)
    {
        $user = $this->getUser($userKey);
        $this->actingAs($user);
        if ($companyKey === 'company') {
            $uri = '/locations/states/company/' . $this->user->company->id;
        } else {
            $uri = '/locations/states/company/' . $this->otherUser->company->id;
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
                        'name'
                    ],
                    [
                    	'id',
                        'name'  
                    ],
                    [
                    	'id',
                        'name'
                    ],
                    [
                    	'id',
                        'name'  
                    ],
                ],
            ]);
            foreach ($this->locationStates as $locationState) {
                $this->seeJsonContains([
                    'id' => $locationState->id,
                    'name' => $locationState->name
                ]);
            }
        }
    }
}
