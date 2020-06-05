<?php

use App\TaskPriority;
use App\Company;
use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Ramsey\Uuid\Uuid;

class ListTaskPriorityTest extends TestCase
{
    use DatabaseMigrations;

    /* @var App\TaskPriority $taskpriorities */
    protected $taskpriorities;

    public function setUp()
    {
    	parent::setUp();

    	$taskPriority1 = factory(TaskPriority::class)->make();
        $taskPriority1->id = Uuid::uuid4()->toString();
    	$taskPriority1->company()->associate($this->company);
    	$taskPriority1->save();

    	$taskPriority2 = factory(TaskPriority::class)->make();
        $taskPriority2->id = Uuid::uuid4()->toString();
    	$taskPriority2->company()->associate($this->company);
    	$taskPriority2->save();

    	$this->taskpriorities = [
    		TaskPriority::where('name', '=', 'low')->first(),
    		TaskPriority::where('name', '=', 'medium')->first(),
    		TaskPriority::where('name', '=', 'high')->first(),
    		$taskPriority1,
    		$taskPriority2
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
            $uri = '/tasks/priorities/company/' . $this->user->company->id;
        } else {
        	$uri = '/tasks/priorities/company/' . Uuid::uuid4()->toString();
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
            $uri = '/tasks/priorities/company/' . $this->user->company->id;
        } else {
            $uri = '/tasks/priorities/company/' . $this->otherUser->company->id;
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
                    [
                    	'id',
                        'name'  
                    ],
                ],
            ]);
            foreach ($this->taskpriorities as $taskPriority) {
                $this->seeJsonContains([
                    'id' => $taskPriority->id,
                    'name' => $taskPriority->name
                ]);
            }
        }
    }
}
