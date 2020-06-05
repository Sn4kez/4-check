<?php

use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Ramsey\Uuid\Uuid;

class UpdatePhoneTest extends TestCase
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
        $this->json('PATCH', $uri);
        $this->seeStatusCode($statusCode);
        $this->seeHeader('Content-Type', 'application/json');
    }

    public function provideValidEntities()
    {
        return [
            [self::$USER, 'countryCode', 'countryCode', '123'],
            [self::$ADMIN, 'countryCode', 'countryCode', '123'],
            [self::$SUPER_ADMIN, 'countryCode', 'countryCode', '123'],
            [self::$USER, 'nationalNumber', 'nationalNumber', '123'],
            [self::$ADMIN, 'nationalNumber', 'nationalNumber', '123'],
            [self::$SUPER_ADMIN, 'nationalNumber', 'nationalNumber', '123'],
            [self::$USER, 'type', 'typeId', 'home'],
            [self::$ADMIN, 'type', 'typeId', 'home'],
            [self::$SUPER_ADMIN, 'type', 'typeId', 'home'],
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
        $this->json('PATCH', '/phones/' . $this->phone->id, $data);
        $this->seeStatusCode(Response::HTTP_NO_CONTENT);
        $this->seeInDatabase('phones', [
            'id' => $this->phone->id,
            $dbAttribute => $value,
        ]);
    }

    public function provideInvalidEntities()
    {
        return [
            [self::$USER, 'countryCode', null],
            [self::$USER, 'countryCode', 123],
            [self::$USER, 'countryCode', str_repeat('123', 5)],
            [self::$USER, 'countryCode', 'abc'],
            [self::$USER, 'nationalNumber', null],
            [self::$USER, 'nationalNumber', 123],
            [self::$USER, 'nationalNumber', str_repeat('123', 32)],
            [self::$USER, 'nationalNumber', 'abc'],
            [self::$USER, 'type', null],
            [self::$USER, 'type', 123],
            [self::$USER, 'type', 'unknown'],
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
        $this->json('PATCH', '/phones/' . $this->phone->id, $data);
        $this->seeStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->seeHeader('Content-Type', 'application/json');
    }
}
