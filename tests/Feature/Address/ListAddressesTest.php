<?php

use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Ramsey\Uuid\Uuid;

class ListAddressesTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * @var array
     */
    protected $addresses;

    public function setUp()
    {
        parent::setUp();
        $this->addresses = [
            $this->makeFakeAddress('billing', [
                'with_line1',
                'with_line2',
                'with_city',
                'with_postal_code',
                'with_province',
            ]),
            $this->makeFakeAddress('postal', [
                'with_line1',
                'with_line2',
                'with_city',
                'with_postal_code',
                'with_province',
            ]),
        ];
        $this->company->addresses()->saveMany($this->addresses);
    }

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
        $this->json('GET', $uri);
        $this->seeStatusCode($statusCode);
        $this->seeHeader('Content-Type', 'application/json');
    }

    public function provideValidAccessData()
    {
        return [
            [self::$ADMIN, 'company', false],
            [self::$SUPER_ADMIN, 'company', false],
            [self::$OTHER_ADMIN, 'otherCompany', true],
        ];
    }

    /**
     * @param $userKey
     * @param $companyKey
     * @param $expectEmpty
     * @dataProvider provideValidAccessData
     */
    public function testValidAccess($userKey, $companyKey, $expectEmpty)
    {
        $user = $this->getUser($userKey);
        $this->actingAs($user);
        if ($companyKey === 'company') {
            $uri = '/companies/' . $this->company->id . '/addresses';
        } else {
            $uri = '/companies/' . $this->otherCompany->id . '/addresses';
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
                        'company',
                        'line1',
                        'line2',
                        'city',
                        'postalCode',
                        'province',
                        'country',
                        'type',
                    ],
                    [
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
                ],
            ]);
            foreach ($this->addresses as $address) {
                $this->seeJsonContains([
                    'id' => $address->id,
                    'company' => $address->company->id,
                    'line1' => $address->line1,
                    'line2' => $address->line2,
                    'city' => $address->city,
                    'postalCode' => $address->postalCode,
                    'province' => $address->province,
                    'country' => $address->country->id,
                    'type' => $address->type->id,
                ]);
            }
        }
    }
}
