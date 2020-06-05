<?php

use App\InspectionPlan;
use App\Company;
use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;

class indexInspectionPlanTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * @var \App\InspectionPlan $plan
     */
    protected $plans;

    public function setUp()
    {
        parent::setUp();
        $this->checklist = $this->makeFakeChecklist(['with_description']);
        $this->checklist->save();
        $this->company->directory->entry($this->checklist)->save();

        $plan = new InspectionPlan();
        $plan->name = 'test';
        $plan->type = 'daily';
        $plan->factor = 2;
        $plan->startTime = '08:00';
        $plan->endTime = '09:00';
        $plan->company()->associate($this->company);
        $plan->user()->associate($this->user);
        $plan->checklist()->associate($this->checklist);
        $plan->save();

        $plan2 = new InspectionPlan();
        $plan2->name = 'test2';
        $plan2->type = 'daily';
        $plan2->factor = 2;
        $plan2->startTime = '08:00';
        $plan2->endTime = '09:00';
        $plan2->company()->associate($this->company);
        $plan2->user()->associate($this->user);
        $plan2->checklist()->associate($this->checklist);
        $plan2->save();

        $this->plans = [
        	$plan,
        	$plan2
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
        $this->json('GET', '/audits/plans/company/' . $this->company->id);
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
        $this->json('GET', '/audits/plans/company/' . $this->company->id);
        $this->seeStatusCode(Response::HTTP_OK);
        $this->seeJsonStructure([
            'data' => [
                [
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
	            ],
	            [
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
            ]
        ]);

        foreach ($this->plans as $plan) {
            $this->seeJsonContains([
                'name' => $plan->name,
		        'type' => $plan->type,
		        'factor' => (string) $plan->factor,
		        'startTime' => $plan->startTime,
		        'endTime' => $plan->endTime,
		        'checklist' => $plan->checklist->id,
	            'user' => $plan->user->id,
	            'company' => $plan->company->id,
            ]);
        }
    }
}