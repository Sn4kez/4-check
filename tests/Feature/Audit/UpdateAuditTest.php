<?php

use App\Audit;
use App\AuditState;
use App\Checklist;
use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Ramsey\Uuid\Uuid;

class UpdateAuditTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * @var \App\Checklist
     */
    protected $checklist;

    /**
     * @var \App\Audit
     */
    protected $audit;

    public function setUp()
    {
        parent::setUp();
        $this->checklist = $this->makeFakeChecklist(['with_description']);
        $this->checklist->save();
        $this->company->directory->entry($this->checklist)->save();

        $this->audit = factory(Audit::class)->make();
        $this->audit->company()->associate($this->company);
        $this->audit->user()->associate($this->user);	
        $this->audit->checklist()->associate($this->checklist);
        $this->audit->state()->associate(AuditState::where('name', '=', 'draft')->first());
        $this->audit->save();
    }

    public function provideInvalidAccessData()
    {
        return [
            [null, 'random', Response::HTTP_UNAUTHORIZED],
            [null, 'audit', Response::HTTP_UNAUTHORIZED],
            [self::$USER, 'random', Response::HTTP_NOT_FOUND],
            [self::$USER, 'audit', Response::HTTP_FORBIDDEN],
            [self::$ADMIN, 'random', Response::HTTP_NOT_FOUND],
            [self::$OTHER_ADMIN, 'audit', Response::HTTP_FORBIDDEN],
        ];
    }

    /**
     * @param string $userKey
     * @param boolean $checklistKey
     * @param int $statusCode
     * @dataProvider provideInvalidAccessData
     */
    public function testInvalidAccess($userKey, $auditKey, $statusCode)
    {
        if ($userKey != null) {
            $user = $this->getUser($userKey);
            $this->actingAs($user);
        }
        if ($auditKey === 'audit') {
            $uri = '/audits/' . $this->audit->id;
        } else {
            $uri = '/audits/' . Uuid::uuid4()->toString();
        }
        $this->json('PATCH', $uri);
        $this->seeStatusCode($statusCode);
        $this->seeHeader('Content-Type', 'application/json');
    }

    public function provideValidEntities()
    {
        return [
            [self::$ADMIN, 'executionDue', 'executionDue', "2018-12-31"],
            [self::$SUPER_ADMIN, 'executionDue', 'executionDue', "2018-12-31"],
        ];
    }

    /**
     * @param $userKey
     * @param $attribute
     * @param $dbAttribute
     * @param $value
     * @dataProvider provideValidEntities
     */
    
    public function testValidEntities($userKey, $attribute, $dbAttribute, $value) {
        $user = $this->getUser($userKey);
        $this->actingAs($user);

        $data = [$attribute => $value];
 
        $this->json('PATCH', '/audits/' . $this->audit->id, $data);
        $this->seeStatusCode(Response::HTTP_NO_CONTENT);
        $this->seeInDatabase('audits', [
            'id' => $this->audit->id,
            $dbAttribute => $value,
        ]);
    }

    public function provideInvalidEntities()
    {
        return [
            [self::$ADMIN, 'executionDue', null],
            [self::$ADMIN, 'executionDue', 123],
            [self::$ADMIN, 'executionDue', str_repeat('123', 128)],
            [self::$ADMIN, 'executionDue', -1],
            [self::$ADMIN, 'executionDue', 'abc'],
        ];
    }

    /**
     * @param $userKey
     * @param $attribute
     * @param $value
     * @dataProvider provideInvalidEntities
     */
    public function testInvalidEntities($userKey, $attribute, $value)
    {
    	$user = $this->getUser($userKey);
        $this->actingAs($user);
        $data = [$attribute => $value];
        $this->json('PATCH', '/audits/' . $this->audit->id, $data);
        $this->seeStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->seeHeader('Content-Type', 'application/json');
    }
}
