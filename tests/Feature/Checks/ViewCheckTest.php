<?php

use App\Checklist;
use App\Audit;
use App\AuditState;
use App\Check;
use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Ramsey\Uuid\Uuid;

class ViewCheckTest extends TestCase {
    use DatabaseMigrations;

    /**
     * @var \App\Check
     */
    protected $check;
    private $pictureCheck;

    public function setUp() {
        parent::setUp();
        $checklist = $this->makeFakeChecklist(['with_description']);
        $checklist->save();
        $this->company->directory->entry($checklist)->save();
        $section = $this->makeFakeSection();
        $section->save();
        $checklist->entry($section)->save();
        $scoringScheme = $this->makeFakeScoringScheme();
        $this->company->scoringSchemes()->save($scoringScheme);
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

        $this->pictureCheck = new Check();
        $this->pictureCheck->id = Uuid::uuid4()->toString();
        $this->pictureCheck->auditId = $audit->id;
        $this->pictureCheck->checklistId = $checklist->id;
        $this->pictureCheck->checkpointId = $checkpoint->id;
        $this->pictureCheck->sectionId = $section->id;
        $this->pictureCheck->objectType = 'picture';
        $this->pictureCheck->objectId = $checkpoint->id;
        $this->pictureCheck->save();
    }

    public function provideInvalidAccessData() {
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
    public function testInvalidAccess($userKey, $statusCode) {
        if ($userKey != null) {
            $user = $this->getUser($userKey);
            $this->actingAs($user);
        }

        $uri = '/audits/checks/' . $this->check->id;

        $this->json('GET', $uri);
        $this->seeStatusCode($statusCode);
        $this->seeHeader('Content-Type', 'application/json');
    }

    public function provideValidEntities() {
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
        $user = $this->getUser($userKey);
        $this->actingAs($user);

        $this->json('GET', '/audits/checks/' . $this->check->id);
        $this->seeStatusCode(Response::HTTP_OK);
        $this->seeInDatabase('checks', [
            'id' => $this->check->id,
            'auditId' => $this->check->auditId,
            'checklistId' => $this->check->checklistId,
            'sectionId' => $this->check->sectionId,
            'checkpointId' => $this->check->checkpointId,
            'valueSchemeId' => $this->check->valueSchemeId,
            'scoringSchemeId' => $this->check->scoringSchemeId,
            'objectType' => $this->check->objectType,
            'objectId' => $this->check->objectId
        ]);
    }

    public function testPicture() {
        $this->actingAs($this->getUser(self::$ADMIN));

        $base64Content = base64_encode(file_get_contents(sprintf("%s/files/logo.jpg", dirname(__FILE__))));

        $data = [
            'value' => $base64Content
        ];

        /**
         * PATCH an image to a check
         */
        $this->json('PATCH', '/audits/checks/' . $this->pictureCheck->id, $data);
        $this->seeStatusCode(Response::HTTP_NO_CONTENT);
        $this->seeInDatabase('checks', [
            'id' => $this->pictureCheck->id
        ]);

        $r=$this->json('GET', '/audits/checks/' . $this->pictureCheck->id);
        $this->seeStatusCode(Response::HTTP_OK);
        $this->seeInDatabase('checks', [
            'id' => $this->check->id,
            'auditId' => $this->check->auditId,
            'checklistId' => $this->check->checklistId,
            'sectionId' => $this->check->sectionId,
            'checkpointId' => $this->check->checkpointId,
            'valueSchemeId' => $this->check->valueSchemeId,
            'scoringSchemeId' => $this->check->scoringSchemeId,
            'objectType' => $this->check->objectType,
            'objectId' => $this->check->objectId
        ]);
        $this->seeJsonStructure([
            'data' => [
                'base64'
            ]
        ]);
        $this->seeJsonNotNull([
            'data' => [
                'base64'
            ]
        ]);
    }
}
