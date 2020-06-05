<?php

use App\UserInvitation;
use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Ramsey\Uuid\Uuid;
use Illuminate\Support\Carbon;

class ListUserInvitationTest extends TestCase {
    use DatabaseMigrations;

    /**
     * @var \App\Task $task
     */
    protected $invitations;
    private $otherCompaniesInvitation;


    public function setUp() {
        parent::setUp();

        $invitation1 = new UserInvitation();
        $invitation1->token = Uuid::uuid4()->toString();
        $invitation1->email = "test@test.com";
        $invitation1->company()->associate($this->company);
        $invitation1->tokenCreatedAt = Carbon::now();
        $invitation1->save();

        $invitation2 = new UserInvitation();
        $invitation2->token = Uuid::uuid4()->toString();
        $invitation2->email = "test2@test.com";
        $invitation2->tokenCreatedAt = Carbon::now();
        $invitation2->company()->associate($this->company);
        $invitation2->save();

        $this->otherCompaniesInvitation = new UserInvitation();
        $this->otherCompaniesInvitation->token = Uuid::uuid4()->toString();
        $this->otherCompaniesInvitation->email = "test2@test3.com";
        $this->otherCompaniesInvitation->tokenCreatedAt = Carbon::now();
        $this->otherCompaniesInvitation->company()->associate($this->otherCompany);
        $this->otherCompaniesInvitation->save();

        $this->invitations = [
            $invitation1,
            $invitation2
        ];
    }

    /**
     * Tests if every user has the correct permissions for invitations
     */
    public function testAccessOtherCompanies() {
        $this->actingAs($this->getUser(self::$USER));

        $r = $this->json('GET', '/users/invitations/company/' . $this->company->id);
        $content = json_decode($r->response->getContent(), true);
        $this->assertTrue(2 === count($content['data']));

        $r = $this->json('GET', '/users/invitations/company/' . $this->otherCompany->id);
        $content = json_decode($r->response->getContent(), true);
        $this->assertTrue(1 === count($content['data']));
    }

    public function provideInvalidAccessData() {
        return [
            [
                null,
                'random',
                Response::HTTP_UNAUTHORIZED
            ],
            [
                null,
                'company',
                Response::HTTP_UNAUTHORIZED
            ],
        ];
    }

    /**
     * @param string $userKey
     * @param string $userIdKey
     * @param int $statusCode
     * @dataProvider provideInvalidAccessData
     */
    public function testInvalidAccess($userKey, $companyIdKey, $statusCode) {

        if ($companyIdKey === 'company') {
            $uri = '/users/invitations/company/' . $this->company->id;
        } else {
            $uri = '/users/invitations/company/' . Uuid::uuid4()->toString();
        }

        $this->json('GET', $uri);
        $this->seeStatusCode($statusCode);
        $this->seeHeader('Content-Type', 'application/json');
    }

    public function provideValidAccessData() {
        return [
            [
                self::$ADMIN,
                'company',
                false
            ],
            [
                self::$SUPER_ADMIN,
                'company',
                false
            ],
            [
                self::$OTHER_ADMIN,
                'otherCompany',
                true
            ],
        ];
    }

    /**
     * @param $userKey
     * @param $userIdKey
     * @param $expectEmpty
     * @dataProvider provideValidAccessData
     */
    public function testValidAccess($userKey, $companyKey, $expectEmpty) {
        $user = $this->getUser($userKey);
        $this->actingAs($user);

        if ($companyKey === 'company') {
            $uri = '/users/invitations/company/' . $this->company->id;
        } else {
            $uri = '/users/invitations/company/' . $this->otherUser->company->id;
        }

        $this->json('GET', $uri);
        $this->seeStatusCode(Response::HTTP_OK);
        $this->seeHeader('Content-Type', 'application/json');
        if ($expectEmpty) {
            $this->seeJsonStructure([
                'data' => [],
            ]);
        } else {
            $this->seeJsonStructure([
                'data' => [
                    [
                        'token',
                        'email',
                        'company',
                    ],
                    [
                        'token',
                        'email',
                        'company',
                    ],
                ],
            ]);
            foreach ($this->invitations as $inv) {
                $this->seeJsonContains([
                    'token' => $inv->token,
                    'email' => $inv->email,
                    'company' => $inv->company->id,
                ]);
            }
        }
    }
}
