<?php

use App\Phone;
use App\PhoneType;
use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Ramsey\Uuid\Uuid;

class CreatePhoneTest extends TestCase
{
    use DatabaseMigrations;

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
        $this->json('POST', $uri);
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
     * @param string $userKey
     * @dataProvider provideValidAccessData
     */
    public function testValidAccess($userKey)
    {
        $user = $this->getUser($userKey);
        $this->actingAs($user);
        $phone1 = factory(Phone::class)->make();
        $phone1->type()->associate(PhoneType::find('work'));
        $phone2 = factory(Phone::class)->make();
        $phone2->type()->associate(PhoneType::find('home'));
        $this->actingAs($this->user);
        $this->json('POST', '/users/' . $this->user->id . '/phones', [
            'countryCode' => $phone1->countryCode,
            'nationalNumber' => $phone1->nationalNumber,
            'type' => $phone1->type->id,
        ]);
        $this->seeStatusCode(Response::HTTP_CREATED);
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
            'countryCode' => $phone1->countryCode,
            'nationalNumber' => $phone1->nationalNumber,
            'type' => $phone1->type->id,
        ]);
        $this->json('POST', '/users/' . $this->user->id . '/phones', [
            'countryCode' => $phone2->countryCode,
            'nationalNumber' => $phone2->nationalNumber,
            'type' => $phone2->type->id,
        ]);
        $this->seeStatusCode(Response::HTTP_CREATED);
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
            'countryCode' => $phone2->countryCode,
            'nationalNumber' => $phone2->nationalNumber,
            'type' => $phone2->type->id,
        ]);
        $this->seeInDatabase('phones', [
            'userId' => $this->user->id,
            'countryCode' => $phone1->countryCode,
            'nationalNumber' => $phone1->nationalNumber,
            'typeId' => $phone1->type->id,
        ])->seeInDatabase('phones', [
            'userId' => $this->user->id,
            'countryCode' => $phone2->countryCode,
            'nationalNumber' => $phone2->nationalNumber,
            'typeId' => $phone2->type->id,
        ]);
        $this->assertCount(2, $this->user->phones);
    }

    public function provideInvalidEntities()
    {
        return [
            ['countryCode', null],
            ['countryCode', 123],
            ['countryCode', str_repeat('123', 5)],
            ['countryCode', 'abc'],
            ['nationalNumber', null],
            ['nationalNumber', 123],
            ['nationalNumber', str_repeat('123', 32)],
            ['nationalNumber', 'abc'],
            ['type', null],
            ['type', 123],
            ['type', 'unknown'],
        ];
    }

    /**
     * @param string $attribute
     * @param string $value
     * @dataProvider provideInvalidEntities
     */
    public function testInvalidEntities($attribute, $value)
    {
        $phone = factory(Phone::class)->make();
        $phone->type()->associate(PhoneType::find('work'));
        $data = array_merge([
            'countryCode' => $phone->countryCode,
            'nationalNumber' => $phone->numberNumber,
            'type' => $phone->type->id,
        ], [$attribute => $value]);
        $this->actingAs($this->user);
        $this->json('POST', '/users/' . $this->user->id . '/phones', $data);
        $this->seeStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->seeHeader('Content-Type', 'application/json');
    }
}
