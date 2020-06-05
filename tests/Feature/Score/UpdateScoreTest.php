<?php

use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Ramsey\Uuid\Uuid;

class UpdateScoreTest extends TestCase
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
     * @param boolean $scoreKey
     * @param int $statusCode
     * @dataProvider provideInvalidAccessData
     */
    public function testInvalidAccess($userKey, $scoreKey, $statusCode)
    {
        if ($userKey != null) {
            $user = $this->getUser($userKey);
            $this->actingAs($user);
        }
        if ($scoreKey === 'score') {
            $uri = '/scores/' . $this->score->id;
        } else {
            $uri = '/scores/' . Uuid::uuid4()->toString();
        }
        $this->json('PATCH', $uri);
        $this->seeStatusCode($statusCode);
        $this->seeHeader('Content-Type', 'application/json');
    }

    public function provideValidEntities()
    {
        return [
            [self::$ADMIN, 'name', 'name', 'name'],
            [self::$SUPER_ADMIN, 'name', 'name', 'name'],
            [self::$ADMIN, 'value', 'value', 12.3],
            [self::$SUPER_ADMIN, 'value', 'value', 12.3],
        ];
    }

    /**
     * @param $userKey
     * @param $attribute
     * @param $dbAttribute
     * @param $value
     * @dataProvider provideValidEntities
     */
    public function testValidEntities($userKey, $attribute, $dbAttribute, $value)
    {
        $user = $this->getUser($userKey);
        $this->actingAs($user);
        $data = [$attribute => $value];
        $this->json('PATCH', '/scores/' . $this->score->id, $data);
        $this->seeStatusCode(Response::HTTP_NO_CONTENT);
        $this->seeInDatabase('scores', [
            'id' => $this->score->id,
            $dbAttribute => $value,
        ]);
    }

    public function provideInvalidEntities()
    {
        return [
            [self::$ADMIN, 'name', null],
            [self::$ADMIN, 'name', 123],
            [self::$ADMIN, 'name', str_repeat('Long', 32) . 'Name'],
            [self::$ADMIN, 'value', null],
            [self::$ADMIN, 'value', 'string'],
        ];
    }

    /**
     * @param $userKey
     * @param $attribute
     * @param $value
     * @dataProvider provideInvalidEntities
     */
    public function testInvalidEntities($userKey, $attribute, $value)
    {
        $user = $this->getUser($userKey);
        $this->actingAs($user);
        $data = [$attribute => $value];
        $this->json('PATCH', '/scores/' . $this->score->id, $data);
        $this->seeStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->seeHeader('Content-Type', 'application/json');
    }
}
