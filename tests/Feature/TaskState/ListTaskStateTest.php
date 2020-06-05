<?php

use App\TaskState;
use App\Company;
use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Ramsey\Uuid\Uuid;

class ListTaskStateTest extends TestCase
{
    use DatabaseMigrations;

    /* @var App\TaskState $taskStates */
    protected $taskStates;

    public function setUp()
    {
    	parent::setUp();

    	$taskState1 = factory(TaskState::class)->make();
        $taskState1->id = Uuid::uuid4()->toString();
    	$taskState1->company()->associate($this->company);
    	$taskState1->save();

    	$taskState2 = factory(TaskState::class)->make();
        $taskState2->id = Uuid::uuid4()->toString();
    	$taskState2->company()->associate($this->company);
    	$taskState2->save();

    	$this->taskStates = [
    		TaskState::where('name', '=', 'todo')->first(),
    		TaskState::where('name', '=', 'done')->first(),
    		$taskState1,
    		$taskState2
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
            $uri = '/tasks/states/company/' . $this->user->company->id;
        } else {
        	$uri = '/tasks/states/company/' . Uuid::uuid4()->toString();
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
            $uri = '/tasks/states/company/' . $this->user->company->id;
        } else {
            $uri = '/tasks/states/company/' . $this->otherUser->company->id;
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
            foreach ($this->taskStates as $taskState) {
                $this->seeJsonContains([
                    'id' => $taskState->id,
                    'name' => $taskState->name
                ]);
            }
        }
    }
}
