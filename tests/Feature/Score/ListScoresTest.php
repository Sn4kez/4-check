<?php

use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Ramsey\Uuid\Uuid;

class ListScoresTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * @var \App\Score
     */
    protected $scores;

    /**
     * @var \App\ScoringScheme
     */
    protected $scoringScheme;

    /**
     * @var \App\ScoringScheme
     */
    protected $otherScoringScheme;

    public function setUp()
    {
        parent::setUp();
        $this->scoringScheme = $this->makeFakeScoringScheme();
        $this->company->scoringSchemes()->save($this->scoringScheme);
        $this->otherScoringScheme = $this->makeFakeScoringScheme();
        $this->otherCompany->scoringSchemes()->save($this->otherScoringScheme);
        $this->scores = [
            $this->makeFakeScore(),
            $this->makeFakeScore(),
        ];
        $this->scoringScheme->scores()->saveMany($this->scores);
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
            $uri = '/scoringschemes/' . $this->scoringScheme->id . '/scores';
        } else {
            $uri = '/scoringschemes/' . Uuid::uuid4()->toString() . '/scores';
        }
        $this->json('GET', $uri);
        $this->seeStatusCode($statusCode);
        $this->seeHeader('Content-Type', 'application/json');
    }

    public function provideValidAccessData()
    {
        return [
            [self::$ADMIN, 'scheme', false],
            [self::$SUPER_ADMIN, 'scheme', false],
            [self::$OTHER_ADMIN, 'otherScheme', true],
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
            $uri = '/scoringschemes/' . $this->scoringScheme->id . '/scores';
        } else {
            $uri = '/scoringschemes/' . $this->otherScoringScheme->id . '/scores';
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
                        'name',
                        'value',
                        'schemeId',
                    ],
                    [
                        'id',
                        'name',
                        'value',
                        'schemeId',
                    ],
                ],
            ]);
            foreach ($this->scores as $score) {
                $this->seeJsonContains([
                    'id' => $score->id,
                    'schemeId' => $this->scoringScheme->id,
                    'name' => $score->name,
                    'value' => $score->value,
                ]);
            }
        }
    }
}
