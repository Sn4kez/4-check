<?php

use App\Checklist;
use App\Audit;
use App\AuditState;
use App\Check;
use App\ValueScheme;
use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Ramsey\Uuid\Uuid;
use App\Media;

class UpdateCheckTest extends TestCase {
    use DatabaseMigrations;

    /**
     * @var \App\Check
     */
    protected $check;

    /**
     * @var \App\Check
     */
    protected $integrationCheck;
    private $scoringNotification;

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
        $this->score = $this->makeFakeScore();
        $scoringScheme->scores()->save($this->score);
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

        $this->integrationCheck = new Check();
        $this->integrationCheck->id = Uuid::uuid4()->toString();
        $this->integrationCheck->auditId = $audit->id;
        $this->integrationCheck->checklistId = $checklist->id;
        $this->integrationCheck->checkpointId = $checkpoint->id;
        $this->integrationCheck->sectionId = $section->id;
        $this->integrationCheck->objectType = 'picture';
        $this->integrationCheck->objectId = $checkpoint->id;
        $this->integrationCheck->save();

        $this->check->save();

        $this->scoringNotification = new \App\ScoreNotification();
        $this->scoringNotification->id = Uuid::uuid4()->toString();
        $this->scoringNotification->scoreId = $this->score->id;
        $this->scoringNotification->checklistId = $checklist->id;
        $this->scoringNotification->objectType = 'user';
        $this->scoringNotification->objectId = $this->user->id;
        $this->scoringNotification->save();
    }

    public function testIntegration() {
        $user = $this->getUser(self::$SUPER_ADMIN);
        $this->actingAs($user);

        $base64Content = base64_encode(file_get_contents(sprintf("%s/files/logo.jpg", dirname(__FILE__))));

        $data = [
            'value' => $base64Content
        ];

        $this->json('PATCH', 'audits/checks/' . $this->check->id, [
            'scoreId' => $this->score->id,
            'objectType' => 'checkpoint',
            'value' => 1
        ]);

        /*$this->seeInDatabase('notifications', [
            'title' => \App\Notification::TITLE_CHECK_NOTIFICATION
        ]);*/

        /**
         * PATCH an image to a check
         */
        $dataRaw = [
            "value" => file_get_contents(sprintf("%s/files/logo.jpg.txt", dirname(__FILE__)))
        ];

        $this->json('PATCH', '/audits/checks/' . $this->integrationCheck->id, $dataRaw);
        $this->seeStatusCode(Response::HTTP_NO_CONTENT);
        $this->seeInDatabase('checks', [
            'id' => $this->integrationCheck->id
        ]);

        /**
         * PATCH an image to a check
         */
        $this->json('PATCH', '/audits/checks/' . $this->integrationCheck->id, $data);
        $this->seeStatusCode(Response::HTTP_NO_CONTENT);
        $this->seeInDatabase('checks', [
            'id' => $this->integrationCheck->id
        ]);

        /**
         * Check if the image was saved at the check
         */
        $this->json('GET', '/audits/checks/' . $this->integrationCheck->id);
        $this->seeJsonNotNull([
            'data' => [
                'value' => [
                    'id',
                    'checkId',
                    'value'
                ]
            ]
        ]);

        /**
         * PATCH - REMOVE image
         */
        $this->json('PATCH', '/audits/checks/' . $this->integrationCheck->id, ['value' => null]);
        $this->seeStatusCode(Response::HTTP_NO_CONTENT);
        $this->seeInDatabase('checks', [
            'id' => $this->integrationCheck->id
        ]);

        /**
         * Check if the image was saved at the check
         */
        $this->json('GET', '/audits/checks/' . $this->integrationCheck->id);
        $data = $this->getDataFromLastResponse('data');
        $this->assertTrue($data['value']['value'] === null);
    }

    public function provideInvalidAccessData() {
        return [
            [
                null,
                Response::HTTP_UNAUTHORIZED
            ],
            [
                self::$USER,
                Response::HTTP_FORBIDDEN
            ],
            [
                self::$OTHER_ADMIN,
                Response::HTTP_FORBIDDEN
            ],
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

        $this->json('PATCH', $uri);
        $this->seeStatusCode($statusCode);
        $this->seeHeader('Content-Type', 'application/json');
    }

    public function provideValidEntities() {
        return [
            [
                self::$ADMIN,
                'executionDate',
                'executionDate',
                "2018-12-31"
            ],
            [
                self::$SUPER_ADMIN,
                'executionDate',
                'executionDate',
                "2018-12-31"
            ],
        ];
    }

    /**
     * @param $userKey
     * @param $attribute
     * @param $dbAttribute
     * @param $value
     * @dataProvider provideValidEntities
     */

    public function _testValidEntities($userKey, $attribute, $dbAttribute, $value) {
        $user = $this->getUser($userKey);
        $this->actingAs($user);

        $data = [
            $attribute => $value,
            'value' => 'test',
            'sectionId' => $this->check->sectionId

        ];

        $this->json('PATCH', '/audits/checks/' . $this->check->id, $data);
        $this->seeStatusCode(Response::HTTP_NO_CONTENT);
        $this->seeInDatabase('checks', [
            'id' => $this->check->id,
            $dbAttribute => $value,
        ]);
    }
}
