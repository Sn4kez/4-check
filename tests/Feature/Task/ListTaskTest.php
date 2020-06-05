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
use App\Media;

class ListTaskTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * @var \App\Task $task
     */
    protected $tasks;

    /**
     * @var \App\Company $company
     */

    protected $companyId;

    private $mediaBase64ContentCheckString;


    public function setUp()
    {
        parent::setUp();

        $uploadFeedback = Media::uploadUnitTestTestImage(sprintf("%s/files/logo.jpg", dirname(__FILE__)));
        $this->mediaBase64ContentCheckString = $uploadFeedback["base64Content"];
        $mediaName = $uploadFeedback["mediaName"];

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
        $task1->image = $mediaName;
        $task1->save();

        $task2 = factory(Task::class)->make();;
        $task2->type()->associate(TaskType::where('name', '=', 'offer')->first());
        $task2->priority()->associate(TaskPriority::where('name', '=', 'low')->first());
        $task2->state()->associate(TaskState::where('name', '=', 'todo')->first());
        $task2->issuer()->associate($this->user);
        $task2->assignee()->associate($this->user);
        $task2->location()->associate($location);
        $task2->company()->associate($this->company);
        $task2->image = $mediaName;
        $task2->save();

        $this->companyId = $this->company->id;

        $this->tasks = [
            $task1,
            $task2
        ];

    }

    public function provideInvalidAccessData()
    {
        return [
            [null, 'random', Response::HTTP_UNAUTHORIZED],
            [null, 'company', Response::HTTP_UNAUTHORIZED],
        ];
    }

    /**
     * @param string $userKey
     * @param string $userIdKey
     * @param int $statusCode
     * @dataProvider provideInvalidAccessData
     */
    public function testInvalidAccess($userKey, $companyIdKey, $statusCode)
    {

        if ($companyIdKey === 'company') {
            $uri = '/tasks/company/' . $this->user->company->id;
        } else {
            $uri = '/tasks/company/' . Uuid::uuid4()->toString();
        }

        $this->json('GET', $uri);
        $this->seeStatusCode($statusCode);
        $this->seeHeader('Content-Type', 'application/json');
    }

    public function provideValidAccessData()
    {
        return [
            [self::$USER, 'company', false],
            [self::$ADMIN, 'company', false],
            [self::$SUPER_ADMIN, 'company', false],
            [self::$OTHER_ADMIN, 'otherCompany', true],
        ];
    }

    /**
     * @param $userKey
     * @param $userIdKey
     * @param $expectEmpty
     * @dataProvider provideValidAccessData
     */
    public function testValidAccess($userKey, $companyKey, $expectEmpty)
    {
        $user = $this->getUser($userKey);
        $this->actingAs($user);

        if ($companyKey === 'company') {
            $uri = '/tasks/company/' . $this->user->company->id;
        } else {
            $uri = '/tasks/company/' . $this->otherUser->company->id;
        }

        $this->json('GET', $uri);
        $this->seeStatusCode(Response::HTTP_OK);
        $this->seeHeader('Content-Type', 'application/json');
        if ($expectEmpty) {
            $this->seeJsonStructure([
                'data' => [],
            ]);
        } else {
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
                        'assignee',
                        'location',
                        'image'
                    ],
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
                        'assignee',
                        'location',
                        'image'
                    ],
                ],
            ]);
            foreach ($this->tasks as $task) {
                $this->seeJsonContains([
                    'name' => $task->name,
                    'description' => $task->description,
                    'giveNotice' => (string)$task->giveNotice,
                    'doneAt' => $task->doneAt,
                    'assignedAt' => $task->assignedAt,
                    'type' => $task->type->id,
                    'priority' => $task->priority->id,
                    'state' => $task->state->id,
                    'issuer' => $task->issuer->id,
                    'assignee' => $task->assignee->id,
                    'location' => $task->location->id,
                    'image' => $this->mediaBase64ContentCheckString
                ]);
            }
        }
    }
}
