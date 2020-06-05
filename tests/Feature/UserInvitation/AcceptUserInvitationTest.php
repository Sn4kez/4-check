<?php

use App\UserInvitation;
use App\User;
use App\Phone;
use App\Gender;
use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Ramsey\Uuid\Uuid;
use Illuminate\Support\Carbon;

class AcceptUserInvitationTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * @var \App\UserInvitation $invitation
     */
    protected $invitation;

    public function setUp()
    {
        parent::setUp();

        $this->invitation = new UserInvitation();
        $this->invitation->token = Uuid::uuid4()->toString();
        $this->invitation->email = "test@test.com";
        $this->invitation->tokenCreatedAt = Carbon::now();
        $this->invitation->company()->associate($this->company);
        $this->invitation->save();
    }

    public function testValidAccess()
    {
    	$user = factory(User::class)->make();
        $user->gender()->associate(Gender::all()->random());
        $phone = factory(Phone::class)->make();

        $this->json('POST', '/invitation/' . $this->invitation->token, [
        	'firstName' => $user->firstName,
        	'middleName' => $user->middleName,
        	'lastName' => $user->lastName,
        	'password' => 'Secret123',
            'gender' => $user->gender->id,
            'phone' => [
                'countryCode' => $phone->countryCode,
                'nationalNumber' => $phone->countryCode,
            ],
        ]);
        $this->seeStatusCode(Response::HTTP_CREATED);
        $this->seeHeader('Content-Type', 'application/json');
        $this->seeJsonStructure([
            'data' => [
                'id',
                'email',
                'companyId',
            ],
        ])->seeJsonNotNull([
            'data' => [
                'id',
                'companyId',
            ],
        ])->seeJsonContains([
            'email' => $this->invitation->email,
            
        ]);
        $this->seeInDatabase('users', [
            'email' => $this->invitation->email,
        ]);
    }
}
