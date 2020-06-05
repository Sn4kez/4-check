<?php

use App\InspectionPlan;
use App\Company;
use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;

class UpdateInspectionPlanTest extends TestCase
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
        $this->json('PATCH', '/audits/plans/' . $this->plan->id);
        $this->seeStatusCode($statusCode);
        $this->seeHeader('Content-Type', 'application/json');
    }

    public function provideValidEntities()
    {
        return [
            [self::$ADMIN, 'name', 'name', 'abc'],
            [self::$SUPER_ADMIN, 'name', 'name', 'abc'],
        ];
    }

    /**
     * @param $userKey
     * @param $attribute
     * @param $dbAttribute
     * @param $value
     * @dataProvider provideValidEntities
     */
    public function testValidEntities($userKey, $attribute, $dbAttribute, $value)
    {
        $user = $this->getUser($userKey);
        $this->actingAs($user);

        $data = [$attribute => $value];

        $this->json('PATCH', '/audits/plans/' . $this->plan->id, $data);
        $this->seeStatusCode(Response::HTTP_NO_CONTENT);
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

        $data = [$attribute => $value];

        $this->json('POST', '/audits/plans', $data);
        $this->seeStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->seeHeader('Content-Type', 'application/json');
    }
}