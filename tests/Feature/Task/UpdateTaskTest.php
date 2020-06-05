<?php

use App\Gender;
use App\Notification;
use App\Task;
use App\TaskType;
use App\TaskPriority;
use App\TaskState;
use App\User;
use App\Location;
use App\LocationType;
use App\LocationState;
use App\Country;
use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;

class UpdateTaskTest extends TestCase
{

    use DatabaseMigrations;

    /**
     * @var \App\Task $task
     */
    protected $task;

    /**
     * @var \App\User $otherUser
     */

    protected $otherUser;

    /**
     * @var \App\Location $location
     */

    protected $location;

    /**
     * @var Task
     */
    protected $notificationTestTask;


    public function setUp() {
        parent::setUp();

        $this->location = factory(Location::class)->make();
        $this->location->type()->associate(LocationType::where('name', '=', 'building')->first());
        $this->location->state()->associate(LocationState::where('name', '=', 'active')->first());
        $this->location->company()->associate($this->company);
        $this->location->country()->associate(Country::find('de'));
        $this->location->save();

        $this->task = factory(Task::class)->make();;
        $this->task->type()->associate(TaskType::where('name', '=', 'offer')->first());
        $this->task->priority()->associate(TaskPriority::where('name', '=', 'low')->first());
        $this->task->state()->associate(TaskState::where('name', '=', 'todo')->first());
        $this->task->issuer()->associate($this->user);
        $this->task->assignee()->associate($this->user);
        $this->task->location()->associate($this->location);
        $this->task->company()->associate($this->company);
        $this->task->save();

        $this->notificationTestTask = factory(Task::class)->make();;
        $this->notificationTestTask->id = \Ramsey\Uuid\Uuid::uuid4()->toString();
        $this->notificationTestTask->type()->associate(TaskType::where('name', '=', 'offer')->first());
        $this->notificationTestTask->priority()->associate(TaskPriority::where('name', '=', 'low')->first());
        $this->notificationTestTask->state()->associate(TaskState::where('name', '=', 'todo')->first());
        $this->notificationTestTask->issuer()->associate($this->user);
        $this->notificationTestTask->assignee()->associate($this->user);
        $this->notificationTestTask->location()->associate($this->location);
        $this->notificationTestTask->company()->associate($this->company);
        $this->notificationTestTask->save();

        $this->otherUser = factory(User::class)->make();
        $this->otherUser->company()->associate($this->user->company);
        $this->otherUser->gender()->associate(Gender::all()->random());
        $this->otherUser->save();
    }

    public function testNotifications() {
        $user = $this->getUser(self::$USER);
        $this->actingAs($user);
        $taskId = $this->notificationTestTask->id;

        $this->json('PATCH', '/tasks/' . $taskId, [
            'assignee' => $this->otherUser->id
        ]);

        $this->seeInDatabase('notifications', [
            'title' => Notification::TITLE_TASK_ASSIGNED,
            'user_id' => $this->otherUser->id
        ]);

        $this->seeInDatabase('notifications', [
            'title' => Notification::TITLE_TASK_UPDATED,
            'user_id' => $this->otherUser->id
        ]);

        $this->json('PATCH', '/tasks/' . $taskId, [
            'state' => TaskState::where('name', '=', TaskState::NAME_DONE)->first()->id
        ]);

        $this->seeInDatabase('notifications', [
            'title' => Notification::TITLE_TASK_COMPLETED,
            'user_id' => $this->otherUser->id
        ]);
    }

    public function provideInvalidAccessData() {
        return [
            [null, Response::HTTP_UNAUTHORIZED],
        ];
    }

    /**
     * @param string $userKey
     * @param string $userIdKey
     * @param int $statusCode
     * @dataProvider provideInvalidAccessData
     */
    public function testInvalidAccess($userKey, $statusCode) {
        $this->json('PATCH', '/tasks/' . $this->task->id);
        $this->seeStatusCode($statusCode);
        $this->seeHeader('Content-Type', 'application/json');
    }

