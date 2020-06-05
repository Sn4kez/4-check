<?php

use App\Audit;
use App\AuditState;
use App\Checklist;
use App\Check;
use App\LocationType;
use App\LocationState;
use App\Location;
use App\Country;
use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Ramsey\Uuid\Uuid;

class FilterAuditTest extends TestCase
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

    /**
     * @var \App\Location
     */

    protected $location;

    public function setUp()
    {
        parent::setUp();
        $this->checklist = $this->makeFakeChecklist(['with_description']);
        $this->checklist->save();
        $this->company->directory->entry($this->checklist)->save();

        $section = $this->makeFakeSection();
        $section->save();
        $this->checklist->entry($section)->save();
        $scoringScheme = $this->makeFakeScoringScheme();
        $this->company->scoringSchemes()->save($scoringScheme);
        $score = $this->makeFakeScore();
        $scoringScheme->scores()->save($score);
        $checkpoint = $this->createFakeCheckpoint();
        $checkpoint->save();
        $section->entry($checkpoint)->save();

        $audit1 = factory(Audit::class)->make();
        $audit1->company()->associate($this->company);
        $audit1->user()->associate($this->user);	
        $audit1->checklist()->associate($this->checklist);
        $audit1->state()->associate(AuditState::where('name', '=', 'draft')->first());
        $audit1->save();

        $audit2 = factory(Audit::class)->make();
        $audit2->company()->associate($this->company);
        $audit2->user()->associate($this->otherUser);	
        $audit2->checklist()->associate($this->checklist);
        $audit2->state()->associate(AuditState::where('name', '=', 'finished')->first());
        $audit2->save();

        $this->location = $this->makeFakeLocation();
        $this->location->type()->associate(LocationType::where('name', '=', 'building')->first());
        $this->location->state()->associate(LocationState::where('name', '=', 'active')->first());
        $this->location->company()->associate($this->company);
        $this->location->country()->associate(Country::find('de'));
        $this->location->save();
        $locationExtension = $this->makeFakeLocationExtension();
        $locationExtension->location()->associate($this->location);
        $locationExtension->save();
        $this->extension = $checkpoint->extension($locationExtension);
        $this->extension->save();
        $checkpoint->entry($this->extension)->save();

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

    public function provideValidAccessData()
    {
        return [
            [self::$ADMIN, 'state', 'draft', 0],
            [self::$ADMIN, 'state', 'finished', 1],
            [self::$SUPER_ADMIN, 'state', 'draft', 0],
            [self::$SUPER_ADMIN, 'state', 'finished', 1],
            [self::$ADMIN, 'user', 'user', 0],
            [self::$SUPER_ADMIN, 'user', 'user', 0],
            [self::$ADMIN, 'location', 'location', null],
            [self::$SUPER_ADMIN, 'location', 'location', null],
        ];
    }

    /**
     * @param $userKey
     * @param $attribute
     * @param $value
     * @param $taskArrayId
     * @dataProvider provideValidAccessData
     */
    public function testValidAccess($userKey, $attribute, $value, $auditArrayId)
    {
        $user = $this->getUser($userKey);
        $this->actingAs($user);

        if ($attribute == 'state') {
            $value = AuditState::where('name', '=', $value)->first()->id;
        }

        if ($attribute == 'user') {
        	$value = $this->user->id;
        }

        if ($attribute == 'location') {
            $value = $this->location->id;
        }

        $uri = '/audits/company/' . $this->user->company->id . '?' . $attribute . '=' . $value;

        $this->json('GET', $uri);
        $this->seeStatusCode(Response::HTTP_OK);
        $this->seeHeader('Content-Type', 'application/json');
        
        if(!is_null($auditArrayId)) {
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

            $audit = $this->audits[$auditArrayId];
            
            $this->seeJsonContains([
                'company' => $audit->company->id,
                'user' => $audit->user->id,
                'checklist' => $audit->checklist->id,
                'executionDue' => $audit->executionDue
            ]);
        }        
    }

    private function createFakeCheckpoint() {
        $scoringScheme = $this->makeFakeScoringScheme();
        $this->company->scoringSchemes()->save($scoringScheme);
        $score = $this->makeFakeScore();
        $scoringScheme->scores()->save($score);
        $checkpoint = $this->makeFakeCheckpoint();
        $checkpoint->scoringScheme()->associate($scoringScheme);
        $evaluationScheme = $this->makeFakeChoiceScheme();
        $evaluationScheme->save();
        $checkpoint->evaluationScheme()->associate($evaluationScheme);
        return $checkpoint;
    }
}
