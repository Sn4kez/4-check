<?php

use App\Audit;
use App\AuditState;
use App\Checklist;
use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Ramsey\Uuid\Uuid;

class FilterAuditByPeriodTest extends TestCase
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
        $audit1->executionDue = '2018-07-13';
        $audit1->save();

        $audit2 = factory(Audit::class)->make();
        $audit2->company()->associate($this->company);
        $audit2->user()->associate($this->otherUser);	
        $audit2->checklist()->associate($this->checklist);
        $audit2->state()->associate(AuditState::where('name', '=', 'finished')->first());
        $audit2->executionDue = '2018-07-13';
        $audit2->save();

        $this->audit = $audit1;
    }

    public function provideValidAccessData()
    {
        return [
            [self::$USER],
            [self::$SUPER_ADMIN],
        ];
    }

    /**
     * @param $userKey
     * @param $attribute
     * @param $value
     * @param $taskArrayId
     * @dataProvider provideValidAccessData
     */
    public function testValidAccess($userKey)
    {
        $user = $this->getUser($userKey);
        $this->actingAs($user);

        $start = '2018-07-01';
        $end = '2018-07-20';

        $uri = '/audits/company/' . $this->user->company->id . '?start=' . $start . '&end=' . $end;

        $this->json('GET', $uri);
        $this->seeStatusCode(Response::HTTP_OK);
        $this->seeHeader('Content-Type', 'application/json');
        
        $this->seeJsonStructure([
            'data' => [
                [
                    'id',
                    'company',
                    'user',
                    'checklist',
                    'executionDue'
                ],
            ],
        ]);
            
        $this->seeJsonContains([
            'company' => $this->audit->company->id,
            'user' => $this->audit->user->id,
            'checklist' => $this->audit->checklist->id,
            'executionDue' => $this->audit->executionDue
        ]);
    }
}