    public function provideValidEntities() {
        return [
            [self::$USER, 'source_b64', 'image', 'logo.jpg'],
            [self::$ADMIN, 'source_b64', 'image', 'logo.jpg'],
            [self::$SUPER_ADMIN, 'source_b64', 'image', 'logo.jpg'],
            [self::$USER, 'source_b64', 'image', null],
            [self::$ADMIN, 'source_b64', 'image', null],
            [self::$SUPER_ADMIN, 'source_b64', 'image', null],
            [self::$USER, 'name', 'name', 'abc'],
            [self::$ADMIN, 'name', 'name', 'abc'],
            [self::$SUPER_ADMIN, 'name', 'name', 'abc'],
            [self::$USER, 'description', 'description', 'abc'],
            [self::$ADMIN, 'description', 'description', 'abc'],
            [self::$SUPER_ADMIN, 'description', 'description', 'abc'],
            [self::$USER, 'description', 'description', null],
            [self::$ADMIN, 'description', 'description', null],
            [self::$SUPER_ADMIN, 'description', 'description', null],
            [self::$USER, 'giveNotice', 'giveNotice', 1],
            [self::$ADMIN, 'giveNotice', 'giveNotice', 1],
            [self::$SUPER_ADMIN, 'giveNotice', 'giveNotice', 1],
            [self::$USER, 'doneAt', 'doneAt', "2018-12-31"],
            [self::$ADMIN, 'doneAt', 'doneAt', "2018-12-31"],
            [self::$SUPER_ADMIN, 'doneAt', 'doneAt', "2018-12-31"],
            [self::$USER, 'type', 'typeId', "call"],
            [self::$ADMIN, 'type', 'typeId', "call"],
            [self::$SUPER_ADMIN, 'type', 'typeId', "call"],
            [self::$USER, 'state', 'stateId', "done"],
            [self::$ADMIN, 'state', 'stateId', "done"],
            [self::$SUPER_ADMIN, 'state', 'stateId', "done"],
            [self::$USER, 'issuer', 'issuerId', null],
            [self::$ADMIN, 'issuer', 'issuerId', null],
            [self::$SUPER_ADMIN, 'issuer', 'issuerId', null],
            [self::$USER, 'assignee', 'assigneeId', null],
            [self::$ADMIN, 'assignee', 'assigneeId', null],
            [self::$SUPER_ADMIN, 'assignee', 'assigneeId', null],
            [self::$USER, 'location', 'locationId', null],
            [self::$ADMIN, 'location', 'locationId', null],
            [self::$SUPER_ADMIN, 'location', 'locationId', null],
        ];
    }

    /**
     * @param $userKey
     * @param $attribute
     * @param $dbAttribute
     * @param $value
     * @dataProvider provideValidEntities
     */
    public function testValidEntities($userKey, $attribute, $dbAttribute, $value) {
        $user = $this->getUser($userKey);
        $this->actingAs($user);
        if ($value === null && $attribute !== 'source_b64') {
            if ($attribute === 'location') {
                $value = $this->location->id;
            } else {
                $value = $this->otherUser->id;
            }
        }

        if ($attribute == 'state') {
            $value = TaskState::where('name', '=', $value)->first()->id;
        }

        if ($attribute == 'type') {
            $value = TaskType::where('name', '=', $value)->first()->id;
        }

        if ($attribute == 'priority') {
            $value = TaskPriority::where('name', '=', $value)->first()->id;
        }

        if ($attribute == 'source_b64' && !is_null($value)) {
            $fileLocation = sprintf("%s/files/%s", dirname(__FILE__), $value);

            if (file_exists($fileLocation)) {
                $value = base64_encode(file_get_contents($fileLocation));
            } else {
                $value = '';
            }
        }

        $data = [$attribute => $value];
        $this->json('PATCH', '/tasks/' . $this->task->id, $data);
        $this->seeStatusCode(Response::HTTP_NO_CONTENT);

        if (!$attribute == 'source_b64') {
            $this->seeInDatabase('tasks', [
                'id' => $this->task->id,
                $dbAttribute => $value,
            ]);
        }
    }

    public function provideInvalidEntities() {
        return [
            [self::$USER, 'name', null],
            [self::$USER, 'name', 123],
            [self::$USER, 'name', str_repeat('123', 128)],
            [self::$USER, 'description', 123],
            [self::$USER, 'giveNotice', -1],
            [self::$USER, 'giveNotice', 123],
            [self::$USER, 'giveNotice', 'abc'],
            [self::$USER, 'doneAt', 'abc'],
            [self::$USER, 'doneAt', 123],
            [self::$USER, 'type', 'edit'],
            [self::$USER, 'type', 123],
            [self::$USER, 'state', 'edit'],
            [self::$USER, 'state', 123],
            [self::$USER, 'location', 'edit'],
            [self::$USER, 'location', 123],
        ];
    }

    /**
     * @param $userKey
     * @param $attribute
     * @param $value
     * @dataProvider provideInvalidEntities
     */
    public function testInvalidEntities($userKey, $attribute, $value) {
        $user = $this->getUser($userKey);
        $this->actingAs($user);
        $data = [$attribute => $value];
        $this->json('PATCH', '/tasks/' . $this->task->id, $data);
        $this->seeStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->seeHeader('Content-Type', 'application/json');
    }
}
