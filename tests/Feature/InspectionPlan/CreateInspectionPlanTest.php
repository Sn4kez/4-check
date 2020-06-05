<?php

use App\InspectionPlan;
use App\Company;
use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;

class CreateInspectionPlanTest extends TestCase
{
    use DatabaseMigrations;

    public function setUp()
    {
        parent::setUp();
        $this->checklist = $this->makeFakeChecklist(['with_description']);
        $this->checklist->save();
        $this->company->directory->entry($this->checklist)->save();
    }

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
        $this->json('POST', '/audits/plans');
        $this->seeStatusCode($statusCode);
        $this->seeHeader('Content-Type', 'application/json');
    }

    public function provideValidAccessData() {
        return [
            [self::$ADMIN],
            [self::$SUPER_ADMIN],
        ];
    }

    /**
     * @param string $userKey
     * @param $image
     * @dataProvider provideValidAccessData
     */
    public function testValidAccess($userKey ) {
        $user = $this->getUser($userKey);
        $this->actingAs($user);

        $plan = new InspectionPlan();
        $plan->name = 'test';
        $plan->type = 'daily';
        $plan->factor = 2;
        $plan->startTime = '08:00';
        $plan->endTime = '09:00';
        $plan->company()->associate($this->company);
        $plan->user()->associate($this->user);
        $plan->checklist()->associate($this->checklist);

        $postData = [
            'name' => $plan->name,
	        'type' => $plan->type,
	        'factor' => $plan->factor,
	        'startTime' => $plan->startTime,
	        'endTime' => $plan->endTime,
	        'checklist' => $plan->checklist->id,
            'user' => $plan->user->id,
            'company' => $plan->company->id,
        ];


        $this->json('POST', '/audits/plans', $postData);
        $this->seeStatusCode(Response::HTTP_CREATED);
        $this->seeHeader('Content-Type', 'application/json');
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
            'name' => $plan->name,
	        'type' => $plan->type,
	        'factor' => $plan->factor,
	        'startTime' => $plan->startTime,
	        'endTime' => $plan->endTime,
	        'checklist' => $plan->checklist->id,
            'user' => $plan->user->id,
            'company' => $plan->company->id,
        ]);

        $this->seeInDatabase('audits', [
            'checklistId' => $plan->checklist->id,
        ]);
    }

    public function provideInvalidEntities() {
        return [
            ['name', null],
            ['name', 123],
        ];
    }

    /**
     * @param string $attribute
     * @param string $value
     * @dataProvider provideInvalidEntities
     */
    public function testInvalidEntities($attribute, $value) {
    	$user = $this->getUser(self::$ADMIN);
        $this->actingAs($user);

        $plan = new InspectionPlan();
        $plan->name = 'test';
        $plan->type = 'daily';
        $plan->factor = 2;
        $plan->startTime = '08:00';
        $plan->endTime = '09:00';
        $plan->company()->associate($this->company);
        $plan->user()->associate($this->user);
        $plan->checklist()->associate($this->checklist);

        $data = array_merge([
            'name' => $plan->name,
	        'type' => $plan->type,
	        'factor' => $plan->factor,
	        'startTime' => $plan->startTime,
	        'endTime' => $plan->endTime,
	        'checklist' => $plan->checklist->id,
            'user' => $plan->user->id,
            'company' => $plan->company->id,
        ], [$attribute => $value]);


        $this->json('POST', '/audits/plans', $data);
        $this->seeStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->seeHeader('Content-Type', 'application/json');
    }
}
