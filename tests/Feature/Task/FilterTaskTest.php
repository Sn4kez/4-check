<?php

use App\Task;
use App\TaskType;
use App\TaskPriority;
use App\TaskState;
use App\Location;
use App\LocationType;
use App\LocationState;
use App\Company;
use App\Country;
use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Ramsey\Uuid\Uuid;

class FilterTaskTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * @var array $tasks
     */
    protected $tasks;

    /**
     * @var \App\Location $location
     */
    protected $location;

    /**
     * @var \App\Location $otherLocation
     */
    protected $otherLocation;


    public function setUp()
    {
        parent::setUp();

        $this->location = factory(Location::class)->make();
        $this->location->type()->associate(LocationType::where('name', '=', 'building')->first());
        $this->location->state()->associate(LocationState::where('name', '=', 'active')->first());
        $this->location->company()->associate($this->company);
        $this->location->country()->associate(Country::find('de'));
        $this->location->save();

        $task1 = factory(Task::class)->make();;
        $task1->type()->associate(TaskType::where('name', '=', 'offer')->first());
        $task1->priority()->associate(TaskPriority::where('name', '=', 'low')->first());
        $task1->state()->associate(TaskState::where('name', '=', 'todo')->first());
        $task1->issuer()->associate($this->user);
        $task1->assignee()->associate($this->user);
        $task1->location()->associate($this->location);
        $task1->company()->associate($this->company);
        $task1->save();

        $this->otherLocation = factory(Location::class)->make();
        $this->otherLocation->type()->associate(LocationType::where('name', '=', 'building')->first());
        $this->otherLocation->state()->associate(LocationState::where('name', '=', 'active')->first());
        $this->otherLocation->company()->associate($this->company);
        $this->otherLocation->country()->associate(Country::find('de'));
        $this->otherLocation->save();

        $task2 = factory(Task::class)->make();;
        $task2->type()->associate(TaskType::where('name', '=', 'call')->first());
        $task2->priority()->associate(TaskPriority::where('name', '=', 'medium')->first());
        $task2->state()->associate(TaskState::where('name', '=', 'done')->first());
        $task2->issuer()->associate($this->user);
        $task2->assignee()->associate($this->otherUser);
        $task2->location()->associate($this->otherLocation);
        $task2->company()->associate($this->company);
        $task2->save();

        $this->tasks = [
        	$task1,
        	$task2
        ];
    }

    public function provideValidAccessData()
    {
        return [
            [self::$USER, 'type', 'offer', 0],
            [self::$USER, 'state', 'todo', 0],
            [self::$USER, 'priority', 'low', 0],
            [self::$USER, 'location', null, 0],
            [self::$USER, 'type', 'call', 1],
            [self::$USER, 'state', 'done', 1],
            [self::$USER, 'priority', 'medium', 1],
            [self::$USER, 'location', null, 1],
            [self::$SUPER_ADMIN, 'type', 'offer', 0],
            [self::$SUPER_ADMIN, 'state', 'todo', 0],
            [self::$SUPER_ADMIN, 'priority', 'low', 0],
            [self::$SUPER_ADMIN, 'location', null, 0],
            [self::$SUPER_ADMIN, 'issuer', null, 0],
            [self::$SUPER_ADMIN, 'assignee', null, 0], 
            [self::$SUPER_ADMIN, 'type', 'call', 1],
            [self::$SUPER_ADMIN, 'state', 'done', 1],
            [self::$SUPER_ADMIN, 'priority', 'medium', 1],
            [self::$SUPER_ADMIN, 'location', null, 1],
        ];
    }

    /**
     * @param $userKey
     * @param $attribute
     * @param $value
     * @param $taskArrayId
     * @dataProvider provideValidAccessData
     */
    public function testValidAccess($userKey, $attribute, $value, $taskArrayId)
    {
    	$user = $this->getUser($userKey);
        $this->actingAs($user);

        if ($value === null) {
            if ($taskArrayId === 0) {
                $value = $this->location->id;
            } else {
                $value = $this->otherLocation->id;
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

        if($attribute == 'issuer' || $attribute == 'assignee') {
            $value = $this->user->id;
        }

        $company = $this->user->company->id;

        if($user->isSuperAdmin()) {
            $compnay = $this->company->id;
        }

        $uri = '/tasks/company/' . $company . '?' . $attribute . '=' . $value;

        $this->json('GET', $uri);
        $this->seeStatusCode(Response::HTTP_OK);
        $this->seeHeader('Content-Type', 'application/json');
        $this->seeJsonStructure([
            'data' => [
                [
                    'id',
                    'name',
                    'description',
                    'giveNotice',
                    'doneAt',
                    'assignedAt',
                    'type',
                    'priority',
                    'state',
                    'issuer',
                    'assignee'
                ],
            ],
        ]);
        $task = $this->tasks[$taskArrayId];

        $this->seeJsonContains([
            'name' => $task->name,
            'description' => $task->description,
            'giveNotice' => (string) $task->giveNotice,
            'doneAt' => $task->doneAt,
            'assignedAt' => $task->assignedAt,
            'type' => $task->type->id,
            'priority' => $task->priority->id,
            'state' => $task->state->id,
            'issuer' => $task->issuer->id,
            'assignee' => $task->assignee->id
        ]);
    }
}
