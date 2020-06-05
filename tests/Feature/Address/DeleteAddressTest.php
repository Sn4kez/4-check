<?php

use App\Address;
use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Ramsey\Uuid\Uuid;

class DeleteAddressTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * @var \App\Address $address
     */
    protected $address;

    public function setUp()
    {
        parent::setUp();
        $this->address = $this->makeFakeAddress('billing');
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
        $this->json('DELETE', '/addresses/' . $this->address->id);
        $this->seeStatusCode(Response::HTTP_NO_CONTENT);
        $this->assertSoftDeleted(
            'addresses',
            ['id' => $this->address->id],
            Address::DELETED_AT);
    }
}
