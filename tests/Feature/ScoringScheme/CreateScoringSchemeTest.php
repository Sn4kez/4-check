<?php

use App\ScoringScheme;
use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Ramsey\Uuid\Uuid;

class CreateScoringSchemeTest extends TestCase {
    use DatabaseMigrations;

    public function provideInvalidAccessData() {
        return [
            [null, 'random', Response::HTTP_UNAUTHORIZED],
            [null, 'company', Response::HTTP_UNAUTHORIZED],
            [self::$USER, 'random', Response::HTTP_NOT_FOUND],
            [self::$USER, 'company', Response::HTTP_FORBIDDEN],
            [self::$OTHER_ADMIN, 'company', Response::HTTP_FORBIDDEN],
        ];
    }

    /**
     * @param string $userKey
     * @param string $companyKey
     * @param int $statusCode
     * @dataProvider provideInvalidAccessData
     */
    public function testInvalidAccess($userKey, $companyKey, $statusCode) {
        if ($userKey != null) {
            $user = $this->getUser($userKey);
            $this->actingAs($user);
        }
        if ($companyKey === 'company') {
            $uri = '/companies/' . $this->company->id . '/scoringschemes';
        } else {
            $uri = '/companies/' . Uuid::uuid4()->toString() . '/scoringschemes';
        }
        $this->json('POST', $uri);
        $this->seeStatusCode($statusCode);
        $this->seeHeader('Content-Type', 'application/json');
    }

    public function provideValidAccessData() {
        return [
            [self::$ADMIN],
            [self::$SUPER_ADMIN],
        ];
    }

    /**
     * @param string $userKey
     * @dataProvider provideValidAccessData
     */
    public function testValidAccess($userKey) {
        $user = $this->getUser($userKey);
        $this->actingAs($user);

        $scheme = factory(ScoringScheme::class)->make();
        $scheme->scope()->associate($this->company);

        $url = '/companies/' . $this->company->id . '/scoringschemes';
        $this->json('POST', $url, [
            'name' => $scheme->name,
        ]);

        $this->seeStatusCode(Response::HTTP_CREATED);
        $this->seeHeader('Content-Type', 'application/json');

        $this->seeJsonStructure([
            'data' => [
                'id',
                'name',
                'scopeId',
                'scopeType',
            ],
        ])->seeJsonNotNull([
            'data' => [
                'id',
            ],
        ])->seeJsonContains([
            'name' => $scheme->name,
            'scopeId' => $this->company->id,
            'scopeType' => ScoringScheme::SCOPE_TYPE_COMPANY,
        ]);
        $this->seeInDatabase('scoring_schemes', [
            'name' => $scheme->name,
            'scopeId' => $this->company->id,
            'scopeType' => ScoringScheme::SCOPE_TYPE_COMPANY,
        ]);

        /**
         * All users shall see
         */
        $this->actingAs($this->getUser(self::$USER));
        $r = $this->json('GET', '/companies/' . $this->company->id . '/scoringschemes');
        $data = json_decode($r->response->getContent(), true);
        $this->assertTrue(1 === count($data['data']));
    }

    public function provideInvalidEntities() {
        return [
            ['name', null],
            ['name', 123],
            ['name', str_repeat('Long', 32) . 'Name'],
        ];
    }

    /**
     * @param string $attribute
     * @param string $value
     * @dataProvider provideInvalidEntities
     */
    public function testInvalidEntities($attribute, $value) {
        $scheme = factory(ScoringScheme::class)->make();
        $data = array_merge([
            'name' => $scheme->name,
        ], [$attribute => $value]);
        $this->actingAs($this->admin);
        $url = '/companies/' . $this->company->id . '/scoringschemes';
        $this->json('POST', $url, $data);
        $this->seeStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->seeHeader('Content-Type', 'application/json');
    }
}
