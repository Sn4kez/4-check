<?php

use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Ramsey\Uuid\Uuid;

class ListScopeScoringSchemesTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * @var array
     */
    protected $schemes;

    public function setUp()
    {
        parent::setUp();
        $this->schemes = [
            $this->makeFakeScoringScheme(),
            $this->makeFakeScoringScheme(),
        ];
        $this->company->scoringSchemes()->saveMany($this->schemes);
    }

    public function provideInvalidAccessData()
    {
        return [
            [null, 'random', Response::HTTP_UNAUTHORIZED],
            [null, 'company', Response::HTTP_UNAUTHORIZED],
            [self::$USER, 'random', Response::HTTP_NOT_FOUND],
        ];
    }

    /**
     * @param string $userKey
     * @param string $scopeKey
     * @param int $statusCode
     * @dataProvider provideInvalidAccessData
     */
    public function testInvalidAccess($userKey, $scopeKey, $statusCode)
    {
        if ($userKey != null) {
            $user = $this->getUser($userKey);
            $this->actingAs($user);
        }
        if ($scopeKey === 'company') {
            $uri = '/companies/' . $this->company->id . '/scoringschemes';
        } else {
            $uri = '/companies/' . Uuid::uuid4()->toString() . '/scoringschemes';
        }
        $this->json('GET', $uri);
        $this->seeStatusCode($statusCode);
        $this->seeHeader('Content-Type', 'application/json');
    }

    public function provideValidAccessData()
    {
        return [
            [self::$ADMIN, 'company', false],
            [self::$SUPER_ADMIN, 'company', false],
            [self::$OTHER_ADMIN, 'otherCompany', true],
        ];
    }

    /**
     * @param $userKey
     * @param $scopeKey
     * @param $expectEmpty
     * @dataProvider provideValidAccessData
     */
    public function testValidAccess($userKey, $scopeKey, $expectEmpty)
    {
        $user = $this->getUser($userKey);
        $this->actingAs($user);
        if ($scopeKey === 'company') {
            $uri = '/companies/' . $this->company->id . '/scoringschemes';
        } else {
            $uri = '/companies/' . $this->otherCompany->id . '/scoringschemes';
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
                        'scopeId',
                        'scopeType',
                    ],
                    [
                        'id',
                        'name',
                        'scopeId',
                        'scopeType',
                    ],
                ],
            ]);
            foreach ($this->schemes as $scheme) {
                $this->seeJsonContains([
                    'id' => $scheme->id,
                    'name' => $scheme->name,
                    'scopeId' => $scheme->scope->id,
                    'scopeType' => $scheme->scopeType,
                ]);
            }
        }
    }
}
