<?php

use Laravel\Lumen\Testing\DatabaseMigrations;
use Illuminate\Http\Response;

class HardResetTest extends TestCase
{
    use DatabaseMigrations;

    public function provideInvalidAccessData()
    {
        return [
            [null, Response::HTTP_UNAUTHORIZED],
            [self::$USER, Response::HTTP_FORBIDDEN]
        ];
    }

    /**
     * @param string $userKey
     * @param int $statusCode
     * @dataProvider provideInvalidAccessData
     */
    public function testInvalidAccess($userKey, $statusCode)
    {
    	if(!is_null($userKey))
    	{
    		$user = $this->getUser($userKey);
        	$this->actingAs($user);
    	}

        $this->json('GET', '/hardreset/' . $this->company->id);
        $this->seeStatusCode($statusCode);
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
     * @param $withParent
     * @dataProvider provideValidAccessData
     */
    public function testValidAccess($userKey)
    {
    	$user = $this->getUser($userKey);
       	$this->actingAs($user);

       	$this->json('GET', '/hardreset/' . $this->company->id);
        $this->seeStatusCode(Response::HTTP_NO_CONTENT);
    }
}
