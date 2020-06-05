<?php

use App\Company;
use App\CorporateIdentity;
use App\CorporateIdentityLogin;
use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Ramsey\Uuid\Uuid;

class DeleteCorporateLoginTest extends TestCase
{
	use DatabaseMigrations;

	private $login;
    
    public function setUp()
    {
        parent::setUp();

        $ci = new CorporateIdentity();
        $ci->id = Uuid::uuid4()->toString();
        $ci->brand_primary = '#000000';
        $ci->brand_secondary = '#FFFFFF';
        $ci->link_color = '#FF0000';
        $ci->company()->associate($this->company);
        $ci->save();

        $this->login = new CorporateIdentityLogin();
        $this->login->id = "test123";
        $this->login->corporateIdentity()->associate($ci);
        $this->login->save();
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
        $this->json('DELETE', '/companies/preferences/login/' . $this->login->id);
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

        $this->json('DELETE', '/companies/preferences/login/' . $this->login->id);

        $this->seeStatusCode(Response::HTTP_NO_CONTENT);
    }
}
