<?php

use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Ramsey\Uuid\Uuid;

class ViewAddressTest extends TestCase
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
        $this->json('GET', $uri);
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
        $this->json('GET', '/addresses/' . $this->address->id);
        $this->seeStatusCode(Response::HTTP_OK);
        $this->seeHeader('Content-Type', 'application/json');
        $this->seeJsonStructure([
            'data' => [
                'id',
                'company',
                'line1',
                'line2',
                'city',
                'postalCode',
                'province',
                'country',
                'type',
            ],
        ])->seeJsonContains([
            'id' => $this->address->id,
            'company' => $this->address->company->id,
            'line1' => $this->address->line1,
            'line2' => $this->address->line2,
            'city' => $this->address->city,
            'postalCode' => $this->address->postalCode,
            'province' => $this->address->province,
            'country' => $this->address->country->id,
            'type' => $this->address->type->id,
        ]);
    }
}
