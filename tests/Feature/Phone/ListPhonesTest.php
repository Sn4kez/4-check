<?php

use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Ramsey\Uuid\Uuid;

class ListPhonesTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * @var array $phones
     */
    protected $phones;

    public function setUp()
    {
        parent::setUp();
        $this->phones = [
            $this->makeFakePhone('work'),
            $this->makeFakePhone('home'),
        ];
        $this->user->phones()->saveMany($this->phones);
    }

    public function provideInvalidAccessData()
    {
        return [
            [null, 'random', Response::HTTP_UNAUTHORIZED],
            [null, 'user', Response::HTTP_UNAUTHORIZED],
            [self::$USER, 'random', Response::HTTP_NOT_FOUND],
            [self::$OTHER_USER, 'user', Response::HTTP_FORBIDDEN],
            [self::$OTHER_ADMIN, 'user', Response::HTTP_FORBIDDEN],
        ];
    }

    /**
     * @param string $userKey
     * @param string $userIdKey
     * @param int $statusCode
     * @dataProvider provideInvalidAccessData
     */
    public function testInvalidAccess($userKey, $userIdKey, $statusCode)
    {
        if ($userKey != null) {
            $user = $this->getUser($userKey);
            $this->actingAs($user);
        }
        if ($userIdKey === 'user') {
            $uri = '/users/' . $this->user->id . '/phones';
        } else {
            $uri = '/users/' . Uuid::uuid4()->toString() . '/phones';
        }
        $this->json('GET', $uri);
        $this->seeStatusCode($statusCode);
        $this->seeHeader('Content-Type', 'application/json');
    }

    public function provideValidAccessData()
    {
        return [
            [self::$USER, 'user', false],
            [self::$ADMIN, 'user', false],
            [self::$SUPER_ADMIN, 'user', false],
            [self::$OTHER_ADMIN, 'otherUser', true],
        ];
    }

    /**
     * @param $userKey
     * @param $userIdKey
     * @param $expectEmpty
     * @dataProvider provideValidAccessData
     */
    public function testValidAccess($userKey, $userIdKey, $expectEmpty)
    {
        $user = $this->getUser($userKey);
        $this->actingAs($user);
        if ($userIdKey === 'user') {
            $uri = '/users/' . $this->user->id . '/phones';
        } else {
            $uri = '/users/' . $this->otherUser->id . '/phones';
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
                        'countryCode',
                        'nationalNumber',
                        'type',
                    ],
                    [
                        'id',
                        'countryCode',
                        'nationalNumber',
                        'type',
                    ],
                ],
            ]);
            foreach ($this->phones as $phone) {
                $this->seeJsonContains([
                    'countryCode' => $phone->countryCode,
                    'nationalNumber' => $phone->nationalNumber,
                    'type' => $phone->type->id,
                ]);
            }
        }
    }
}
