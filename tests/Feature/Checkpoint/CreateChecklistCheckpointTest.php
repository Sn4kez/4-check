<?php

use App\Checkpoint;
use App\ChoiceScheme;
use App\ScoreCondition;
use App\ValueScheme;
use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Ramsey\Uuid\Uuid;

class CreateChecklistCheckpointTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * @var \App\Checklist
     */
    protected $checklist;

    /**
     * @var \App\ScoringScheme
     */
    protected $scoringScheme;

    /**
     * @var \App\Score
     */
    protected $score;

    public function setUp()
    {
        parent::setUp();
        $this->checklist = $this->makeFakeChecklist(['with_description']);
        $this->checklist->save();
        $this->company->directory->entry($this->checklist)->save();
        $this->scoringScheme = $this->makeFakeScoringScheme();
        $this->company->scoringSchemes()->save($this->scoringScheme );
        $this->score = $this->makeFakeScore();
        $this->scoringScheme->scores()->save($this->score);
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
        $this->json('POST', $uri);
        $this->seeStatusCode($statusCode);
        $this->seeHeader('Content-Type', 'application/json');
    }

    public function provideValidAccessData()
    {
        return [
            [self::$ADMIN],
            [self::$SUPER_ADMIN],
        ];
    }

    /**
     * @param string $userKey
     * @dataProvider provideValidAccessData
     */
    public function testValidAccess($userKey)
    {
        $user = $this->getUser($userKey);
        $this->actingAs($user);
        /** @var Checkpoint $checkpoint1 */
        $checkpoint1 = factory(Checkpoint::class)->make();
        /** @var ChoiceScheme $evaluationScheme1 */
        $evaluationScheme1 = factory(ChoiceScheme::class)->make();
        $checkpoint1->evaluationScheme()->associate($evaluationScheme1);
        $checkpoint1->scoringScheme()->associate($this->scoringScheme);
        /** @var Checkpoint $checkpoint2 */
        $checkpoint2 = factory(Checkpoint::class)->make();
        /** @var ValueScheme $evaluationScheme2 */
        $evaluationScheme2 = factory(ValueScheme::class)->make();
        /** @var ScoreCondition $scoreCondition */
        $scoreCondition = factory(ScoreCondition::class)->make();
        $scoreCondition->scheme()->associate($evaluationScheme2);
        $scoreCondition->score()->associate($this->score);
        $checkpoint2->evaluationScheme()->associate($evaluationScheme2);
        $checkpoint2->scoringScheme()->associate($this->scoringScheme);
        $uri = '/checklists/' . $this->checklist->id . '/checkpoints';
        $this->json('POST', $uri, [
            'prompt' => $checkpoint1->prompt,
            'mandatory' => $checkpoint1->mandatory,
            'factor' => $checkpoint1->factor,
            'index' => $checkpoint1->index,
            'scoringSchemeId' => $checkpoint1->scoringScheme->id,
            'evaluationScheme' => [
                'type' => 'choice',
                'data' => [
                    'multiselect' => $evaluationScheme1->multiselect,
                ],
            ]
        ]);
        $this->seeStatusCode(Response::HTTP_CREATED);
        $this->seeHeader('Content-Type', 'application/json');
        $this->seeJsonStructure([
            'data' => [
                'id',
                'prompt',
                'mandatory',
                'factor',
                'index',
                'scoringSchemeId',
                'evaluationSchemeId',
            ],
        ])->seeJsonNotNull([
            'data' => [
                'id',
                'scoringSchemeId',
                'evaluationSchemeId',
            ],
        ])->seeJsonContains([
            'prompt' => $checkpoint1->prompt,
            'mandatory' => $checkpoint1->mandatory,
            'factor' => $checkpoint1->factor,
            'index' => $checkpoint1->index,
        ]);
        $this->json('POST', $uri, [
            'prompt' => $checkpoint2->prompt,
            'mandatory' => $checkpoint2->mandatory,
            'factor' => $checkpoint2->factor,
            'index' => $checkpoint2->index,
            'scoringSchemeId' => $checkpoint2->scoringScheme->id,
            'evaluationScheme' => [
                'type' => 'value',
                'data' => [
                    'unit' => $evaluationScheme2->unit,
                    'scoreConditions' => [
                        [
                            'from' => $scoreCondition->from,
                            'to' => $scoreCondition->to,
                            'scoreId' => $scoreCondition->score->id,
                        ]
                    ]
                ],
            ]
        ]);
        $this->seeStatusCode(Response::HTTP_CREATED);
        $this->seeHeader('Content-Type', 'application/json');
        $this->seeJsonStructure([
            'data' => [
                'id',
                'prompt',
                'mandatory',
                'factor',
                'index',
                'scoringSchemeId',
                'evaluationSchemeId',
            ],
        ])->seeJsonNotNull([
            'data' => [
                'id',
                'scoringSchemeId',
                'evaluationSchemeId',
            ],
        ])->seeJsonContains([
            'prompt' => $checkpoint2->prompt,
            'mandatory' => $checkpoint2->mandatory,
            'factor' => $checkpoint2->factor,
            'index' => $checkpoint2->index,
        ]);
        $this->seeInDatabase('checkpoints', [
            'prompt' => $checkpoint1->prompt,
            'mandatory' => $checkpoint1->mandatory,
            'factor' => $checkpoint1->factor,
            'index' => $checkpoint1->index,
        ])->seeInDatabase('checkpoints', [
            'prompt' => $checkpoint2->prompt,
            'mandatory' => $checkpoint2->mandatory,
            'factor' => $checkpoint2->factor,
            'index' => $checkpoint2->index,
        ]);
        $this->assertCount(2, $this->checklist->entries);
    }

    public function provideInvalidEntities()
    {
        return [
            ['prompt', null],
            ['prompt', 123],
            ['prompt', str_repeat('Long', 32) . 'Prompt'],
            ['mandatory', 123],
            ['factor', 'string'],
            ['index', null],
            ['index', 123],
            ['index', str_repeat('Long', 32) . 'Index'],
        ];
    }

    /**
     * @param string $attribute
     * @param string $value
     * @dataProvider provideInvalidEntities
     */
    public function testInvalidEntities($attribute, $value)
    {
        /** @var Checkpoint $checkpoint */
        $checkpoint = factory(Checkpoint::class)->make();
        /** @var ChoiceScheme $evaluationScheme1 */
        $evaluationScheme1 = factory(ChoiceScheme::class)->make();
        $checkpoint->evaluationScheme()->associate($evaluationScheme1);
        $checkpoint->scoringScheme()->associate($this->scoringScheme);
        $data = array_merge([
            'prompt' => $checkpoint->prompt,
            'mandatory' => $checkpoint->mandatory,
            'factor' => $checkpoint->factor,
            'index' => $checkpoint->index,
            'scoringSchemeId' => $checkpoint->scoringScheme->id,
            'evaluationScheme' => [
                'type' => 'choice',
                'data' => [
                    'multiselect' => $evaluationScheme1->multiselect,
                ],
            ]
        ], [$attribute => $value]);
        $this->actingAs($this->admin);
        $uri = '/checklists/' . $this->checklist->id . '/checkpoints';
        $this->json('POST', $uri, $data);
        $this->seeStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->seeHeader('Content-Type', 'application/json');
    }
}
