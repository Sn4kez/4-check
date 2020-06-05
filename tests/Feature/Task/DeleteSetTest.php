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

class DeleteSetTaskTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * @var \App\Task $task
     */
    protected $tasks;


    public function setUp()
    {
        parent::setUp();

        $location = factory(Location::class)->make();
        $location->type()->associate(LocationType::where('name', '=', 'building')->first());
        $location->state()->associate(LocationState::where('name', '=', 'active')->first());
        $location->company()->associate($this->company);
        $location->country()->associate(Country::find('de'));
        $location->save();

        $task1 = factory(Task::class)->make();;
        $task1->type()->associate(TaskType::where('name', '=', 'offer')->first());
        $task1->priority()->associate(TaskPriority::where('name', '=', 'low')->first());
        $task1->state()->associate(TaskState::where('name', '=', 'todo')->first());
        $task1->issuer()->associate($this->user);
        $task1->assignee()->associate($this->user);
        $task1->location()->associate($location);
        $task1->company()->associate($this->company);
        $task1->save();

        $task2 = factory(Task::class)->make();;
        $task2->type()->associate(TaskType::where('name', '=', 'offer')->first());
        $task2->priority()->associate(TaskPriority::where('name', '=', 'low')->first());
        $task2->state()->associate(TaskState::where('name', '=', 'todo')->first());
        $task2->issuer()->associate($this->user);
        $task2->assignee()->associate($this->user);
        $task2->location()->associate($location);
        $task2->company()->associate($this->company);
        $task2->save();

        $this->tasks = [
        	$task1,
        	$task2
        ];
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
        $this->json('PATCH', '/tasks/delete');
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
        $this->json('PATCH', '/tasks/delete', [
        	'items' => [
        		$this->tasks[0]->id,
        		$this->tasks[1]->id,
        	]
        ]);
        $this->seeStatusCode(Response::HTTP_NO_CONTENT);
        $this->assertSoftDeleted(
            'tasks',
            ['id' => $this->tasks[0]->id,],
            Task::DELETED_AT);
        $this->assertSoftDeleted(
            'tasks',
            ['id' => $this->tasks[1]->id,],
            Task::DELETED_AT);

        $this->seeInDatabase('notifications', [
            'title' => Notification::TITLE_TASK_DELETED,
            'user_id' => $this->tasks[0]->assigneeId
        ]);

        $this->seeInDatabase('notifications', [
            'title' => Notification::TITLE_TASK_DELETED,
            'user_id' => $this->tasks[1]->assigneeId
        ]);
    }
}
