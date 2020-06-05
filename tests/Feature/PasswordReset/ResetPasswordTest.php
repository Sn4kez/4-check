<?php

use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Ramsey\Uuid\Uuid;
use Carbon\Carbon;

class ResetPasswordTest extends TestCase
{

    use DatabaseMigrations;

    public function testTokenCreation()
    {
    	$user = $this->getUser(self::$USER);
    	$user->token = Uuid::uuid4()->toString();
    	$user->tokenCreatedAt = Carbon::now();
    	$user->save();

    	$password = 'Test23!';
        
    	$this->json('POST', '/users/password/reset', [
    		'token' => $user->token,
    		'password' => $password,
    	]);
    	$this->seeStatusCode(Response::HTTP_OK);
    }

}