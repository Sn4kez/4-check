<?php

use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Ramsey\Uuid\Uuid;

class ViewScoreTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * @var \App\Score
     */
    protected $score;

    /**
     * @var \App\ScoringScheme
     */
    protected $scoringScheme;

    public function setUp()
    {
        parent::setUp();
        $this->scoringScheme = $this->makeFakeScoringScheme();
        $this->company->scoringSchemes()->save($this->scoringScheme );
        $this->score = $this->makeFakeScore();
        $this->scoringScheme->scores()->save($this->score);
    }

    public function provideInvalidAccessData()
    {
        return [
            [null, 'random', Response::HTTP_UNAUTHORIZED],
            [null, 'score', Response::HTTP_UNAUTHORIZED],
            [self::$USER, 'random', Response::HTTP_NOT_FOUND],
            [self::$USER, 'score', Response::HTTP_FORBIDDEN],
            [self::$ADMIN, 'random', Response::HTTP_NOT_FOUND],
            [self::$OTHER_ADMIN, 'score', Response::HTTP_FORBIDDEN],
        ];
    }

    /**
     * @param string $userKey
     * @param boolean $storeKey
     * @param int $statusCode
     * @dataProvider provideInvalidAccessData
     */
    public function testInvalidAccess($userKey, $storeKey, $statusCode)
    {
        if ($userKey != null) {
            $user = $this->getUser($userKey);
            $this->actingAs($user);
        }
        if ($storeKey === 'score') {
            $uri = '/scores/' . $this->score->id;
        } else {
            $uri = '/scores/' . Uuid::uuid4()->toString();
        }
        $this->json('GET', $uri);
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
     * @param $userKey
     * @dataProvider provideValidAccessData
     */
    public function testValidAccess($userKey)
    {
        $user = $this->getUser($userKey);
        $this->actingAs($user);
        $this->json('GET', '/scores/' . $this->score->id);
        $this->seeStatusCode(Response::HTTP_OK);
        $this->seeHeader('Content-Type', 'application/json');
        $this->seeJsonStructure([
            'data' => [
                'id',
                'name',
                'value',
                'color',
                'schemeId',
            ],
        ])->seeJsonContains([
            'id' => $this->score->id,
            'schemeId' => $this->scoringScheme->id,
            'name' => $this->score->name,
            'color' => $this->score->color,
            'value' => $this->score->value,
        ]);
    }
}
