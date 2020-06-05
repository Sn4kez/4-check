<?php

use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Ramsey\Uuid\Uuid;

class UpdateScoringSchemeTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * @var \App\ScoringScheme
     */
    protected $scheme;

    public function setUp()
    {
        parent::setUp();
        $this->scheme = $this->makeFakeScoringScheme();
        $this->company->scoringSchemes()->save($this->scheme);
    }

    public function provideInvalidAccessData()
    {
        return [
            [null, 'random', Response::HTTP_UNAUTHORIZED],
            [null, 'scheme', Response::HTTP_UNAUTHORIZED],
            [self::$USER, 'random', Response::HTTP_NOT_FOUND],
            [self::$USER, 'scheme', Response::HTTP_FORBIDDEN],
            [self::$ADMIN, 'random', Response::HTTP_NOT_FOUND],
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
            $uri = '/scoringschemes/' . $this->scheme->id;
        } else {
            $uri = '/scoringschemes/' . Uuid::uuid4()->toString();
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
        $this->json('PATCH', '/scoringschemes/' . $this->scheme->id, $data);
        $this->seeStatusCode(Response::HTTP_NO_CONTENT);
        $this->seeInDatabase('scoring_schemes', [
            'id' => $this->scheme->id,
            $dbAttribute => $value,
        ]);
    }

    public function provideInvalidEntities()
    {
        return [
            [self::$ADMIN, 'name', null],
            [self::$ADMIN, 'name', 123],
            [self::$ADMIN, 'name', str_repeat('Long', 32) . 'Name'],
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
        $this->json('PATCH', '/scoringschemes/' . $this->scheme->id, $data);
        $this->seeStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->seeHeader('Content-Type', 'application/json');
    }
}
