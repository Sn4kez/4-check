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

class FilterTaskByPeriodTest extends TestCase
{
	use DatabaseMigrations;

	/**
     * @var Task $tasks
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

        $task1 = factory(Task::class)->make();;
        $task1->type()->associate(TaskType::where('name', '=', 'offer')->first());
        $task1->priority()->associate(TaskPriority::where('name', '=', 'low')->first());
        $task1->state()->associate(TaskState::where('name', '=', 'todo')->first());
        $task1->issuer()->associate($this->user);
        $task1->assignee()->associate($this->user);
        $task1->location()->associate($location);
        $task1->company()->associate($this->company);
        $task1->doneAt = '2018-07-13';
        $task1->save();

        $task2 = factory(Task::class)->make();;
        $task2->type()->associate(TaskType::where('name', '=', 'call')->first());
        $task2->priority()->associate(TaskPriority::where('name', '=', 'medium')->first());
        $task2->state()->associate(TaskState::where('name', '=', 'done')->first());
        $task2->issuer()->associate($this->user);
        $task2->assignee()->associate($this->otherUser);
        $task2->location()->associate($location);
        $task2->company()->associate($this->company);
        $task2->doneAt = '2019-07-13';
        $task2->save();

        $this->task = $task1;
    }

    public function provideValidAccessData()
    {
        return [
            [self::$USER],
            [self::$SUPER_ADMIN],
        ];
    }

    /**
     * @param $userKey
     * @param $attribute
     * @param $value
     * @param $taskArrayId
     * @dataProvider provideValidAccessData
     */
    public function testValidAccess($userKey)
    {
    	$user = $this->getUser($userKey);
        $this->actingAs($user);

        $company = $this->user->company->id;

        if($user->isSuperAdmin()) {
            $compnay = $this->company->id;
        }

        $start = '2018-07-01';
        $end = '2018-07-20';

        $uri = '/tasks/company/' . $company . '?start=' . $start . '&end=' . $end;

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
        $task = $this->task;

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
