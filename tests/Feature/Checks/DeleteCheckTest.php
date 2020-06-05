<?php

use App\Checklist;
use App\Audit;
use App\AuditState;
use App\Check;
use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Ramsey\Uuid\Uuid;

class DeleteCheckTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * @var \App\Check
     */
    protected $check;

    public function setUp()
    {
        parent::setUp();
        $checklist = $this->makeFakeChecklist(['with_description']);
        $checklist->save();
        $this->company->directory->entry($checklist)->save();
        $section = $this->makeFakeSection();
        $section->save();
        $checklist->entry($section)->save();
        $scoringScheme = $this->makeFakeScoringScheme();
        $this->company->scoringSchemes()->save($scoringScheme );
        $score = $this->makeFakeScore();
        $scoringScheme->scores()->save($score);
        $checkpoint = $this->makeFakeCheckpoint();
        $checkpoint->scoringScheme()->associate($scoringScheme);
        $valueScheme = $this->makeFakeValueScheme();
        $valueScheme->save();
        $checkpoint->evaluationScheme()->associate($valueScheme);
        $checkpoint->save();
        $section->entry($checkpoint)->save();

        $audit = factory(Audit::class)->make();
        $audit->company()->associate($this->company);
        $audit->user()->associate($this->user);	
        $audit->checklist()->associate($checklist);
        $audit->state()->associate(AuditState::where('name', '=', 'draft')->first());
        $audit->save();

        $this->check = new Check();
        $this->check->id = Uuid::uuid4()->toString();
        $this->check->auditId = $audit->id;
        $this->check->checklistId = $checklist->id;
        $this->check->checkpointId = $checkpoint->id;
        $this->check->sectionId = $section->id;
        $this->check->valueSchemeId = $valueScheme->id;
        $this->check->scoringSchemeId = $scoringScheme->id;
        $this->check->objectType = "checkpoint";
        $this->check->objectId = $checkpoint->id;
        $this->check->save();
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
     * @param boolean $checklistKey
     * @param int $statusCode
     * @dataProvider provideInvalidAccessData
     */
    public function testInvalidAccess($userKey, $statusCode)
    {
        if ($userKey != null) {
            $user = $this->getUser($userKey);
            $this->actingAs($user);
        }
        
        $uri = '/audits/checks/' . $this->check->id;
        
        $this->json('DELETE', $uri);
        $this->seeStatusCode($statusCode);
        $this->seeHeader('Content-Type', 'application/json');
    }

    public function provideValidEntities()
    {
        return [
            [self::$ADMIN],
            [self::$SUPER_ADMIN],
        ];
    }

    /**
     * @param $userKey
     * @param $attribute
     * @param $dbAttribute
     * @param $value
     * @dataProvider provideValidEntities
     */
    
    public function testValidEntities($userKey) {
    	if ($userKey != null) {
            $user = $this->getUser($userKey);
            $this->actingAs($user);
        }
        
        $uri = '/audits/checks/' . $this->check->id;
        
        $this->json('DELETE', $uri);
        $this->seeStatusCode(Response::HTTP_NO_CONTENT);
    }
}
