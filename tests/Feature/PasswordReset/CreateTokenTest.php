<?php

use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;

class CreateTokenTest extends TestCase
{

    use DatabaseMigrations;

    public function testTokenCreation()
    {
    	$user = $this->getUser(self::$USER);
        
    	$this->json('POST', '/users/password/token', [
    		'email' => $user->email,
    	]);

    	$this->seeStatusCode(Response::HTTP_OK);
    }

}