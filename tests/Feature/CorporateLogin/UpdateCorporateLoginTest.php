<?php

use App\Company;
use App\CorporateIdentity;
use App\CorporateIdentityLogin;
use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Ramsey\Uuid\Uuid;

class UpdateCorporateLoginTest extends TestCase
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
        $this->json('PATCH', '/companies/preferences/login/' . $this->login->id);
        $this->seeStatusCode($statusCode);
        $this->seeHeader('Content-Type', 'application/json');
    }

    public function provideValidEntities()
    {
        return [
        	
            [self::$ADMIN,'test'],
            [self::$SUPER_ADMIN, 'test'],
            
        ];
    }

    /**
     * @param $userKey
     * @param $attribute
     * @param $dbAttribute
     * @param $value
     * @dataProvider provideValidEntities
     */
    public function testValidEntities($userKey, $value)
    {
    	$user = $this->getUser($userKey);
        $this->actingAs($user);

        $this->json('PATCH', '/companies/preferences/login/' . $this->login->id, [
            'id' => $value,
        ]);

        $this->seeStatusCode(Response::HTTP_NO_CONTENT);
    }

    public function provideInvalidEntities()
    {
        return [
            [123],
            [null],
        ];
    }

    /**
     * @param string $value
     * @dataProvider provideInvalidEntities
     */
    public function testInvalidEntities($value)
    {
    	$user = $this->getUser(self::$ADMIN);
        $this->actingAs($user);

        $ci = new CorporateIdentity();
        $ci->brand_primary = '#000000';
        $ci->brand_secondary = '#FFFFFF';
        $ci->link_color = '#FF0000';
        $ci->company()->associate($this->company);
        $ci->save();

        $this->json('PATCH', '/companies/preferences/login/' . $this->login->id, [
            'id' => $value,
            'ci' => $ci->id,
        ]);

        $this->seeStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->seeHeader('Content-Type', 'application/json');
    }
}
