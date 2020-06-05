<?php

use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Ramsey\Uuid\Uuid;
use Carbon\Carbon;

class VerifyEmailTest extends TestCase
{

    use DatabaseMigrations;

    public function testTokenCreation()
    {
    	$user = $this->getUser(self::$USER);
    	$user->token = Uuid::uuid4()->toString();
    	$user->save();
        
    	$this->json('POST', '/users/verify', [
    		'token' => $user->token,
    	]);
    	$this->seeStatusCode(Response::HTTP_OK);
    }

}