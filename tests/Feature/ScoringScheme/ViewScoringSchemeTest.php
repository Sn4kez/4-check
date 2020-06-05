<?php

use App\ScoringScheme;
use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Ramsey\Uuid\Uuid;

class ViewScoringSchemeTest extends TestCase
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
     * @param boolean $schemeKey
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
        $this->json('GET', '/scoringschemes/' . $this->scheme->id);
        $this->seeStatusCode(Response::HTTP_OK);
        $this->seeHeader('Content-Type', 'application/json');
        $this->seeJsonStructure([
            'data' => [
                'id',
                'name',
                'scopeId',
                'scopeType',
                'isListed',
            ],
        ])->seeJsonContains([
            'id' => $this->scheme->id,
            'name' => $this->scheme->name,
            'isListed' => (string) $this->scheme->isListed,
            'scopeId' => $this->company->id,
            'scopeType' => ScoringScheme::SCOPE_TYPE_COMPANY,
        ]);
    }
}
