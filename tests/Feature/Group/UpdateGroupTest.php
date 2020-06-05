<?php

use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Ramsey\Uuid\Uuid;

class UpdateGroupTest extends TestCase
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
            $uri = '/groups/' . $this->group->id;
        } else {
            $uri = '/groups/' . Uuid::uuid4()->toString();
        }
        $this->json('PATCH', $uri);
        $this->seeStatusCode($statusCode);
        $this->seeHeader('Content-Type', 'application/json');
    }

    public function provideValidEntities()
    {
        return [
            [self::$ADMIN, 'name', 'name', 'name'],
            [self::$SUPER_ADMIN, 'name', 'name', 'name'],
            [self::$ADMIN, 'source_b64', 'source_b64', 'logo.jpg'],
            [self::$ADMIN, 'source_b64', 'source_b64', null],
        ];
    }

    /**
     * @param $userKey
     * @param $attribute
     * @param $dbAttribute
     * @param $value
     * @dataProvider provideValidEntities
     */
    public function testValidEntities($userKey, $attribute, $dbAttribute, $value)
    {
        $user = $this->getUser($userKey);
        $this->actingAs($user);

        if ($attribute === "source_b64") {
            $fileLocation = sprintf("%s/files/%s", dirname(__FILE__), $value);

            if (file_exists($fileLocation)) {
                $value = base64_encode(file_get_contents($fileLocation));
            }
        }

        $data = [$attribute => $value];

        $this->json('PATCH', '/groups/' . $this->group->id, $data);

        $this->seeStatusCode(Response::HTTP_NO_CONTENT);

        if($attribute !== 'source_b64') {
            $this->seeInDatabase('groups', [
                'id' => $this->group->id,
                $dbAttribute => $value,
            ]);
        }
    }

    public function provideInvalidEntities()
    {
        return [
            [self::$ADMIN, 'name', null],
            [self::$ADMIN, 'name', 123],
            [self::$ADMIN, 'name', str_repeat('Long', 32) . 'Name'],
        ];
    }

    /**
     * @param $userKey
     * @param $attribute
     * @param $value
     * @dataProvider provideInvalidEntities
     */
    public function testInvalidEntities($userKey, $attribute, $value)
    {
        $user = $this->getUser($userKey);
        $this->actingAs($user);
        $data = [$attribute => $value];
        $this->json('PATCH', '/groups/' . $this->group->id, $data);
        $this->seeStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->seeHeader('Content-Type', 'application/json');
    }
}
