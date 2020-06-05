<?php

use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Ramsey\Uuid\Uuid;

class UpdateCompanyTest extends TestCase
{
    use DatabaseMigrations;

    public function provideInvalidAccessData()
    {
        return [
            [null, 'random', Response::HTTP_UNAUTHORIZED],
            [null, 'company', Response::HTTP_UNAUTHORIZED],
            [self::$USER, 'random', Response::HTTP_NOT_FOUND],
            [self::$USER, 'company', Response::HTTP_FORBIDDEN],
            [self::$OTHER_USER, 'company', Response::HTTP_FORBIDDEN],
            [self::$OTHER_ADMIN, 'company', Response::HTTP_FORBIDDEN],
        ];
    }

    /**
     * @param string $userKey
     * @param boolean $companyIdKey
     * @param int $statusCode
     * @throws Exception
     * @dataProvider provideInvalidAccessData
     */
    public function testInvalidAccess($userKey, $companyIdKey, $statusCode)
    {
        if ($userKey != null) {
            $user = $this->getUser($userKey);
            $this->actingAs($user);
        }
        if ($companyIdKey === 'company') {
            $uri = '/companies/' . $this->company->id;
        } else {
            $uri = '/companies/' . Uuid::uuid4()->toString();
        }
        $this->json('PATCH', $uri);
        $this->seeStatusCode($statusCode);
        $this->seeHeader('Content-Type', 'application/json');
    }

    public function provideValidEntities()
    {
        return [
            [self::$ADMIN, 'name', 'name', 'name'],
            [self::$SUPER_ADMIN, 'name', 'name', 'name'],
            [self::$ADMIN, 'sector', 'sectorId', 'catering'],
            [self::$SUPER_ADMIN, 'sector', 'sectorId', 'catering'],
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
        $this->json('PATCH', '/companies/' . $this->company->id, $data);
        $this->seeStatusCode(Response::HTTP_NO_CONTENT);
        $this->seeInDatabase('companies', [
            'id' => $this->company->id,
            $dbAttribute => $value,
        ]);
    }

    public function provideInvalidEntities()
    {
        return [
            [self::$ADMIN, 'name', null],
            [self::$ADMIN, 'name', 123],
            [self::$ADMIN, 'name', str_repeat('Long', 32) . 'Name'],
            [self::$ADMIN, 'sector', null],
            [self::$ADMIN, 'sector', 123],
            [self::$ADMIN, 'sector', 'unknown'],
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
        $this->json('PATCH', '/companies/' . $this->company->id, $data);
        $this->seeStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->seeHeader('Content-Type', 'application/json');
    }
}
