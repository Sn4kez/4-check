<?php

use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Ramsey\Uuid\Uuid;

class ListGroupsTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * @var array
     */
    protected $groups;

    public function setUp()
    {
        parent::setUp();
        $this->groups = [
            $this->makeFakeGroup(),
            $this->makeFakeGroup(),
        ];
        $this->company->groups()->saveMany($this->groups);
        $this->user->groups()->saveMany($this->groups);
    }

    public function provideInvalidAccessData()
    {
        return [
            [null, 'randomCompany', Response::HTTP_UNAUTHORIZED],
            [null, 'company', Response::HTTP_UNAUTHORIZED],
            [self::$USER, 'randomCompany', Response::HTTP_NOT_FOUND],
            [self::$USER, 'company', Response::HTTP_FORBIDDEN],
            [self::$OTHER_ADMIN, 'company', Response::HTTP_FORBIDDEN],
            [null, 'randomUser', Response::HTTP_UNAUTHORIZED],
            [null, 'user', Response::HTTP_UNAUTHORIZED],
            [self::$USER, 'randomUser', Response::HTTP_NOT_FOUND],
            [self::$OTHER_USER, 'user', Response::HTTP_FORBIDDEN],
            [self::$OTHER_ADMIN, 'user', Response::HTTP_FORBIDDEN],
        ];
    }

    /**
     * @param string $userKey
     * @param string $subjectKey
     * @param int $statusCode
     * @dataProvider provideInvalidAccessData
     */
    public function testInvalidAccess($userKey, $subjectKey, $statusCode)
    {
        if ($userKey != null) {
            $user = $this->getUser($userKey);
            $this->actingAs($user);
        }
        if ($subjectKey === 'company') {
            $uri = '/companies/' . $this->company->id . '/groups';
        } else if ($subjectKey === 'randomCompany') {
            $uri = '/companies/' . Uuid::uuid4()->toString() . '/groups';
        } else if ($subjectKey === 'user') {
            $uri = '/users/' . $this->user->id . '/groups';
        } else {
            $uri = '/users/' . Uuid::uuid4()->toString() . '/groups';
        }
        $this->json('GET', $uri);
        $this->seeStatusCode($statusCode);
        $this->seeHeader('Content-Type', 'application/json');
    }

    public function provideValidAccessUserData()
    {
        return [
            [self::$USER, 'user', false],
            [self::$ADMIN, 'user', false],
            [self::$SUPER_ADMIN, 'user', false],
            [self::$OTHER_USER, 'otherUser', true],
            [self::$OTHER_ADMIN, 'otherUser', true],
        ];
    }

    /**
     * @param $userKey
     * @param $subjectKey
     * @param $expectEmpty
     * @dataProvider provideValidAccessUserData
     */
    public function testValidAccessUser($userKey, $subjectKey, $expectEmpty)
    {
        $user = $this->getUser($userKey);
        $this->actingAs($user);
        if ($subjectKey === 'user') {
            $uri = '/users/' . $this->user->id . '/groups';
        } else {
            $uri = '/users/' . $this->otherUser->id . '/groups';
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
                    ],
                    [
                        'id',
                        'name',
                    ],
                ],
            ]);
            foreach ($this->groups as $group) {
                $this->seeJsonContains([
                    'id' => $group->id,
                    'name' => $group->name,
                ]);
            }
        }
    }

    public function provideValidAccessCompanyData()
    {
        return [
            [self::$ADMIN, 'company', false],
            [self::$SUPER_ADMIN, 'company', false],
            [self::$OTHER_ADMIN, 'otherCompany', true],
        ];
    }

    /**
     * @param $userKey
     * @param $subjectKey
     * @param $expectEmpty
     * @dataProvider provideValidAccessCompanyData
     */
    public function testValidAccessCompany($userKey, $subjectKey, $expectEmpty)
    {
        $user = $this->getUser($userKey);
        $this->actingAs($user);
        if ($subjectKey === 'company') {
            $uri = '/companies/' . $this->company->id . '/groups';
        } else {
            $uri = '/companies/' . $this->otherCompany->id . '/groups';
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
                    ],
                    [
                        'id',
                        'name',
                    ],
                ],
            ]);
            foreach ($this->groups as $group) {
                $this->seeJsonContains([
                    'id' => $group->id,
                    'name' => $group->name,
                ]);
            }
        }
    }
}
