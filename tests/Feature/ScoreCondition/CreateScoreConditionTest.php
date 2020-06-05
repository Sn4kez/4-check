<?php

use App\ScoreCondition;
use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Ramsey\Uuid\Uuid;

class CreateScoreConditionTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * @var \App\ValueScheme
     */
    protected $valueScheme;

    /**
     * @var \App\Score
     */
    protected $score;

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
        $this->company->scoringSchemes()->save($scoringScheme);
        $this->score = $this->makeFakeScore();
        $scoringScheme->scores()->save($this->score);
        $checkpoint = $this->makeFakeCheckpoint();
        $checkpoint->scoringScheme()->associate($scoringScheme);
        $this->valueScheme = $this->makeFakeValueScheme();
        $this->valueScheme->save();
        $checkpoint->evaluationScheme()->associate($this->valueScheme);
        $checkpoint->save();
        $section->entry($checkpoint)->save();
    }

    public function provideInvalidAccessData()
    {
        return [
            [null, 'random', Response::HTTP_UNAUTHORIZED],
            [null, 'scheme', Response::HTTP_UNAUTHORIZED],
            [self::$USER, 'random', Response::HTTP_NOT_FOUND],
            [self::$USER, 'scheme', Response::HTTP_FORBIDDEN],
            [self::$OTHER_ADMIN, 'scheme', Response::HTTP_FORBIDDEN],
        ];
    }

    /**
     * @param string $userKey
     * @param string $schemeKey
     * @param int $statusCode
     * @dataProvider provideInvalidAccessData
     */
    public function testInvalidAccess($userKey, $schemeKey, $statusCode)
    {
        if ($userKey != null) {
            $user = $this->getUser($userKey);
            $this->actingAs($user);
        }
        if ($schemeKey === 'scheme') {
            $uri = '/valueschemes/' . $this->valueScheme->id . '/conditions';
        } else {
            $uri = '/valueschemes/' . Uuid::uuid4()->toString() . '/conditions';
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
        /** @var \App\ScoreCondition $condition1 */
        $condition1 = factory(ScoreCondition::class)->make();
        $condition1->score()->associate($this->score);
        /** @var \App\ScoreCondition $condition2 */
        $condition2 = factory(ScoreCondition::class)->make();
        $condition2->score()->associate($this->score);
        $uri = '/valueschemes/' . $this->valueScheme->id . '/conditions';
        $this->json('POST', $uri, [
            'from' => $condition1->from,
            'to' => $condition1->to,
            'scoreId' => $condition1->score->id,
        ]);
        $this->seeStatusCode(Response::HTTP_CREATED);
        $this->seeHeader('Content-Type', 'application/json');
        $this->seeJsonStructure([
            'data' => [
                'id',
                'from',
                'to',
                'scoreId',
            ],
        ])->seeJsonNotNull([
            'data' => [
                'id',
            ],
        ])->seeJsonContains([
            'from' => $condition1->from,
            'to' => $condition1->to,
            'scoreId' => $condition1->score->id,
        ]);
        $this->json('POST', $uri, [
            'from' => $condition2->from,
            'to' => $condition2->to,
            'scoreId' => $condition2->score->id,
        ]);
        $this->seeStatusCode(Response::HTTP_CREATED);
        $this->seeHeader('Content-Type', 'application/json');
        $this->seeJsonStructure([
            'data' => [
                'id',
                'from',
                'to',
                'scoreId',
            ],
        ])->seeJsonNotNull([
            'data' => [
                'id',
            ],
        ])->seeJsonContains([
            'from' => $condition2->from,
            'to' => $condition2->to,
            'scoreId' => $condition2->score->id,
        ]);
        $this->seeInDatabase('score_conditions', [
            'valueSchemeId' => $this->valueScheme->id,
            'from' => $condition1->from,
            'to' => $condition1->to,
            'scoreId' => $condition1->score->id,
        ])->seeInDatabase('score_conditions', [
            'valueSchemeId' => $this->valueScheme->id,
            'from' => $condition2->from,
            'to' => $condition2->to,
            'scoreId' => $condition2->score->id,
        ]);
        $this->assertCount(2, $this->valueScheme->conditions);
    }

    public function provideInvalidEntities()
    {
        return [
            ['from', 'string'],
            ['to', 'string'],
        ];
    }

    /**
     * @param string $attribute
     * @param string $value
     * @dataProvider provideInvalidEntities
     */
    public function testInvalidEntities($attribute, $value)
    {
        $condition = factory(ScoreCondition::class)->make();
        $condition->score()->associate($this->score);
        $data = array_merge([
            'from' => $condition->from,
            'to' => $condition->to,
            'scoreId' => $condition->score->id,
        ], [$attribute => $value]);
        $this->actingAs($this->admin);
        $uri = '/valueschemes/' . $this->valueScheme->id . '/conditions';
        $this->json('POST', $uri, $data);
        $this->seeStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->seeHeader('Content-Type', 'application/json');
    }
}
