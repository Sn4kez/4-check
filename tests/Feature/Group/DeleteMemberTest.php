<?php

use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Ramsey\Uuid\Uuid;

class DeleteMemberTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * @var \App\Group $group
     */
    protected $group;

    public function setUp()
    {
        parent::setUp();
        $this->group = $this->makeFakeGroup();
        $this->company->groups()->save($this->group);
        $this->group->users()->attach($this->user->id);
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
     * @param string $groupKey
     * @param int $statusCode
     * @dataProvider provideInvalidAccessData
     */
    public function testInvalidAccess($userKey, $groupKey, $statusCode)
    {
        if ($userKey != null) {
            $user = $this->getUser($userKey);
            $this->actingAs($user);
        }
        if ($groupKey === 'group') {
            $uri = '/groups/' . $this->group->id . '/members';
        } else {
            $uri = '/groups/' . Uuid::uuid4()->toString() . '/members';
        }
        $this->json('PATCH', $uri);
        $this->seeStatusCode($statusCode);
        $this->seeHeader('Content-Type', 'application/json');
    }

    public function provideValidEntities()
    {
        return [
            [self::$ADMIN],
            [self::$SUPER_ADMIN],
        ];
    }

    /**
     * @param $userKey
     * @dataProvider provideValidEntities
     */
    public function testValidEntities($userKey)
    {
        $user = $this->getUser($userKey);
        $this->actingAs($user);
        $data = ['id' => $this->user->id];
        $this->json('DELETE', '/groups/' . $this->group->id . '/members', $data);
        $this->seeStatusCode(Response::HTTP_NO_CONTENT);
        $this->notSeeInDatabase('user_group', [
            'userId' => $this->user->id,
            'groupId' => $this->group->id,
        ]);
    }

    public function provideInvalidEntities()
    {
        return [
            [self::$ADMIN],
        ];
    }

    /**
     * @param $userKey
     * @dataProvider provideInvalidEntities
     */
    public function testInvalidEntities($userKey)
    {
        $user = $this->getUser($userKey);
        $this->actingAs($user);
        $data = ['id' => null];
        $this->json('PATCH', '/groups/' . $this->group->id . '/members', $data);
        $this->seeStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->seeHeader('Content-Type', 'application/json');
    }
}
