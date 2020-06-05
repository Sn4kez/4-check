<?php

use App\Audit;
use App\AuditState;
use App\Checklist;
use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Ramsey\Uuid\Uuid;

class ListAuditTest extends TestCase
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
        	$audit1,
        	$audit2
        ];
    }

    public function provideInvalidAccessData()
    {
        return [
            [null, 'random', Response::HTTP_UNAUTHORIZED],
            [null, 'company', Response::HTTP_UNAUTHORIZED],
        ];
    }

    /**
     * @param string $userKey
     * @param string $userIdKey
     * @param int $statusCode
     * @dataProvider provideInvalidAccessData
     */
    public function testInvalidAccess($userKey, $companyIdKey, $statusCode)
    {

        if ($companyIdKey === 'company') {
            $uri = '/audits/company/' . $this->user->company->id;
        } else {
            $uri = '/audits/company/' . Uuid::uuid4()->toString();
        }

        $this->json('GET', $uri);
        $this->seeStatusCode($statusCode);
        $this->seeHeader('Content-Type', 'application/json');
    }

    public function provideValidAccessData()
    {
        return [
            [self::$USER, 'company', false],
            [self::$ADMIN, 'company', false],
            [self::$SUPER_ADMIN, 'company', false],
            [self::$OTHER_ADMIN, 'otherCompany', true],
        ];
    }

    /**
     * @param $userKey
     * @param $userIdKey
     * @param $expectEmpty
     * @dataProvider provideValidAccessData
     */
    public function testValidAccess($userKey, $companyKey, $expectEmpty)
    {
        $user = $this->getUser($userKey);
        $this->actingAs($user);

        if ($companyKey === 'company') {
            $uri = '/audits/company/' . $this->user->company->id;
        } else {
            $uri = '/audits/company/' . $this->otherUser->company->id;
        }

        $this->json('GET', $uri);
        $this->seeStatusCode(Response::HTTP_OK);
        $this->seeHeader('Content-Type', 'application/json');
        if ($expectEmpty) {
            $this->seeJsonStructure([
                'data' => [],
            ]);
        } else {
            $this->seeJsonStructure([
                'data' => [
                    [
                        'id',
                        'company',
                        'user',
                        'checklist',
                        'checklistName',
                        'checklistDescription',
                        'executionDue'
                    ],
                    [
                        'id',
                        'company',
                        'user',
                        'checklist',
                        'checklistName',
                        'checklistDescription',
                        'executionDue'
                    ],
                ],
            ]);
            foreach ($this->audits as $audit) {
                $this->seeJsonContains([
                    'company' => $audit->company->id,
                    'user' => $audit->user->id,
                    'checklist' => $audit->checklist->id,
                    'checklistName' => $audit->checklist->name,
                    'checklistDescription' => $audit->checklist->description,
                    'executionDue' => $audit->executionDue
                ]);
            }
        }
    }
        
    
}
