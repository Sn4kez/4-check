<?php

use App\TaskType;
use App\Company;
use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Ramsey\Uuid\Uuid;

class DeleteTaskTypeTest extends TestCase
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
        $this->taskType->id = Uuid::uuid4()->toString();
        $this->taskType->company()->associate($this->company);
        $this->taskType->save();
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
        $this->json('DELETE', '/tasks/types/' . $this->taskType->id);
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
        $this->json('DELETE', '/tasks/types/' . $this->taskType->id);
        $this->seeStatusCode(Response::HTTP_NO_CONTENT);
        $this->assertSoftDeleted(
            'Task_types',
            ['id' => $this->taskType->id],
            TaskType::DELETED_AT);
    }
}
