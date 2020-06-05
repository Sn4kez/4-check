<?php

use App\Audit;
use App\AuditState;
use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;

class CreateAuditTest extends TestCase
{
    use DatabaseMigrations;

    public function setUp()
    {
        parent::setUp();
        $this->checklist = $this->makeFakeChecklist(['with_description']);
        $this->checklist->save();
        $this->company->directory->entry($this->checklist)->save();
    }

    public function provideInvalidAccessData()
    {
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
    public function testInvalidAccess($userKey, $statusCode)
    {
        $this->json('POST', '/audits');
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
     * @param string $userKey
     * @param $image
     * @dataProvider provideValidAccessData
     */
    public function testValidAccess($userKey)
    {
    	$user = $this->getUser($userKey);
        $this->actingAs($user);

        $audit1 = factory(Audit::class)->make();
        $audit1->company()->associate($this->company);
        $audit1->user()->associate($user);	
        $audit1->checklist()->associate($this->checklist);
        $audit1->state()->associate(AuditState::where('name', '=', 'draft')->first());

        $postData = [
        	'executionDue' => $audit1->executionDue,
        	'checklist' => $audit1->checklist->id,
            'user' => $audit1->user->id,
            'company' => $audit1->company->id,
            'state' => $audit1->state->id,
        ];

        $this->json('POST', '/audits', $postData);
        $this->seeStatusCode(Response::HTTP_CREATED);
        $this->seeHeader('Content-Type', 'application/json');
        $this->seeJsonStructure([
            'data' => [
                'id',
                'user',
                'company',
                'checklist',
                'executionDue',
            ],
        ])->seeJsonNotNull([
            'data' => [
                'id',
            ]
        ])->seeJsonContains([
        	'checklist' => $audit1->checklist->id,
            'user' => $audit1->user->id,
            'company' => $audit1->company->id,
            'executionDue' => $audit1->executionDue,
        ]);
    }

}
