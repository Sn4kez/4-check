<?php

use App\Country;
use App\Score;
use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Ramsey\Uuid\Uuid;

class CreateScoreTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * @var \App\ScoringScheme
     */
    protected $scoringScheme;

    public function setUp()
    {
        parent::setUp();
        $this->scoringScheme = $this->makeFakeScoringScheme();
        $this->company->scoringSchemes()->save($this->scoringScheme );
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
        $score1 = factory(Score::class)->make();
        $score2 = factory(Score::class)->make();
        $uri = '/scoringschemes/' . $this->scoringScheme->id . '/scores';
        $this->json('POST', $uri, [
            'name' => $score1->name,
            'value' => $score1->value,
            'color' => $score1->color,
        ]);
        $this->seeStatusCode(Response::HTTP_CREATED);
        $this->seeHeader('Content-Type', 'application/json');
        $this->seeJsonStructure([
            'data' => [
                'id',
                'name',
                'value',
                'schemeId',
            ],
        ])->seeJsonNotNull([
            'data' => [
                'id',
            ],
        ])->seeJsonContains([
            'name' => $score1->name,
            'value' => $score1->value,
        ]);
        $this->json('POST', $uri, [
            'name' => $score2->name,
            'value' => $score2->value,
            'color' => $score2->color,
        ]);
        $this->seeStatusCode(Response::HTTP_CREATED);
        $this->seeHeader('Content-Type', 'application/json');
        $this->seeJsonStructure([
            'data' => [
                'id',
                'name',
                'value',
                'schemeId',
            ],
        ])->seeJsonNotNull([
            'data' => [
                'id',
            ],
        ])->seeJsonContains([
            'name' => $score2->name,
            'value' => $score2->value,
        ]);
        $this->seeInDatabase('scores', [
            'scoringSchemeId' => $this->scoringScheme->id,
            'name' => $score1->name,
            'value' => $score1->value,
        ])->seeInDatabase('scores', [
            'scoringSchemeId' => $this->scoringScheme->id,
            'name' => $score2->name,
            'value' => $score2->value,
        ]);
        $this->assertCount(2, $this->scoringScheme->scores);
    }

    public function provideInvalidEntities()
    {
        return [
            ['name', null],
            ['name', 123],
            ['name', str_repeat('Long', 32) . 'Name'],
            ['value', null],
            ['value', 'string'],
            ['value', -1],
            ['value', 101],
        ];
    }

    /**
     * @param string $attribute
     * @param string $value
     * @dataProvider provideInvalidEntities
     */
    public function testInvalidEntities($attribute, $value)
    {
        $score = factory(Score::class)->make();
        $data = array_merge([
            'name' => $score->name,
            'value' => $score->value,
        ], [$attribute => $value]);
        $this->actingAs($this->admin);
        $uri = '/scoringschemes/' . $this->scoringScheme->id . '/scores';
        $this->json('POST', $uri, $data);
        $this->seeStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->seeHeader('Content-Type', 'application/json');
    }
}
