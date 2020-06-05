<?php

use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Ramsey\Uuid\Uuid;
use App\Company;

class ViewCompanyTest extends TestCase
{
    use DatabaseMigrations;

    public function testCompanyWithoutSubscription() {
        $company = $this->makeFakeCompany();
        $company->save();

        $user = $this->getUser(self::$USER);
        $this->actingAs($user);
        $user->company()->associate($company);
        $user->save();

        $this->json('GET', '/companies/' . $company->id);
        $this->seeSuccessfulCompanyRequest($company);
    }

    public function provideInvalidAccessData()
    {
        return [
            [null, 'random', Response::HTTP_UNAUTHORIZED],
            [null, 'company', Response::HTTP_UNAUTHORIZED],
            [self::$USER, 'random', Response::HTTP_NOT_FOUND],
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
        $this->json('GET', '/companies/' . $this->company->id);
        $this->seeSuccessfulCompanyRequest($this->company);
    }

    private function seeSuccessfulCompanyRequest(Company $company) {
        $this->seeStatusCode(Response::HTTP_OK);
        $this->seeHeader('Content-Type', 'application/json');
        $this->seeJsonStructure([
            'data' => [
                'id',
                'name',
            ],
        ])->seeJsonNotNull([
            'data' => [
                'id',
            ],
        ])->seeJsonContains([
            'name' => $company->name,
        ]);
    }
}
