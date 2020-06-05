<?php

use App\TaskPriority;
use App\TaskState;
use App\TaskType;
use App\Location;
use App\Task;

use Laravel\Lumen\Testing\DatabaseMigrations;
use Illuminate\Http\Response;

class ViewDashboardTest extends TestCase
{
    use DatabaseMigrations;

    const DEFAULT_ROUTE = '/dashboard';

    /**
     * @var string
     */
    const REQUEST_PARAM_NAME_DATE_FROM = 'start';

    /**
     * @var string
     */
    const REQUEST_PARAM_NAME_DATE_TO = 'end';

    private $taskUser;

    public function setUp()
    {
        parent::setUp();

        $this->createFakeTasks([$this->user, $this->superAdmin]);
        $this->createFakeAudits();
    }

    private function createFakeTasks($users)
    {
        $taskTemplate = factory(Task::class)->make();
        $taskTemplate->type()->associate(TaskType::where('name', '=', 'offer')->first());
        $taskTemplate->priority()->associate(TaskPriority::where('name', '=', 'low')->first());
        $taskTemplate->state()->associate(TaskState::where('name', '=', 'todo')->first());
        $taskTemplate->issuer()->associate($this->user);
        $taskTemplate->assignee()->associate($this->user);
        $taskTemplate->company()->associate($this->company);

        foreach ($users as $user) {
            $task = clone $taskTemplate;
            $task->assignee()->associate($user);
            $task->issuer()->associate($user);
            $task->company()->associate($user->company);
            $task->id = \Ramsey\Uuid\Uuid::uuid4()->toString();
            $task->location()->associate(Location::where('companyId', '=', $user->company->id)->first());

            $secondTask = clone $task;
            $secondTask->id = \Ramsey\Uuid\Uuid::uuid4()->toString();
            $secondTask->save();

            $task->save();
        }
    }

    private function createFakeAudits()
    {
        $fakeChecklist = $this->makeFakeChecklist();
        $fakeChecklist->save();

        $subdir2 = $this->makeFakeDirectory();
        $subdir2->name = 'layer 2';
        $subdir2->save();

        $subdir3 = $this->makeFakeDirectory();
        $subdir3->name = 'layer 3';
        $subdir3->save();

        $subdir3->entry($fakeChecklist)->save();
        $subdir2->entry($subdir3)->save();

        $this->company->directory->entry($subdir2)->save();

        $fakeAuditState = $this->makeFakeAuditState();
        $fakeAuditState->company()->associate($this->company);
        $fakeAuditState->save();

        $auditTemplate = $this->makeFakeAudit();
        $auditTemplate->company()->associate($this->company);
        $auditTemplate->checklist()->associate($fakeChecklist);

        $audit1 = clone $auditTemplate;
        $audit1->save();

        $audit2 = clone $auditTemplate;
        $audit2->save();

        $audit3 = clone $auditTemplate;
        $audit3->save();

        $audit4 = clone $auditTemplate;
        $audit4->company()->associate($this->superAdmin->company);
        $audit4->save();

        $audit5 = clone $auditTemplate;
        $audit5->company()->associate($this->superAdmin->company);
        $audit5->save();
    }

    public function provideInvalidAccessData()
    {
        return [
            [null, Response::HTTP_UNAUTHORIZED],
            ['random', Response::HTTP_UNAUTHORIZED],
            ['nonexistent', Response::HTTP_UNAUTHORIZED]
        ];
    }

    /**
     * @param string $userKey
     * @param int $statusCode
     * @dataProvider provideInvalidAccessData
     */
    public function testInvalidAccess($userKey, $statusCode)
    {
        if ($userKey != null) {
            $user = $this->getUser($userKey);

            if ($user !== null) {
                $this->actingAs($user);
            }
        }

        $this->json('GET', self::DEFAULT_ROUTE);
        $this->seeStatusCode($statusCode);
        $this->seeHeader('Content-Type', 'application/json');
    }

    public function provideValidAccessData()
    {
        return [
            [self::$USER, 3, 3, 2],
            [self::$SUPER_ADMIN, 2, 2, 2]
        ];
    }

    /**
     * A basic test example.
     *
     * @param $userKey
     * @param $awaitedLastAuditsTotal
     * @param $awaitedNextAuditsTotal
     * @param $awaitedTasksTotal
     * @return void
     * @dataProvider provideValidAccessData
     */
    public function testValidAccess($userKey, $awaitedLastAuditsTotal, $awaitedNextAuditsTotal, $awaitedTasksTotal)
    {
        $user = $this->getUser($userKey);
        $this->actingAs($user);

        $this->json('GET', self::DEFAULT_ROUTE);
        $this->seeStatusCode(Response::HTTP_OK);
        $this->seeHeader('Content-Type', 'application/json');

        $commonStructure = [
            'data' => [
                'last_audits',
                'last_audits_count',
                'last_audits_total',
                'last_audits_pages',
                'next_audits',
                'next_audits_count',
                'next_audits_total',
                'next_audits_pages',
                'tasks',
                'tasks_count',
                'tasks_total',
                'tasks_pages',
                'informations'
            ]
        ];

        $this->seeJsonStructure($commonStructure);
        $this->seeJsonNotNull($commonStructure);

        $this->seeJsonContains([
            'data' => [
                'last_audits_count' => $awaitedLastAuditsTotal,
                'next_audits_total' => $awaitedNextAuditsTotal,
                'tasks_total' => $awaitedTasksTotal
            ]
        ]);
    }
}
