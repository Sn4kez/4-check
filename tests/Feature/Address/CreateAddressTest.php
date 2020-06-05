<?php

use App\Address;
use App\AddressType;
use App\Country;
use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Ramsey\Uuid\Uuid;

class CreateAddressTest extends TestCase
{
    use DatabaseMigrations;

    public function provideInvalidAccessData()
    {
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
    public function testInvalidAccess($userKey, $companyKey, $statusCode)
    {
        if ($userKey != null) {
            $user = $this->getUser($userKey);
            $this->actingAs($user);
        }
        if ($companyKey === 'company') {
            $uri = '/companies/' . $this->company->id . '/addresses';
        } else {
            $uri = '/companies/' . Uuid::uuid4()->toString() . '/addresses';
        }
        $this->json('POST', $uri);
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
     * @param string $userKey
     * @dataProvider provideValidAccessData
     */
    public function testValidAccess($userKey)
    {
        $user = $this->getUser($userKey);
        $this->actingAs($user);
        $address1 = factory(Address::class)->make();
        $address1->type()->associate(AddressType::find('billing'));
        $address1->country()->associate(Country::find('de'));
        $address2 = factory(Address::class)->make();
        $address2->type()->associate(AddressType::find('postal'));
        $address2->country()->associate(Country::find('ch'));
        $this->json('POST', '/companies/' . $this->company->id . '/addresses', [
            'country' => $address1->country->id,
            'type' => $address1->type->id,
        ]);
        $this->seeStatusCode(Response::HTTP_CREATED);
        $this->seeHeader('Content-Type', 'application/json');
        $this->seeJsonStructure([
            'data' => [
                'id',
                'country',
                'type',
            ],
        ])->seeJsonNotNull([
            'data' => [
                'id',
            ],
        ])->seeJsonContains([
            'country' => $address1->country->id,
            'type' => $address1->type->id,
        ]);
        $this->json('POST', '/companies/' . $this->company->id . '/addresses', [
            'country' => $address2->country->id,
            'type' => $address2->type->id,
        ]);
        $this->seeStatusCode(Response::HTTP_CREATED);
        $this->seeHeader('Content-Type', 'application/json');
        $this->seeJsonStructure([
            'data' => [
                'id',
                'country',
                'type',
            ],
        ])->seeJsonNotNull([
            'data' => [
                'id',
            ],
        ])->seeJsonContains([
            'country' => $address2->country->id,
            'type' => $address2->type->id,
        ]);
        $this->seeInDatabase('addresses', [
            'companyId' => $this->company->id,
            'countryId' => $address1->country->id,
            'typeId' => $address1->type->id,
        ])->seeInDatabase('addresses', [
            'companyId' => $this->company->id,
            'countryId' => $address2->country->id,
            'typeId' => $address2->type->id,
        ]);
        $this->assertCount(2, $this->company->addresses);
    }

    public function provideValidEntities()
    {
        return [
            ['line1', 'line1'],
            ['line2', 'line2'],
            ['city', 'city'],
            ['postalCode', 'postalCode'],
            ['province', 'province']
        ];
    }

    /**
     * @param $attribute
     * @param $value
     * @dataProvider provideValidEntities
     */
    public function testValidEntities($attribute, $value)
    {
        $address = factory(Address::class)->make();
        $address->type()->associate(AddressType::find('billing'));
        $address->country()->associate(Country::find('de'));
        $data = array_merge([
            'type' => $address->type->id,
            'country' => $address->country->id,
        ], [$attribute => $value]);
        $this->actingAs($this->admin);
        $this->json('POST', '/companies/' . $this->company->id . '/addresses',
            $data);
        $this->seeStatusCode(Response::HTTP_CREATED);
        $this->seeHeader('Content-Type', 'application/json');
        $this->seeJsonStructure([
            'data' => [
                $attribute,
            ],
        ])->seeJsonContains([
            $attribute => $value,
        ]);
        $this->seeInDatabase('addresses', [
            'companyId' => $this->company->id,
            $attribute => $value,
        ]);
    }

    public function provideInvalidEntities()
    {
        return [
            ['country', null],
            ['country', 123],
            ['country', 'unknown'],
            ['type', null],
            ['type', 123],
            ['type', 'unknown'],
            ['line1', 123],
            ['line1', str_repeat('Long', 32) . 'Line'],
            ['line2', 123],
            ['line2', str_repeat('Long', 32) . 'Line'],
            ['city', 123],
            ['city', str_repeat('Long', 32) . 'City'],
            ['postalCode', 123],
            ['postalCode', str_repeat('Long', 32) . 'PostalCode'],
            ['province', 123],
            ['province', str_repeat('Long', 32) . 'Province'],
        ];
    }

    /**
     * @param string $attribute
     * @param string $value
     * @dataProvider provideInvalidEntities
     */
    public function testInvalidEntities($attribute, $value)
    {
        $address = factory(Address::class)->make();
        $address->type()->associate(AddressType::find('billing'));
        $address->country()->associate(Country::find('de'));
        $data = array_merge([
            'type' => $address->type->id,
            'country' => $address->country->id,
        ], [$attribute => $value]);
        $this->actingAs($this->admin);
        $this->json(
            'POST',
            '/companies/' . $this->company->id . '/addresses',
            $data);
        $this->seeStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->seeHeader('Content-Type', 'application/json');
    }
}
