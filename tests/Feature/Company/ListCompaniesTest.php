<?php

use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;

class ListCompaniesTest extends TestCase
{
    use DatabaseMigrations;

    public function provideInvalidAccessData()
    {
        return [
            [null, Response::HTTP_UNAUTHORIZED],
        ];
    }

    /**
     * @param string $userKey
     * @param int $statusCode
     * @dataProvider provideInvalidAccessData
     */
    public function testInvalidAccess($userKey, $statusCode)
    {
        if ($userKey != null) {
            $user = $this->getUser($userKey);
            $this->actingAs($user);
        }
        $this->json('GET', '/companies');
        $this->seeStatusCode($statusCode);
        $this->seeHeader('Content-Type', 'application/json');
    }

    public function provideValidAccessData()
    {
        return [
            [self::$ADMIN, 'company'],
            [self::$SUPER_ADMIN, 'all'],
        ];
    }

    /**
     * @param $userKey
     * @param $expected
     * @dataProvider provideValidAccessData
     */
    public function testValidAccess($userKey, $expected)
    {
        $user = $this->getUser($userKey);
        $this->actingAs($user);

        $this->json('GET', '/companies');

        $this->seeStatusCode(Response::HTTP_OK);
        $this->seeHeader('Content-Type', 'application/json');

        $expectedCompanies = [$this->company];

        if ($expected == 'all') {
            $expectedCompanies[] = $this->otherCompany;
        }

        $this->seeJsonStructure([
            'data' => [
                [
                    'id',
                    'name',
                    'sector',
                ],
            ],
        ]);
        foreach ($expectedCompanies as $company) {
            $this->seeJsonContains([
                'id' => $company->id,
                'name' => $company->name,
                'sector' => $company->sector->id,
            ]);
        }
    }
}
