<?php

use App\Gender;
use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Ramsey\Uuid\Uuid;

class ListMembersTest extends TestCase
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
     * @var \App\Group
     */
    protected $group;

    public function setUp()
    {
        parent::setUp();
        $this->users = [
            $this->user,
            $this->makeFakeUser(ListUsersTest::$USER_STATES),
        ];
        foreach ($this->users as $user) {
            $user->gender()->associate(Gender::all()->random());
        }
        $this->company->users()->saveMany($this->users);
        $this->group = $this->makeFakeGroup();
        $this->company->groups()->save($this->group);
        $user_ids = array_map(function($user) {return $user->id;}, $this->users);
        $this->group->users()->attach($user_ids);
    }

    public function provideInvalidAccessData()
    {
        return [
            [null, 'random', Response::HTTP_UNAUTHORIZED],
            [null, 'group', Response::HTTP_UNAUTHORIZED],
            [self::$USER, 'random', Response::HTTP_NOT_FOUND],
            [self::$USER, 'group', Response::HTTP_FORBIDDEN],
            [self::$ADMIN, 'random', Response::HTTP_NOT_FOUND],
            [self::$OTHER_ADMIN, 'group', Response::HTTP_FORBIDDEN],
        ];
    }

    /**
     * @param string $userKey
     * @param string $groupIdKey
     * @param int $statusCode
     * @dataProvider provideInvalidAccessData
     */
    public function testInvalidAccess($userKey, $groupIdKey, $statusCode)
    {
        if ($userKey != null) {
            $user = $this->getUser($userKey);
            $this->actingAs($user);
        }
        if ($groupIdKey === 'random') {
            $uri = '/groups/' . Uuid::uuid4()->toString() . '/members';
        } else {
            $uri = '/groups/' . $this->group->id . '/members';
        }
        $this->json('GET', $uri);
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
        $this->json('GET', '/groups/' . $this->group->id . '/members');
        $this->seeStatusCode(Response::HTTP_OK);
        $this->seeHeader('Content-Type', 'application/json');
        $this->seeJsonStructure([
            'data' => [
                [
                    'id',
                    'email',
                    'firstName',
                    'middleName',
                    'lastName',
                    'locale',
                    'timezone',
                ],
            ],
        ]);
        foreach ($this->users as $user) {
            /** @var \App\User $user */
            $user = $user->fresh();
            $this->seeJsonContains([
                'id' => $user->id,
                'email' => $user->email,
                'firstName' => $user->firstName,
                'middleName' => $user->middleName,
                'lastName' => $user->lastName,
                'locale' => $user->locale->id,
                'timezone' => $user->timezone,
            ]);
        }
    }
}
