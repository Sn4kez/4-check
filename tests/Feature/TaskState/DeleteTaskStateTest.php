<?php

use App\TaskState;
use App\Company;
use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Ramsey\Uuid\Uuid;

class DeleteTaskStateTest extends TestCase
{
    use DatabaseMigrations;
    
    /**
     * @var \App\TaskState $TaskState
     */
    protected $taskState;

    public function setUp()
    {
    	parent::setUp();

    	$this->taskState = factory(TaskState::class)->make();
        $this->taskState->id = Uuid::uuid4()->toString();
        $this->taskState->company()->associate($this->company);
        $this->taskState->save();
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
        $this->json('DELETE', '/tasks/states/' . $this->taskState->id);
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
        $this->json('DELETE', '/tasks/states/' . $this->taskState->id);
        $this->seeStatusCode(Response::HTTP_NO_CONTENT);
        $this->assertSoftDeleted(
            'task_states',
            ['id' => $this->taskState->id],
            TaskState::DELETED_AT);
    }
}
