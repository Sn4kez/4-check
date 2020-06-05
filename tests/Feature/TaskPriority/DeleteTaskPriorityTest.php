<?php

use App\TaskPriority;
use App\Company;
use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Ramsey\Uuid\Uuid;

class DeleteTaskPriorityTest extends TestCase
{
    use DatabaseMigrations;
    
    /**
     * @var \App\TaskPriority $TaskPriority
     */
    protected $taskPriority;

    public function setUp()
    {
    	parent::setUp();

    	$this->taskPriority = factory(TaskPriority::class)->make();
        $this->taskPriority->id = Uuid::uuid4()->toString();
        $this->taskPriority->company()->associate($this->company);
        $this->taskPriority->save();
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
        $this->json('DELETE', '/tasks/priorities/' . $this->taskPriority->id);
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
        $this->json('DELETE', '/tasks/priorities/' . $this->taskPriority->id);
        $this->seeStatusCode(Response::HTTP_NO_CONTENT);
        $this->assertSoftDeleted(
            'task_priorities',
            ['id' => $this->taskPriority->id],
            TaskPriority::DELETED_AT);
    }
}
