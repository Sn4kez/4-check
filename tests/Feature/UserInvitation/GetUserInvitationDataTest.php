<?php

use App\UserInvitation;
use App\User;
use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Ramsey\Uuid\Uuid;
use Illuminate\Support\Carbon;

class GetUserInvitationDataTest extends TestCase
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
        $this->json('GET', '/invitation/' . $this->invitation->token);
        $this->seeStatusCode(Response::HTTP_OK);
        $this->seeHeader('Content-Type', 'application/json');
        $this->seeJsonStructure([
            'data' => [
                'token',
                'email',
                'company',
            ],
        ])->seeJsonNotNull([
            'data' => array_merge([
                'token',
                'email',
                'company'
            ]),
        ])->seeJsonContains([
            'email' => $this->invitation->email,
        ]);
    }
}
