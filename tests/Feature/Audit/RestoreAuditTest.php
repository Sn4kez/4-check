<?php

use App\Audit;
use App\AuditState;
use App\Checklist;
use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Ramsey\Uuid\Uuid;

class RestoreAuditsTest extends TestCase
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
        $this->audit->isArchived = 1;
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
     * @param string $userIdKey
     * @param int $statusCode
     * @dataProvider provideInvalidAccessData
     */
    public function testInvalidAccess($userKey, $auditIdKey, $statusCode)
    {
    	if(!is_null($userKey)) {
    		$user = $this->getUser($userKey);
        	$this->actingAs($user);
    	}

        if ($auditIdKey === 'audit') {
            $uri = '/audits/restore/' . $this->audit->id;
        } else {
            $uri = '/audits/restore/' . Uuid::uuid4()->toString();
        }

        $this->json('PATCH', $uri);
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
     * @param $userIdKey
     * @param $expectEmpty
     * @dataProvider provideValidAccessData
     */
    public function testValidAccess($userKey)
    {
        $user = $this->getUser($userKey);
        $this->actingAs($user);

        $uri = '/audits/restore/' . $this->audit->id;

        $this->json('PATCH', $uri);
        $this->seeStatusCode(Response::HTTP_NO_CONTENT);
    }
}

