<?php

use App\TaskType;
use App\Company;
use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Ramsey\Uuid\Uuid;

class ViewTaskTypeTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * @var \App\TaskType $taskType
     */
    protected $taskType;

    public function setUp()
    {
    	parent::setUp();

    	$this->taskType = factory(TaskType::class)->make();
        $this->taskType->company()->associate($this->company);
        $this->taskType->id = Uuid::uuid4()->toString();
        $this->taskType->save();
    }

    public function provideInvalidAccessData()
    {
        return [
            [null, Response::HTTP_UNAUTHORIZED]
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
        $this->json('GET', '/tasks/types' . $this->taskType->id);
        $this->seeStatusCode($statusCode);
        $this->seeHeader('Content-Type', 'application/json');
    }

    public function provideValidAccessData()
    {
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
    public function testValidAccess($userKey)
    {
        $user = $this->getUser($userKey);
        $this->actingAs($user);
        $this->json('GET', '/tasks/types/' . $this->taskType->id);
        $this->seeStatusCode(Response::HTTP_OK);
        $this->seeHeader('Content-Type', 'application/json');
        $this->seeJsonStructure([
            'data' => [
                'id',
                'name'
            ],
        ])->seeJsonNotNull([
            'data' => [
                'id',
                'name'
            ],
        ])->seeJsonContains([
            'id' => $this->taskType->id,
            'name' => $this->taskType->name
        ]);
    }
}
