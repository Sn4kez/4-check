<?php

use App\AuditState;
use App\Check;
use App\Country;
use App\DirectoryEntry;
use App\LocationState;
use App\LocationType;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Ramsey\Uuid\Uuid;
use App\LocationCheck;
use Carbon\Carbon;

class ViewAnalyticsTest extends TestCase {
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

        $scoringScheme = $this->makeFakeScoringScheme();
        $this->company->scoringSchemes()->save($scoringScheme);

        for ($i = 0; $i < self::COUNT_SCORING_TESTS_CHECK; $i++) {
            $score = $this->makeFakeScore();
            $score->value = round(rand(1, 10), 0);
            $scoringScheme->scores()->save($score);

            $this->scores[] = $score;
        }

        $checkpoint = $this->makeFakeCheckpoint();
        $checkpoint->scoringScheme()->associate($scoringScheme);
        $valueScheme = $this->makeFakeValueScheme();
        $valueScheme->save();
        $checkpoint->evaluationScheme()->associate($valueScheme);
        $checkpoint->save();

        $this->audit = $this->makeFakeAudit();
        $this->audit->id = \Ramsey\Uuid\Uuid::uuid4()->toString();
        $this->audit->company()->associate($this->company);
        $this->audit->user()->associate($this->user);
        $this->audit->checklist()->associate($this->checklist);
        $this->audit->state()->associate($this->auditStateFinished);
        $this->audit->executionAt = Carbon::createFromDate(2018, 10, 23);
        $this->audit->save();

        for ($i = 0; $i < self::COUNT_SCORING_TESTS_CHECK; $i++) {
            $this->scoringCheck = new Check();
            $this->scoringCheck->id = Uuid::uuid4()->toString();
            $this->scoringCheck->auditId = $this->audit->id;
            $this->scoringCheck->checklistId = $this->checklist->id;
            $this->scoringCheck->checkpointId = $checkpoint->id;
            $this->scoringCheck->sectionId = $section->id;
            $this->scoringCheck->valueSchemeId = $valueScheme->id;
            $this->scoringCheck->scoringSchemeId = $scoringScheme->id;
            $this->scoringCheck->objectType = Check::VALUE_TYPE_CHECKPOINT;
            $this->scoringCheck->objectId = $checkpoint->id;
            $this->scoringCheck->valueId = $this->scores[$i]->id;
            $this->scoringCheck->rating = round(rand(10, 20), 0) / 10;
            $this->scoringCheck->save();
        }

        $this->pictureCheck = new Check();
        $this->pictureCheck->id = Uuid::uuid4()->toString();
        $this->pictureCheck->auditId = $this->audit->id;
        $this->pictureCheck->checklistId = $this->checklist->id;
        $this->pictureCheck->checkpointId = $checkpoint->id;
        $this->pictureCheck->sectionId = $section->id;
        $this->pictureCheck->objectType = 'picture';
        $this->pictureCheck->objectId = $checkpoint->id;
        $this->pictureCheck->save();

        $this->locationCheck = new Check();
        $this->locationCheck->id = Uuid::uuid4()->toString();
        $this->locationCheck->auditId = $this->audit->id;
        $this->locationCheck->checklistId = $this->checklist->id;
        $this->locationCheck->checkpointId = $checkpoint->id;
        $this->locationCheck->sectionId = $section->id;
        $this->locationCheck->objectType = Check::VALUE_TYPE_LOCATION;
        $this->locationCheck->objectId = $this->childLocation->id;
        $this->locationCheck->save();

        $this->locationCheckSpecific = new LocationCheck();
        $this->locationCheckSpecific->checkId = $this->locationCheck->id;
        $this->locationCheckSpecific->locationId = $this->childLocation->id;
        $this->locationCheckSpecific->save();
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

    private function uploadImageToCheck($checkId) {
        $data = [
            'value' => $this->base64Content
        ];

        $this->json('PATCH', '/audits/checks/' . $checkId, $data);
    }

    public function provideValidAccessData() {
        return [
            [
                false,
                true,
                '1540159200',
                '1540504800',
                false,
                true
            ],
            [
                false,
                false,
                strtotime('2017-10-01'),
                strtotime('2017-10-30'),
                true,
                false
            ],
            [
                false,
                false,
                '2017-10-01',
                '2017-10-30',
                true,
                false
            ],
            [
                false,
                false,
                strtotime('2018-10-01'),
                strtotime('2018-10-31'),
                true,
                true
            ],
            [
                false,
                false,
                '2018-10-01',
                '2018-10-31',
                true,
                true
            ],
            [
                true,
                false,
                null,
                null,
                false,
                true
            ],
            [
                false,
                true,
                null,
                null,
                false,
                true
            ],

        ];
    }

    /**
     * @param $filterForDirectory
     * @param $filterForChecklist
     * @param $start
     * @param $end
     * @param $filterLocation
     * @param $minOneEntryFound
     * @dataProvider provideValidAccessData
     */
    public function testValidAccess($filterForDirectory, $filterForChecklist, $start, $end, $filterLocation, $minOneEntryFound) {
        $this->actingAs($this->getUser(self::$USER));

        $directoryId = null;
        $checklistId = null;
        $location = null;

        if ($filterForDirectory === true) {
            $directoryId = $this->homeDirectory->id;
        }

        if ($filterForChecklist === true) {
            $checklistId = $this->checklist->id;
        }

        if ($filterLocation === true) {
            $location = $this->topLocation->id;
        }

        $this->uploadImageToCheck($this->pictureCheck->id);

        $this->json('GET', '/analytics', [
            'directory' => $directoryId,
            'checklist' => $checklistId,
            'start' => $start,
            'end' => $end,
            'location' => $location
        ]);

        $this->seeJsonStructure([
            'charts',
            'audits',
            'media'
        ]);

        $data = (array)$this->getDataFromLastResponse('audits');

        if ($minOneEntryFound === true) {
            $this->assertTrue(count($data) > 0);
        } else {
            $this->assertTrue(count($data) === 0);
        }
    }
}
