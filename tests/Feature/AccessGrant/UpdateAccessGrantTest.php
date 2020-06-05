<?php

use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Ramsey\Uuid\Uuid;

class UpdateAccessGrantTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * @var \App\AccessGrant $grant
     */
    protected $grant;

    public function setUp()
    {
        parent::setUp();
        $this->grant = $this->makeFakeAccessGrant();
        $this->grant->subject()->associate($this->user);
        $this->grant->object()->associate($this->company->directory);
        $this->grant->save();
    }

    public function provideInvalidAccessData()
    {
        return [
            [null, 'random', Response::HTTP_UNAUTHORIZED],
            [null, 'grant', Response::HTTP_UNAUTHORIZED],
            [self::$USER, 'random', Response::HTTP_NOT_FOUND],
            [self::$ADMIN, 'random', Response::HTTP_NOT_FOUND],
            [self::$OTHER_ADMIN, 'grant', Response::HTTP_FORBIDDEN],
        ];
    }

    /**
     * @param string $userKey
     * @param string $grantKey
     * @param int $statusCode
     * @dataProvider provideInvalidAccessData
     */
    public function testInvalidAccess($userKey, $grantKey, $statusCode)
    {
        if ($userKey != null) {
            $user = $this->getUser($userKey);
            $this->actingAs($user);
        }
        if ($grantKey === 'grant') {
            $uri = '/grants/' . $this->grant->id;
        } else {
            $uri = '/grants/' . Uuid::uuid4()->toString();
        }
        $this->json('PATCH', $uri);
        $this->seeStatusCode($statusCode);
        $this->seeHeader('Content-Type', 'application/json');
    }

    public function provideValidEntities()
    {
        return [
            [self::$ADMIN, 'view', 'view', true],
            [self::$SUPER_ADMIN, 'view', 'view', true],
            [self::$ADMIN, 'index', 'index', true],
            [self::$SUPER_ADMIN, 'index', 'index', true],
            [self::$ADMIN, 'update', 'update', true],
            [self::$SUPER_ADMIN, 'update', 'update', true],
            [self::$ADMIN, 'delete', 'delete', true],
            [self::$SUPER_ADMIN, 'delete', 'delete', true],
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
        $this->json('PATCH', '/grants/' . $this->grant->id, $data);
        $this->seeStatusCode(Response::HTTP_NO_CONTENT);
        $this->seeInDatabase('access_grants', [
            'id' => $this->grant->id,
            $dbAttribute => $value,
        ]);
    }

    public function provideInvalidEntities()
    {
        return [
            [self::$ADMIN, 'view', null],
            [self::$ADMIN, 'view', 123],
            [self::$ADMIN, 'index', null],
            [self::$ADMIN, 'index', 123],
            [self::$ADMIN, 'update', null],
            [self::$ADMIN, 'update', 123],
            [self::$ADMIN, 'delete', null],
            [self::$ADMIN, 'delete', 123],
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
        $this->json('PATCH', '/grants/' . $this->grant->id, $data);
        $this->seeStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->seeHeader('Content-Type', 'application/json');
    }
}
