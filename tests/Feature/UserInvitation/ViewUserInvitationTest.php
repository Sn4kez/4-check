<?php

use App\UserInvitation;
use App\User;
use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Ramsey\Uuid\Uuid;
use Illuminate\Support\Carbon;

class ViewUserInvitationTest extends TestCase
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
        $this->json('GET', '/users/invitations/' . $this->invitation->token);
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
        $this->json('GET', '/users/invitations/' . $this->invitation->token);
        $this->seeStatusCode(Response::HTTP_OK);
        $this->seeHeader('Content-Type', 'application/json');
        $this->seeJsonStructure([
            'data' => [
                'token',
                'email',
                'company',
            ],
        ])->seeJsonNotNull([
            'data' => [
                'token',
                'email',
                'company',
            ],
        ])->seeJsonContains([
            'token' => $this->invitation->token,
            'email' => $this->invitation->email,
            'company' => $this->invitation->company->id,
        ]);
    }
}