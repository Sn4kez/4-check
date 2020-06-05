<?php

use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Ramsey\Uuid\Uuid;

class ListSectionCheckpointsTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * @var \App\Section
     */
    protected $section;

    /**
     * @var \App\Section
     */
    protected $otherSection;

    /**
     * @var array
     */
    protected $checkpoints;

    public function setUp()
    {
        parent::setUp();
        $checklist = $this->makeFakeChecklist(['with_description']);
        $checklist->save();
        $this->company->directory->entry($checklist)->save();
        $otherChecklist = $this->makeFakeChecklist(['with_description']);
        $otherChecklist->save();
        $this->otherCompany->directory->entry($otherChecklist)->save();
        $this->section = $this->makeFakeSection();
        $this->section->save();
        $checklist->entry($this->section)->save();
        $this->otherSection = $this->makeFakeSection();
        $this->otherSection->save();
        $otherChecklist->entry($this->otherSection)->save();
        $this->checkpoints = [
            $this->createFakeCheckpoint(),
            $this->createFakeCheckpoint(),
        ];
        foreach ($this->checkpoints as $checkpoint) {
            $checkpoint->save();
            $this->section->entry($checkpoint)->save();
        }
    }

    public function provideInvalidAccessData()
    {
        return [
            [null, 'random', Response::HTTP_UNAUTHORIZED],
            [null, 'section', Response::HTTP_UNAUTHORIZED],
            [self::$USER, 'random', Response::HTTP_NOT_FOUND],
            [self::$USER, 'section', Response::HTTP_FORBIDDEN],
            [self::$OTHER_ADMIN, 'section', Response::HTTP_FORBIDDEN],
        ];
    }

    /**
     * @param string $userKey
     * @param string $sectionKey
     * @param int $statusCode
     * @dataProvider provideInvalidAccessData
     */
    public function testInvalidAccess($userKey, $sectionKey, $statusCode)
    {
        if ($userKey != null) {
            $user = $this->getUser($userKey);
            $this->actingAs($user);
        }
        if ($sectionKey === 'section') {
            $uri = '/sections/' . $this->section->id . '/checkpoints';
        } else {
            $uri = '/sections/' . Uuid::uuid4()->toString() . '/checkpoints';
        }
        $this->json('GET', $uri);
        $this->seeStatusCode($statusCode);
        $this->seeHeader('Content-Type', 'application/json');
    }

    public function provideValidAccessData()
    {
        return [
            [self::$ADMIN, 'section', false],
            [self::$SUPER_ADMIN, 'section', false],
            [self::$OTHER_ADMIN, 'otherSection', true],
        ];
    }

    /**
     * @param $userKey
     * @param $sectionKey
     * @param $expectEmpty
     * @dataProvider provideValidAccessData
     */
    public function testValidAccess($userKey, $sectionKey, $expectEmpty)
    {
        $user = $this->getUser($userKey);
        $this->actingAs($user);
        if ($sectionKey === 'section') {
            $uri = '/sections/' . $this->section->id . '/checkpoints';
        } else {
            $uri = '/sections/' . $this->otherSection->id . '/checkpoints';
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
                        'prompt',
                        'mandatory',
                        'factor',
                        'index',
                        'scoringSchemeId',
                        'evaluationSchemeId',
                    ],
                    [
                        'id',
                        'prompt',
                        'mandatory',
                        'factor',
                        'index',
                        'scoringSchemeId',
                        'evaluationSchemeId',
                    ],
                ],
            ]);
            foreach ($this->checkpoints as $checkpoint) {
                $this->seeJsonContains([
                    'id' => $checkpoint->id,
                    'prompt' => $checkpoint->prompt,
                    'mandatory' => $checkpoint->mandatory,
                    'factor' => $checkpoint->factor,
                    'index' => $checkpoint->index,
                ]);
            }
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
