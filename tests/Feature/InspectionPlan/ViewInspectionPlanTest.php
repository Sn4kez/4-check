<?php

use App\InspectionPlan;
use App\Company;
use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;

class ViewInspectionPlanTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * @var \App\InspectionPlan $plan
     */
    protected $plan;

    public function setUp()
    {
        parent::setUp();
        $this->checklist = $this->makeFakeChecklist(['with_description']);
        $this->checklist->save();
        $this->company->directory->entry($this->checklist)->save();

        $this->plan = new InspectionPlan();
        $this->plan->name = 'test';
        $this->plan->type = 'daily';
        $this->plan->factor = 2;
        $this->plan->startTime = '08:00';
        $this->plan->endTime = '09:00';
        $this->plan->company()->associate($this->company);
        $this->plan->user()->associate($this->user);
        $this->plan->checklist()->associate($this->checklist);
        $this->plan->save();
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
        $this->json('GET', '/audits/plans/' . $this->plan->id);
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
        $this->json('GET', '/audits/plans/' . $this->plan->id);
        $this->seeStatusCode(Response::HTTP_OK);
        $this->seeJsonStructure([
            'data' => [
                'id',
                'name',
                'monday',
                'tuesday',
                'wednesday',
                'thursday',
                'friday',
                'saturday',
                'sunday',
                'type',
                'factor',
                'startDate',
                'endDate',
                'startTime',
                'endTime',
                'user',
                'checklist',
                'company',
            ]
        ])->seeJsonNotNull([
            'data' => [
                'id',
                'name',
            ]
        ])->seeJsonContains([
            'name' => $this->plan->name,
            'type' => $this->plan->type,
            'factor' => (string) $this->plan->factor,
            'startTime' => $this->plan->startTime,
            'endTime' => $this->plan->endTime,
            'checklist' => $this->plan->checklist->id,
            'user' => $this->plan->user->id,
            'company' => $this->plan->company->id,
        ]);
    }
}