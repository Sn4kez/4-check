<?php

use App\Audit;
use App\AuditState;
use App\Check;
use App\Country;
use App\LocationState;
use App\LocationType;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Ramsey\Uuid\Uuid;
use App\ChoiceCheck;
use App\ValueCheck;
use Illuminate\Http\Response;

class IntegrationTest extends TestCase {
    use DatabaseMigrations;

    const COUNT_SCORING_TESTS_CHECK = 5;

    private $checklist;
    private $audit;
    private $homeDirectory;
    private $auditStateDraft;
    private $auditStateFinished;
    private $auditStateWaiting;
    private $pictureCheck;
    private $base64Content;
    private $choiceCheck;
    private $scoringCheck;
    private $scores;
    private $subDirectory;
    private $subSubDirectory;
    private $topLocation;
    private $childLocation;
    private $locationCheck;
    private $locationCheckSpecific;
    private $scoringScheme;
    private $createdSectionId;
    private $createdQuestionInSectionId;
    private $createdQuestionOuterSectionId;
    private $scoringCheckOuterSection;
    private $scoresIds;
    private $choiceCheckOuterSection;
    /** @var ValueCheck */
    private $valueCheck;
    /** @var int */
    private $createdValueCheckId;
    /** @var Check */
    private $checkValueCheck;

    public function setUp() {
        parent::setUp();

        $this->base64Content = base64_encode(file_get_contents(sprintf("%s/files/logo.jpg", dirname(__FILE__))));

        $this->checklist = $this->makeFakeChecklist();
        $this->checklist->save();

        $this->setupDirectories();
        $this->setupLocations();

        $this->auditStateWaiting = AuditState::where('name', '=', 'awaiting approval')->first();
        $this->auditStateFinished = AuditState::where('name', '=', 'finished')->first();
        $this->auditStateDraft = AuditState::where('name', '=', 'finished')->first();

        $this->pictureCheck = $this->makeFakeCheckExtension();

        $section = $this->makeFakeSection();
        $section->save();

        $this->scoringScheme = $this->makeFakeScoringScheme();
        $this->scoringScheme->id = Uuid::uuid4()->toString();
        $this->company->scoringSchemes()->save($this->scoringScheme);
        $this->scoringScheme->save();

        $i = 0;

        foreach ([0, 100] as $scoreValue) {
            $score = $this->makeFakeScore();
            $score->value = $scoreValue;
            $score->id = Uuid::uuid4()->toString();
            $score->scoringSchemeId = $this->scoringScheme->id;
            $this->scoringScheme->scores()->save($score);
            $score->save();

            $this->scoresIds[$i] = $score->id;
            $i++;
        }
    }

    private function setupLocations() {
        $this->topLocation = $this->makeFakeLocation();
        $this->topLocation->id = Uuid::uuid4()->toString();
        $this->topLocation->type()->associate(LocationType::where('name', '=', 'building')->first());
        $this->topLocation->state()->associate(LocationState::where('name', '=', 'active')->first());
        $this->topLocation->company()->associate($this->company);
        $this->topLocation->country()->associate(Country::find('de'));
        $this->topLocation->save();

        $this->childLocation = $this->makeFakeLocation();
        $this->childLocation->id = Uuid::uuid4()->toString();
        $this->childLocation->parentId = $this->topLocation->id;
        $this->childLocation->type()->associate(LocationType::where('name', '=', 'building')->first());
        $this->childLocation->state()->associate(LocationState::where('name', '=', 'active')->first());
        $this->childLocation->company()->associate($this->company);
        $this->childLocation->country()->associate(Country::find('de'));
        $this->childLocation->save();
    }

    private function setupDirectories() {
        $this->homeDirectory = $this->makeFakeDirectory();
        $this->homeDirectory->id = Uuid::uuid4()->toString();
        $this->homeDirectory->setCreatedAt('2018-10-11');
        $this->homeDirectory->save();

        $this->subDirectory = $this->makeFakeDirectory();
        $this->subDirectory->id = Uuid::uuid4()->toString();
        $this->subDirectory->setCreatedAt('2018-10-12');
        $this->subDirectory->save();

        $this->subSubDirectory = $this->makeFakeDirectory();
        $this->subSubDirectory->id = Uuid::uuid4()->toString();
        $this->subSubDirectory->setCreatedAt('2018-10-13');
        $this->subSubDirectory->save();

        $this->subSubDirectory->entry($this->checklist)->save();
        $this->subDirectory->entry($this->subSubDirectory)->save();
        $this->homeDirectory->entry($this->subDirectory)->save();

        $this->user->company->directory()->save($this->homeDirectory);
    }

    public function testOne() {
        $this->actingAs($this->getUser(self::$ADMIN));

        $this->createSection();
        $this->createQuestionInSection();
        $this->createQuestionOnRoot();
        $this->createValueCheckOnRoot();

        $this->createAudit();
        $this->createChoiceChecks();

        $this->answerCheckValue();
        $this->answerCheckInSection();
        $this->answerCheckOuterSection();

        $this->checkResponse();
    }

    private function createSection() {
        $url = sprintf("/checklists/%s/sections", $this->checklist->id);
        $this->json('POST', $url, [
            'index' => '0',
            'title' => 'testgruppe'
        ]);

        $this->createdSectionId = $this->getDataFromLastResponse('id');
    }

    private function createQuestionInSection() {
        $url = sprintf("/sections/%s/checkpoints", $this->createdSectionId);
        $this->json('POST', $url, [
            'prompt' => 'testfrage',
            'scoringSchemeId' => $this->scoringScheme->id,
            'mandatory' => '0',
            'factor' => '1',
            'index' => '0',
            'evaluationScheme' => [
                'type' => 'choice',
                'data' => [
                    'multiselect' => '0'
                ]
            ]
        ]);

        $this->createdQuestionInSectionId = $this->getDataFromLastResponse('id');
    }

