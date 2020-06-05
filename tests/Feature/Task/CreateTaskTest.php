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
use App\Notification;

class CreateTaskTest extends TestCase
{

    use DatabaseMigrations;

    public function provideInvalidAccessData() {
        return [
            [null, Response::HTTP_UNAUTHORIZED],
            [self::$USER, Response::HTTP_UNAUTHORIZED],
        ];
    }

    /**
     * @param string $userKey
     * @param int $statusCode
     * @dataProvider provideInvalidAccessData
     */
    public function testInvalidAccess($userKey, $statusCode) {
        $this->json('POST', '/tasks');
        $this->seeStatusCode($statusCode);
        $this->seeHeader('Content-Type', 'application/json');
    }

    public function provideValidAccessData() {
        return [
            [self::$USER, 'logo.jpg'],
            [self::$ADMIN, null],
            [self::$SUPER_ADMIN, 'logo.jpg']
        ];
    }

    /**
     * @param string $userKey
     * @param $image
     * @dataProvider provideValidAccessData
     * @group test77
     */
    public function testValidAccess($userKey, $image) {
        $user = $this->getUser($userKey);
        $this->actingAs($user);
        $task1 = factory(Task::class)->make();
        $task1->type()->associate(TaskType::where('name', '=', 'offer')->first());
        $task1->priority()->associate(TaskPriority::where('name', '=', 'low')->first());
        $task1->state()->associate(TaskState::where('name', '=', 'todo')->first());
        $task1->issuer()->associate($user);
        $task1->assignee()->associate($user);
        $task1->company()->associate($this->company);

        $location = factory(Location::class)->make();
        $location->type()->associate(LocationType::where('name', '=', 'building')->first());
        $location->state()->associate(LocationState::where('name', '=', 'active')->first());
        $location->company()->associate($this->company);
        $location->country()->associate(Country::find('de'));
        $location->save();

        $task1->location()->associate($location);

        $postData = [
            'name' => $task1->name,
            'description' => $task1->description,
            'giveNotice' => $task1->giveNotice,
            'doneAt' => $task1->doneAt,
            'assignedAt' => $task1->assignedAt,
            'type' => $task1->type->id,
            'priority' => $task1->priority->id,
            'state' => $task1->state->id,
            'issuer' => $task1->issuer->id,
            'assignee' => $task1->assignee->id,
            'location' => $task1->location->id,
            'company' => $task1->company->id,
        ];

        if (!is_null($image)) {
            $fileLocation = sprintf("%s/files/%s", dirname(__FILE__), $image);

            if (file_exists($fileLocation)) {
                $postData['source_b64'] = base64_encode(file_get_contents($fileLocation));
            }
        }

        $this->json('POST', '/tasks', $postData);
        $this->seeStatusCode(Response::HTTP_CREATED);
        $this->seeHeader('Content-Type', 'application/json');
        $this->seeJsonStructure([
            'data' => [
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
                'assignee',
                'location',
                'image'
            ],
        ])->seeJsonNotNull([
            'data' => array_merge([
                'id',
                'name',
            ], !is_null($image) ? ['image'] : []),
        ])->seeJsonContains([
            'name' => $task1->name,
            'description' => $task1->description,
            'giveNotice' => $task1->giveNotice,
            'doneAt' => $task1->doneAt,
            'assignedAt' => $task1->assignedAt,
            'type' => $task1->type->id,
            'priority' => $task1->priority->id,
            'state' => $task1->state->id,
            'issuer' => $task1->issuer->id,
            'assignee' => $task1->assignee->id,
            'location' => $task1->location->id,
            'company' => $task1->company->id
        ]);

        /*$this->seeInDatabase('notifications', [
            'title' => Notification::TITLE_TASK_ASSIGNED,
            'user_id' => $task1->assigneeId
        ]);*/

        $postData = [
            'name' => $task1->name,
            'description' => null,
            'giveNotice' => $task1->giveNotice,
            'doneAt' => $task1->doneAt,
            'assignedAt' => $task1->assignedAt,
            'type' => $task1->type->id,
            'priority' => $task1->priority->id,
            'state' => $task1->state->id,
            'issuer' => $task1->issuer->id,
            'assignee' => $task1->assignee->id,
            'location' => $task1->location->id,
            'company' => $task1->company->id,
        ];

        if (!is_null($image)) {
            $fileLocation = sprintf("%s/files/%s", dirname(__FILE__), $image);

            if (file_exists($fileLocation)) {
                $postData['source_b64'] = base64_encode(file_get_contents($fileLocation));
            }
        }

        $this->json('POST', '/tasks', $postData);
        $this->seeStatusCode(Response::HTTP_CREATED);
        $this->seeHeader('Content-Type', 'application/json');
        $this->seeJsonStructure([
            'data' => [
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
                'assignee',
                'location',
                'image'
            ],
        ])->seeJsonNotNull([
            'data' => array_merge([
                'id',
                'name',
            ], !is_null($image) ? ['image'] : []),
        ])->seeJsonContains([
            'name' => $task1->name,
            'description' => null,
            'giveNotice' => $task1->giveNotice,
            'doneAt' => $task1->doneAt,
            'assignedAt' => $task1->assignedAt,
            'type' => $task1->type->id,
            'priority' => $task1->priority->id,
            'state' => $task1->state->id,
            'issuer' => $task1->issuer->id,
            'assignee' => $task1->assignee->id,
            'location' => $task1->location->id,
            'company' => $task1->company->id
        ]);

        /*$this->seeInDatabase('notifications', [
            'title' => Notification::TITLE_TASK_ASSIGNED,
            'user_id' => $task1->assigneeId
        ]);*/
    }

