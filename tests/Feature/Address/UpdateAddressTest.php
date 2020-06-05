<?php

use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Ramsey\Uuid\Uuid;

class UpdateAddressTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * @var \App\Address $address
     */
    protected $address;

    public function setUp()
    {
        parent::setUp();
        $this->address = $this->makeFakeAddress('billing', [
            'with_line1',
            'with_line2',
            'with_city',
            'with_postal_code',
            'with_province',
        ]);
        $this->company->addresses()->save($this->address);
    }

    public function provideInvalidAccessData()
    {
        return [
            [null, 'random', Response::HTTP_UNAUTHORIZED],
            [null, 'address', Response::HTTP_UNAUTHORIZED],
            [self::$USER, 'random', Response::HTTP_NOT_FOUND],
            [self::$USER, 'address', Response::HTTP_FORBIDDEN],
            [self::$ADMIN, 'random', Response::HTTP_NOT_FOUND],
            [self::$OTHER_ADMIN, 'address', Response::HTTP_FORBIDDEN],
        ];
    }

    /**
     * @param string $userKey
     * @param boolean $addressKey
     * @param int $statusCode
     * @dataProvider provideInvalidAccessData
     */
    public function testInvalidAccess($userKey, $addressKey, $statusCode)
    {
        if ($userKey != null) {
            $user = $this->getUser($userKey);
            $this->actingAs($user);
        }
        if ($addressKey === 'address') {
            $uri = '/addresses/' . $this->address->id;
        } else {
            $uri = '/addresses/' . Uuid::uuid4()->toString();
        }
        $this->json('PATCH', $uri);
        $this->seeStatusCode($statusCode);
        $this->seeHeader('Content-Type', 'application/json');
    }

    public function provideValidEntities()
    {
        return [
            [self::$ADMIN, 'type', 'typeId', 'postal'],
            [self::$SUPER_ADMIN, 'type', 'typeId', 'postal'],
            [self::$ADMIN, 'line1', 'line1', null],
            [self::$ADMIN, 'line1', 'line1', 'line1'],
            [self::$SUPER_ADMIN, 'line1', 'line1', 'line1'],
            [self::$ADMIN, 'line2', 'line2', null],
            [self::$ADMIN, 'line2', 'line2', 'line2'],
            [self::$SUPER_ADMIN, 'line2', 'line2', 'line2'],
            [self::$ADMIN, 'city', 'city', null],
            [self::$ADMIN, 'city', 'city', 'city'],
            [self::$SUPER_ADMIN, 'city', 'city', 'city'],
            [self::$ADMIN, 'postalCode', 'postalCode', null],
            [self::$ADMIN, 'postalCode', 'postalCode', 'postalCode'],
            [self::$SUPER_ADMIN, 'postalCode', 'postalCode', 'postalCode'],
            [self::$ADMIN, 'province', 'province', null],
            [self::$ADMIN, 'province', 'province', 'province'],
            [self::$SUPER_ADMIN, 'province', 'province', 'province'],
            [self::$ADMIN, 'country', 'countryId', 'de'],
            [self::$SUPER_ADMIN, 'country', 'countryId', 'de'],
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
        $this->json('PATCH', '/addresses/' . $this->address->id, $data);
        $this->seeStatusCode(Response::HTTP_NO_CONTENT);
        $this->seeInDatabase('addresses', [
            'id' => $this->address->id,
            $dbAttribute => $value,
        ]);
    }

    public function provideInvalidEntities()
    {
        return [
            [self::$ADMIN, 'type', null],
            [self::$ADMIN, 'type', 'unknown'],
            [self::$ADMIN, 'line1', 123],
            [self::$ADMIN, 'line1', str_repeat('Long', 32) . 'Line'],
            [self::$ADMIN, 'line2', 123],
            [self::$ADMIN, 'line2', str_repeat('Long', 32) . 'Line'],
            [self::$ADMIN, 'city', 123],
            [self::$ADMIN, 'city', str_repeat('Long', 32) . 'City'],
            [self::$ADMIN, 'postalCode', 123],
            [self::$ADMIN, 'postalCode', str_repeat('Long', 32) . 'PostalCode'],
            [self::$ADMIN, 'province', 123],
            [self::$ADMIN, 'province', str_repeat('Long', 32) . 'Province'],
            [self::$ADMIN, 'country', null],
            [self::$ADMIN, 'country', 'unknown'],
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
        $this->json('PATCH', '/addresses/' . $this->address->id, $data);
        $this->seeStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->seeHeader('Content-Type', 'application/json');
    }
}
