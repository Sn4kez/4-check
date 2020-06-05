<?php

use App\Gender;
use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Ramsey\Uuid\Uuid;

class ListUsersTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * @var array
     */
    public static $USER_STATES = [
        'with_first_name',
        'with_middle_name',
        'with_last_name',
    ];

    /**
     * @var array
     */
    protected $users;

    /**
     * @var array
     */
    protected $otherUsers;

    public function setUp()
    {
        parent::setUp();
        $this->users = [
            $this->user,
            $this->makeFakeUser(ListUsersTest::$USER_STATES),
        ];
        $this->otherUsers = [
            $this->otherUser,
            $this->makeFakeUser(ListUsersTest::$USER_STATES),
        ];
        foreach ($this->users as $user) {
            $user->gender()->associate(Gender::all()->random());
        }
        foreach ($this->otherUsers as $user) {
            $user->gender()->associate(Gender::all()->random());
        }
        $this->company->users()->saveMany($this->users);
        $this->otherCompany->users()->saveMany($this->otherUsers);
    }

    public function provideInvalidAccessData()
    {
        return [
            [null, null, Response::HTTP_UNAUTHORIZED],
            [self::$USER, 'random', Response::HTTP_NOT_FOUND],
            [self::$USER, 'company', Response::HTTP_FORBIDDEN, false],
            [self::$ADMIN, null, Response::HTTP_FORBIDDEN, false],
            [self::$ADMIN, 'company', Response::HTTP_FORBIDDEN, false],
            [self::$OTHER_USER, 'company', Response::HTTP_FORBIDDEN],
        ];
    }

    /**
     * @param string $userKey
     * @param string $companyIdKey
     * @param int $statusCode
     * @param boolean $validSubscription
     * @dataProvider provideInvalidAccessData
     */
    public function testInvalidAccess($userKey, $companyIdKey, $statusCode,
                                      $validSubscription = true)
    {
        if ($userKey != null) {
            $user = $this->getUser($userKey);
            $this->actingAs($user);
            if (!$validSubscription) {
                $subscription = $user->company->companySubscription;
                $subscription->viewCompany = false;
                $subscription->save();
            }
        }
        if ($companyIdKey === 'random') {
            $uri = '/users?company=' . Uuid::uuid4()->toString();
        } else if ($companyIdKey === 'company') {
            $uri = '/users?company=' . $this->company->id;
        } else {
            $uri = '/users';
        }
        $this->json('GET', $uri);
        $this->seeStatusCode($statusCode);
        $this->seeHeader('Content-Type', 'application/json');
    }

    public function provideValidAccessData()
    {
        return [
            [self::$USER, null, 'company'],
            [self::$USER, 'company', 'company'],
            [self::$ADMIN, null, 'company'],
            [self::$ADMIN, 'company', 'company'],
            [self::$SUPER_ADMIN, null, 'all'],
            [self::$SUPER_ADMIN, 'company', 'company'],
        ];
    }

    /**
     * @param $userKey
     * @param $companyIdKey
     * @param $expected
     * @dataProvider provideValidAccessData
     */
    public function testValidAccess($userKey, $companyIdKey, $expected)
    {
        $user = $this->getUser($userKey);
        $this->actingAs($user);
        if ($companyIdKey === 'random') {
            $uri = '/users?company=' . Uuid::uuid4()->toString();
        } else {
            $uri = '/users';
        }
        $this->json('GET', $uri);
        $this->seeStatusCode(Response::HTTP_OK);
        $this->seeHeader('Content-Type', 'application/json');
        $expectedUsers = [];
        if ($expected == 'company') {
            $expectedUsers = array_merge($expectedUsers, $this->users);
        } else {
            $expectedUsers = array_merge($expectedUsers, $this->users);
            $expectedUsers = array_merge($expectedUsers, $this->otherUsers);
        }
        $this->seeJsonStructure([
            'data' => [
                [
                    'id',
                    'email',
                    'firstName',
                    'middleName',
                    'lastName',
                    'gender',
                    'locale',
                    'timezone',
                ],
            ],
        ]);
        foreach ($expectedUsers as $user) {
            /** @var \App\User $user */
            $user = $user->fresh();
            $this->seeJsonContains([
                'id' => $user->id,
                'email' => $user->email,
                'firstName' => $user->firstName,
                'middleName' => $user->middleName,
                'lastName' => $user->lastName,
                'gender' => $user->gender->id,
                'locale' => $user->locale->id,
                'timezone' => $user->timezone,
            ]);
        }
    }
}
