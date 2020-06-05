<?php

use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Ramsey\Uuid\Uuid;

class ListScoreConditionTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * @var \App\ValueScheme
     */
    protected $valueScheme;

    /**
     * @var \App\Checklist
     */
    protected $otherValueScheme;

    /**
     * @var array
     */
    protected $scoreConditions;

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
        $score = $this->makeFakeScore();
        $scoringScheme->scores()->save($score);
        $checkpoint = $this->makeFakeCheckpoint();
        $checkpoint->scoringScheme()->associate($scoringScheme);
        $this->valueScheme = $this->makeFakeValueScheme();
        $this->valueScheme->save();
        $checkpoint->evaluationScheme()->associate($this->valueScheme);
        $checkpoint->save();
        $section->entry($checkpoint)->save();
        $otherCheckpoint = $this->makeFakeCheckpoint();
        $otherCheckpoint->scoringScheme()->associate($scoringScheme);
        $this->otherValueScheme = $this->makeFakeValueScheme();
        $this->otherValueScheme->save();
        $otherCheckpoint->evaluationScheme()->associate($this->otherValueScheme);
        $otherCheckpoint->save();
        $section->entry($otherCheckpoint)->save();
        $this->scoreConditions = [
            $this->makeFakeScoreCondition(),
            $this->makeFakeScoreCondition(),
        ];
        foreach ($this->scoreConditions as $condition) {
            $condition->score()->associate($score);
        }
        $this->valueScheme->conditions()->saveMany($this->scoreConditions);
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
        $this->json('GET', $uri);
        $this->seeStatusCode($statusCode);
        $this->seeHeader('Content-Type', 'application/json');
    }

    public function provideValidAccessData()
    {
        return [
            [self::$ADMIN, 'scheme', false],
            [self::$ADMIN, 'otherScheme', true],
            [self::$SUPER_ADMIN, 'scheme', false],
            [self::$SUPER_ADMIN, 'otherScheme', true],
        ];
    }

    /**
     * @param $userKey
     * @param $schemeKey
     * @param $expectEmpty
     * @dataProvider provideValidAccessData
     */
    public function testValidAccess($userKey, $schemeKey, $expectEmpty)
    {
        $user = $this->getUser($userKey);
        $this->actingAs($user);
        if ($schemeKey === 'scheme') {
            $uri = '/valueschemes/' . $this->valueScheme->id . '/conditions';
        } else {
            $uri = '/valueschemes/' . $this->otherValueScheme->id . '/conditions';
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
                        'from',
                        'to',
                        'scoreId',
                    ],
                    [
                        'id',
                        'from',
                        'to',
                        'scoreId',
                    ],
                ],
            ]);
            foreach ($this->scoreConditions as $condition) {
                $this->seeJsonContains([
                    'id' => $condition->id,
                    'from' => $condition->from,
                    'to' => $condition->to,
                    'scoreId' => $condition->score->id,
                ]);
            }
        }
    }
}