    private function createQuestionOnRoot() {
        $url = sprintf("/checklists/%s/checkpoints", $this->checklist->id);
        $this->json('POST', $url, [
            'prompt' => 'testfrage ohne gruppe',
            'scoringSchemeId' => $this->scoringScheme->id,
            'mandatory' => '0',
            'factor' => '1',
            'index' => '0',
            'evaluationScheme' => [
                'type' => 'choice',
                'data' => [
                    'multiselect' => '0'
                ]
            ]
        ]);

        $this->createdQuestionOuterSectionId = $this->getDataFromLastResponse('id');
    }

    private function createValueCheckOnRoot() {
        $url = sprintf("/checklists/%s/checkpoints", $this->checklist->id);
        $this->json('POST', $url, [
            'prompt' => 'testwertefrage ohne gruppe',
            'scoringSchemeId' => $this->scoringScheme->id,
            'mandatory' => '0',
            'factor' => '2',
            'index' => '1',
            'evaluationScheme' => [
                'type' => 'value',
                'data' => [
                    'unit' => 'C°',
                    'scoreConditions' => [
                        [
                            'id' => null,
                            'to' => 50,
                            'from' => 0,
                            'scoreId' => $this->scoresIds[0]
                        ],
                        [
                            'id' => null,
                            'to' => 100,
                            'from' => 51,
                            'scoreId' => $this->scoresIds[1]
                        ]
                    ]
                ]
            ]
        ]);

        $this->createdValueCheckId = $this->getDataFromLastResponse('id');
    }

    private function createAudit() {
        $this->audit = factory(Audit::class)->make();
        $this->audit->id = Uuid::uuid4()->toString();
        $this->audit->company()->associate($this->company);
        $this->audit->user()->associate($this->user);
        $this->audit->checklist()->associate($this->checklist);
        $this->audit->state()->associate(AuditState::getFinishedStateId());
        $this->audit->save();
    }

    private function answerCheckInSection() {
        $this->scoringCheck = new Check();
        $this->scoringCheck->id = Uuid::uuid4()->toString();
        $this->scoringCheck->auditId = $this->audit->id;
        $this->scoringCheck->checklistId = $this->checklist->id;
        $this->scoringCheck->checkpointId = $this->createdQuestionInSectionId;
        $this->scoringCheck->sectionId = $this->createdSectionId;
        $this->scoringCheck->scoringSchemeId = $this->scoringScheme->id;
        $this->scoringCheck->objectType = Check::VALUE_TYPE_CHECKPOINT;
        $this->scoringCheck->objectId = $this->createdQuestionInSectionId;
        $this->scoringCheck->valueId = $this->choiceCheck->id;
        $this->scoringCheck->save();
    }

    private function answerCheckOuterSection() {
        $this->scoringCheckOuterSection = new Check();
        $this->scoringCheckOuterSection->id = Uuid::uuid4()->toString();
        $this->scoringCheckOuterSection->auditId = $this->audit->id;
        $this->scoringCheckOuterSection->checklistId = $this->checklist->id;
        $this->scoringCheckOuterSection->checkpointId = $this->createdQuestionInSectionId;
        $this->scoringCheckOuterSection->sectionId = null;
        $this->scoringCheckOuterSection->scoringSchemeId = $this->scoringScheme->id;
        $this->scoringCheckOuterSection->objectType = Check::VALUE_TYPE_CHECKPOINT;
        $this->scoringCheckOuterSection->objectId = $this->createdQuestionInSectionId;
        $this->scoringCheckOuterSection->valueId = $this->choiceCheckOuterSection->id;
        $this->scoringCheckOuterSection->save();
    }

    private function answerCheckValue() {
        $this->checkValueCheck = new Check();
        $this->checkValueCheck->id = Uuid::uuid4()->toString();
        $this->checkValueCheck->auditId = $this->audit->id;
        $this->checkValueCheck->checklistId = $this->checklist->id;
        $this->checkValueCheck->checkpointId = $this->createdValueCheckId;
        $this->checkValueCheck->sectionId = null;
        $this->checkValueCheck->scoringSchemeId = $this->scoringScheme->id;
        $this->checkValueCheck->objectType = Check::VALUE_TYPE_CHECKPOINT;
        $this->checkValueCheck->objectId = $this->createdQuestionInSectionId;
        $this->checkValueCheck->valueId = $this->valueCheck->id;
        $this->checkValueCheck->save();
    }

    private function checkResponse() {
        $this->json('GET', '/analytics/', ['checklist' => $this->checklist->id]);
        $this->seeStatusCode(Response::HTTP_OK);
    }

    private function createChoiceChecks() {
        $this->choiceCheck = New ChoiceCheck();
        $this->choiceCheck->id = UUid::uuid4()->toString();
        $this->choiceCheck->checkId = $this->createdQuestionInSectionId;
        $this->choiceCheck->scoreId = $this->scoresIds[0];
        $this->choiceCheck->save();

        $this->choiceCheckOuterSection = New ChoiceCheck();
        $this->choiceCheckOuterSection->id = UUid::uuid4()->toString();
        $this->choiceCheckOuterSection->checkId = $this->createdQuestionOuterSectionId;
        $this->choiceCheckOuterSection->scoreId = $this->scoresIds[1];
        $this->choiceCheckOuterSection->save();

        $this->valueCheck = new ValueCheck();
        $this->valueCheck->id = UUid::uuid4()->toString();
        $this->valueCheck->checkId = $this->createdQuestionOuterSectionId;
        $this->valueCheck->scoreId = $this->scoresIds[1];
        $this->valueCheck->save();
    }
}
