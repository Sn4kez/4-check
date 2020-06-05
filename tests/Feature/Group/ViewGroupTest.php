<?php

use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Ramsey\Uuid\Uuid;
use App\Media;

class ViewGroupTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * @var \App\Group $group
     */
    protected $group;

    private $mediaBase64ContentCheckString;

    public function setUp()
    {
        parent::setUp();

        $uploadFeedback = Media::uploadUnitTestTestImage(sprintf("%s/files/logo.jpg", dirname(__FILE__)));
        $this->mediaBase64ContentCheckString = $uploadFeedback["base64Content"];
        $mediaName = $uploadFeedback["mediaName"];

        $this->group = $this->makeFakeGroup();
        $this->group->image = $mediaName;
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
     * @param boolean $groupKey
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
        $this->json('GET', '/groups/' . $this->group->id);
        $this->seeStatusCode(Response::HTTP_OK);
        $this->seeHeader('Content-Type', 'application/json');
        $this->seeJsonStructure([
            'data' => [
                'id',
                'name',
                'image'
            ],
        ])->seeJsonContains([
            'id' => $this->group->id,
            'name' => $this->group->name,
            'image' => $this->mediaBase64ContentCheckString
        ]);
    }
}
