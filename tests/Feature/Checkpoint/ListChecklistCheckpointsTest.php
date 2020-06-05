<?php

use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Ramsey\Uuid\Uuid;

class ListChecklistCheckpointsTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * @var \App\Checklist
     */
    protected $checklist;

    /**
     * @var \App\Checklist
     */
    protected $otherChecklist;

    /**
     * @var array
     */
    protected $checkpoints;

    public function setUp()
    {
        parent::setUp();
        $this->checklist = $this->makeFakeChecklist(['with_description']);
        $this->checklist->save();
        $this->company->directory->entry($this->checklist)->save();
        $this->otherChecklist = $this->makeFakeChecklist(['with_description']);
        $this->otherChecklist->save();
        $this->otherCompany->directory->entry($this->otherChecklist)->save();
        $this->checkpoints = [
            $this->createFakeCheckpoint(),
            $this->createFakeCheckpoint(),
        ];
        foreach ($this->checkpoints as $checkpoint) {
            $checkpoint->save();
            $this->checklist->entry($checkpoint)->save();
        }
    }

    public function provideInvalidAccessData()
    {
        return [
            [null, 'random', Response::HTTP_UNAUTHORIZED],
            [null, 'checklist', Response::HTTP_UNAUTHORIZED],
            [self::$USER, 'random', Response::HTTP_NOT_FOUND],
            [self::$USER, 'checklist', Response::HTTP_FORBIDDEN],
            [self::$OTHER_ADMIN, 'checklist', Response::HTTP_FORBIDDEN],
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
        if ($sectionKey === 'checklist') {
            $uri = '/checklists/' . $this->checklist->id . '/checkpoints';
        } else {
            $uri = '/checklists/' . Uuid::uuid4()->toString() . '/checkpoints';
        }
        $this->json('GET', $uri);
        $this->seeStatusCode($statusCode);
        $this->seeHeader('Content-Type', 'application/json');
    }

    public function provideValidAccessData()
    {
        return [
            [self::$ADMIN, 'checklist', false],
            [self::$SUPER_ADMIN, 'checklist', false],
            [self::$OTHER_ADMIN, 'otherChecklist', true],
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
        if ($sectionKey === 'checklist') {
            $uri = '/checklists/' . $this->checklist->id . '/checkpoints';
        } else {
            $uri = '/checklists/' . $this->otherChecklist->id . '/checkpoints';
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
