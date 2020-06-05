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
use App\Media;

class ViewTaskTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * @var \App\Task $task
     */
    protected $task;

    private $mediaBase64ContentCheckString;

    public function setUp()
    {
        parent::setUp();

        $uploadFeedback = Media::uploadUnitTestTestImage(sprintf("%s/files/logo.jpg", dirname(__FILE__)));
        $this->mediaBase64ContentCheckString = $uploadFeedback["base64Content"];
        $mediaName = $uploadFeedback["mediaName"];

        $this->task = factory(Task::class)->make();;
        $this->task->type()->associate(TaskType::where('name', '=', 'offer')->first());
        $this->task->priority()->associate(TaskPriority::where('name', '=', 'low')->first());
        $this->task->state()->associate(TaskState::where('name', '=', 'todo')->first());
        $this->task->issuer()->associate($this->user);
        $this->task->assignee()->associate($this->user);
        $this->task->company()->associate($this->company);

        $location = factory(Location::class)->make();
        $location->type()->associate(LocationType::where('name', '=', 'building')->first());
        $location->state()->associate(LocationState::where('name', '=', 'active')->first());
        $location->company()->associate($this->company);
        $location->country()->associate(Country::find('de'));
        $location->save();

        $this->task->location()->associate($location);
        $this->task->image = $mediaName;
        $this->task->save();
    }

    public function provideInvalidAccessData()
    {
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
    public function testInvalidAccess($userKey, $statusCode)
    {
        $this->json('GET', '/tasks/' . $this->task->id);
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
        $this->json('GET', '/tasks/' . $this->task->id);
        $this->seeStatusCode(Response::HTTP_OK);
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
            'data' => [
                'id',
                'name',
                'image'
            ],
        ])->seeJsonContains([
            'name' => $this->task->name,
            'description' => $this->task->description,
            'giveNotice' => (string) $this->task->giveNotice,
            'doneAt' => $this->task->doneAt,
            'assignedAt' => $this->task->assignedAt,
            'type' => $this->task->type->id,
            'priority' => $this->task->priority->id,
            'state' => $this->task->state->id,
            'issuer' => $this->task->issuer->id,
            'assignee' => $this->task->assignee->id,
            'location' => $this->task->location->id,
            'image' => $this->mediaBase64ContentCheckString
        ]);
    }
}
