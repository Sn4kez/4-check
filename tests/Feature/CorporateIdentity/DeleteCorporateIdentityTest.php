<?php

use App\Company;
use App\Corporateidentity;
use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;

class DeleteCorporateIdentityTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * @var \App\CorporateIdentity $ci
     */
    protected $ci;

    public function setUp()
    {
        parent::setUp();

        $this->ci = new CorporateIdentity();
        $this->ci->brand_primary = '#000000';
        $this->ci->brand_secondary = '#FFFFFF';
        $this->ci->link_color = '#FF0000';
        $this->ci->company()->associate($this->company);
        $this->ci->save();
    }

    public function provideInvalidAccessData()
    {
        return [
            [null, Response::HTTP_UNAUTHORIZED],
            [self::$USER, Response::HTTP_UNAUTHORIZED]
        ];
    }

    /**
     * @param string $userKey
     * @param string $userIdKey
     * @param int $statusCode
     * @dataProvider provideInvalidAccessData
     */
    public function testInvalidAccess($userKey, $statusCode)
    {
        $this->json('DELETE', '/companies/preferences/ci/' . $this->ci->company->id);
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

        $this->json('DELETE', '/companies/preferences/ci/' . $this->ci->company->id);
        $this->seeStatusCode(Response::HTTP_NO_CONTENT);
    }
}
