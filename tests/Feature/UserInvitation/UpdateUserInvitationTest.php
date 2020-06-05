<?php

use App\UserInvitation;
use App\User;
use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Ramsey\Uuid\Uuid;
use Illuminate\Support\Carbon;

class UpdateUserInvitationTest extends TestCase
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
            [self::$USER, Response::HTTP_UNAUTHORIZED],
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
        $this->json('PATCH', '/users/invitations/' . $this->invitation->token);
        $this->seeStatusCode($statusCode);
        $this->seeHeader('Content-Type', 'application/json');
    }

    public function provideValidEntities()
    {
        return [
            [self::$ADMIN, 'test2@test2.com'],
            [self::$SUPER_ADMIN, 'test2@test2.com'],
        ];
    }

    /**
     * @param $userKey
     * @param $value
     * @dataProvider provideValidEntities
     */
    public function testValidEntities($userKey, $value)
    {
        $user = $this->getUser($userKey);
        $this->actingAs($user);
        $data = ['email' => $value];
        $this->json('PATCH', '/users/invitations/' . $this->invitation->token, $data);
        $this->seeStatusCode(Response::HTTP_NO_CONTENT);
        $this->seeInDatabase('user_invitations', [
            'email' => $value,
        ]);
    }

    public function provideInvalidEntities()
    {
        return [
            [null],
            [123],
            [str_repeat('123', 128)],
        ];
    }

    /**
     * @param $value
     * @dataProvider provideInvalidEntities
     */
    public function testInvalidEntities($value)
    {
        $user = $this->getUser(self::$ADMIN);
        $this->actingAs($user);
        $data = ['email' => $value];

        $this->json('PATCH', '/users/invitations/' . $this->invitation->token, $data);
        $this->seeStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->seeHeader('Content-Type', 'application/json');
    }
}
