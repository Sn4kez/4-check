<?php

use App\TaskType;
use App\Company;
use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Ramsey\Uuid\Uuid;

class ListTaskTypeTest extends TestCase
{
    use DatabaseMigrations;

    /* @var App\TaskType $taskTypes */
    protected $taskTypes;

    public function setUp()
    {
    	parent::setUp();

    	$taskType1 = factory(TaskType::class)->make();
    	$taskType1->company()->associate($this->company);
        $taskType1->id = Uuid::uuid4()->toString();
    	$taskType1->save();

    	$this->taskTypes = [
    		TaskType::where('name', '=', 'offer')->first(),
    		TaskType::where('name', '=', 'call')->first(),
    		TaskType::where('name', '=', 'disinfection')->first(),
    		TaskType::where('name', '=', 'e-mail')->first(),
    		TaskType::where('name', '=', 'removal')->first(),
    		TaskType::where('name', '=', 'inspection')->first(),
    		TaskType::where('name', '=', 'overhauling')->first(),
    		TaskType::where('name', '=', 'revision')->first(),
    		TaskType::where('name', '=', 'reworking')->first(),
    		TaskType::where('name', '=', 'cleaning')->first(),
    		TaskType::where('name', '=', 'repairing')->first(),
    		TaskType::where('name', '=', 'instruction')->first(),
    		TaskType::where('name', '=', 'miscellaneous')->first(),
    		TaskType::where('name', '=', 'maintenance')->first(),
    		$taskType1
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
            $uri = '/tasks/types/company/' . $this->user->company->id;
        } else {
        	$uri = '/tasks/types/company/' . Uuid::uuid4()->toString();
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
            $uri = '/tasks/types/company/' . $this->user->company->id;
        } else {
            $uri = '/tasks/types/company/' . $this->otherUser->company->id;
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
            foreach ($this->taskTypes as $taskType) {
                $this->seeJsonContains([
                    'id' => $taskType->id,
                    'name' => $taskType->name
                ]);
            }
        }
    }
}
