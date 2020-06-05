<?php

use App\Audit;
use App\AuditState;
use App\Checklist;
use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Ramsey\Uuid\Uuid;

class ArchiveAuditSetTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * @var \App\Checklist
     */
    protected $checklist;

    /**
     * @var \App\Audit
     */
    protected $audits;

    public function setUp()
    {
        parent::setUp();
        $this->checklist = $this->makeFakeChecklist(['with_description']);
        $this->checklist->save();
        $this->company->directory->entry($this->checklist)->save();



        $audit1 = factory(Audit::class)->make();
        $audit1->company()->associate($this->company);
        $audit1->user()->associate($this->user);	
        $audit1->checklist()->associate($this->checklist);
        $audit1->state()->associate(AuditState::where('name', '=', 'draft')->first());
        $audit1->save();

        $audit2 = factory(Audit::class)->make();
        $audit2->company()->associate($this->company);
        $audit2->user()->associate($this->user);	
        $audit2->checklist()->associate($this->checklist);
        $audit2->state()->associate(AuditState::where('name', '=', 'draft')->first());
        $audit2->save();

        $this->audits = [
        	$audit1->id,
        	$audit2->id
        ];
    }

    public function provideInvalidAccessData()
    {
        return [
            [null, Response::HTTP_UNAUTHORIZED],
            [self::$USER, Response::HTTP_FORBIDDEN],
            [self::$OTHER_ADMIN, Response::HTTP_FORBIDDEN],
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
    	if(!is_null($userKey)) {
    		$user = $this->getUser($userKey);
        	$this->actingAs($user);
    	}

        $uri = '/audits/archive';

        $this->json('PATCH', $uri, [
        	'items' => $this->audits
        ]);
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

        $uri = '/audits/archive';

        $this->json('PATCH', $uri, [
        	'items' => $this->audits
        ]);
        $this->seeStatusCode(Response::HTTP_NO_CONTENT);
    }
}

