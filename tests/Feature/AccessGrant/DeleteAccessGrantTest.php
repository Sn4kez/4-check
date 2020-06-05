<?php

use App\Address;
use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Ramsey\Uuid\Uuid;

class DeleteAccessGrantTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * @var \App\AccessGrant
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
     * @param boolean $grantKey
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
        $this->json('DELETE', $uri);
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
        $this->json('DELETE', '/grants/' . $this->grant->id);
        $this->seeStatusCode(Response::HTTP_NO_CONTENT);
        $this->assertSoftDeleted(
            'access_grants',
            ['id' => $this->grant->id],
            Address::DELETED_AT);
    }
}
