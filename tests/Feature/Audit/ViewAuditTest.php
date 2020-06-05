<?php

use App\Audit;
use App\AuditState;
use App\Checklist;
use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Ramsey\Uuid\Uuid;

class ViewAuditTest extends TestCase
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

        $subdir = $this->makeFakeDirectory();
        $subdir->name = 'layer 2';
        $subdir->save();

        $subdir3 = $this->makeFakeDirectory();
        $subdir3->name = 'layer 3';
        $subdir3->save();

        $subdir3->entry($this->checklist)->save();
        $subdir->entry($subdir3)->save();

        $this->company->directory->entry($subdir)->save();

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
    public function _testInvalidAccess($userKey, $auditKey, $statusCode)
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
        $this->json('GET', $uri);
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
        $this->json('GET', '/audits/' . $this->audit->id);
        $this->seeStatusCode(Response::HTTP_OK);
        $this->seeHeader('Content-Type', 'application/json');
        $this->seeJsonStructure([
            'data' => [
                'id',
                'user',
                'company',
                'checklist',
                'checklistPath',
                'executionDue'
            ],
        ])->seeJsonNotNull([
            'data' => [
                'id',
                'checklistPath'
            ]
        ])->seeJsonContains([
        	'checklist' => $this->audit->checklist->id,
            'user' => $this->audit->user->id,
            'company' => $this->audit->company->id,
            'executionDue' => $this->audit->executionDue,
        ]);
    }
}
