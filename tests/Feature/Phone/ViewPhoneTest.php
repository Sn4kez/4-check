<?php

use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Ramsey\Uuid\Uuid;

class ViewPhoneTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * @var \App\Phone $phone
     */
    protected $phone;

    public function setUp()
    {
        parent::setUp();
        $this->phone = $this->makeFakePhone('work');
        $this->user->phones()->save($this->phone);
    }

    public function provideInvalidAccessData()
    {
        return [
            [null, 'random', Response::HTTP_UNAUTHORIZED],
            [null, 'phone', Response::HTTP_UNAUTHORIZED],
            [self::$USER, 'random', Response::HTTP_NOT_FOUND],
            [self::$OTHER_USER, 'phone', Response::HTTP_FORBIDDEN],
            [self::$OTHER_ADMIN, 'phone', Response::HTTP_FORBIDDEN],
        ];
    }

    /**
     * @param string $userKey
     * @param boolean $phoneIdKey
     * @param int $statusCode
     * @dataProvider provideInvalidAccessData
     */
    public function testInvalidAccess($userKey, $phoneIdKey, $statusCode)
    {
        if ($userKey != null) {
            $user = $this->getUser($userKey);
            $this->actingAs($user);
        }
        if ($phoneIdKey === 'phone') {
            $uri = '/phones/' . $this->phone->id;
        } else {
            $uri = '/phones/' . Uuid::uuid4()->toString();
        }
        $this->json('GET', $uri);
        $this->seeStatusCode($statusCode);
        $this->seeHeader('Content-Type', 'application/json');
    }

    public function provideValidAccessData()
    {
        return [
            [self::$USER],
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
        $this->json('GET', '/phones/' . $this->phone->id);
        $this->seeStatusCode(Response::HTTP_OK);
        $this->seeHeader('Content-Type', 'application/json');
        $this->seeJsonStructure([
            'data' => [
                'id',
                'countryCode',
                'nationalNumber',
                'type',
            ],
        ])->seeJsonNotNull([
            'data' => [
                'id',
            ],
        ])->seeJsonContains([
            'id' => $this->phone->id,
            'countryCode' => $this->phone->countryCode,
            'nationalNumber' => $this->phone->nationalNumber,
            'type' => $this->phone->type->id,
        ]);
    }
}