    public function provideInvalidEntities() {
        return [
            ['name', null],
            ['name', 123],
            ['name', str_repeat('123', 128)],
            ['description', 123],
            ['giveNotice', -1],
            ['giveNotice', 123],
            ['giveNotice', 'abc'],
            ['doneAt', 'abc'],
            ['doneAt', 123],
            ['type', 'edit'],
            ['type', 123],
            ['state', 'edit'],
            ['state', 123],
            ['issuer', null],
            ['assignee', null],
        ];
    }

    /**
     * @param string $attribute
     * @param string $value
     * @dataProvider provideInvalidEntities
     */
    public function testInvalidEntities($attribute, $value) {
        $this->actingAs($this->user);
        $task1 = factory(Task::class)->make();
        $task1->type()->associate(TaskType::where('name', '=', 'offer')->first());
        $task1->priority()->associate(TaskPriority::where('name', '=', 'low')->first());
        $task1->state()->associate(TaskState::where('name', '=', 'todo')->first());
        $task1->issuer()->associate($this->user);
        $task1->assignee()->associate($this->user);
        $task1->company()->associate($this->company);

        $location = factory(Location::class)->make();
        $location->type()->associate(LocationType::where('name', '=', 'building')->first());
        $location->state()->associate(LocationState::where('name', '=', 'active')->first());
        $location->company()->associate($this->company);
        $location->country()->associate(Country::find('de'));
        $location->save();

        $task1->location()->associate($location);

        $data = array_merge([
            'name' => $task1->name,
            'description' => $task1->description,
            'giveNotice' => $task1->giveNotice,
            'doneAt' => $task1->doneAt,
            'assignedAt' => $task1->assignedAt,
            'type' => $task1->type->id,
            'priority' => $task1->priority->id,
            'state' => $task1->state->id,
            'issuer' => $task1->issuer->id,
            'assignee' => $task1->assignee->id,
            'location' => $task1->location->id,
            'company' => $task1->company->id
        ], [$attribute => $value]);

        $this->json('POST', '/tasks', $data);
        $this->seeStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->seeHeader('Content-Type', 'application/json');
    }
}
