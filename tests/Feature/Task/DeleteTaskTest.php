<?php

use App\Notification;
use App\Task;
use App\TaskType;
use App\TaskPriority;
use App\TaskState;
use App\Location;
use App\LocationType;
use App\LocationState;
use App\Country;
use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;

class DeleteTaskTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * @var \App\Task $task
     */
    protected $task;


    public function setUp()
    {
        parent::setUp();

        $location = factory(Location::class)->make();
        $location->type()->associate(LocationType::where('name', '=', 'building')->first());
        $location->state()->associate(LocationState::where('name', '=', 'active')->first());
        $location->company()->associate($this->company);
        $location->country()->associate(Country::find('de'));
        $location->save();

        $this->task = factory(Task::class)->make();;
        $this->task->type()->associate(TaskType::where('name', '=', 'offer')->first());
        $this->task->priority()->associate(TaskPriority::where('name', '=', 'low')->first());
        $this->task->state()->associate(TaskState::where('name', '=', 'todo')->first());
        $this->task->issuer()->associate($this->user);
        $this->task->assignee()->associate($this->user);
        $this->task->location()->associate($location);
        $this->task->company()->associate($this->company);
        $this->task->save();
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
        $this->json('DELETE', '/tasks/' . $this->task->id);
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
        $this->json('DELETE', '/tasks/' . $this->task->id);
        $this->seeStatusCode(Response::HTTP_NO_CONTENT);
        $this->assertSoftDeleted(
            'tasks',
            ['id' => $this->task->id],
            Task::DELETED_AT);

        $this->seeInDatabase('notifications', [
            'title' => Notification::TITLE_TASK_DELETED,
            'user_id' => $this->task->assigneeId
        ]);
    }
}
