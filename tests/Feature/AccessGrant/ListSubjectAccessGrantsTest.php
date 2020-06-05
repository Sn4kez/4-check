<?php

use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Ramsey\Uuid\Uuid;

class ListSubjectAccessGrantsTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * @var array
     */
    protected $grants;

    public function setUp()
    {
        parent::setUp();
        $this->grants = [
            $this->makeFakeAccessGrant(),
            $this->makeFakeAccessGrant(),
        ];
        $directory = $this->makeFakeDirectory();
        $directory->save();
        $this->company->directory->entry($directory)->save();
        $this->grants[0]->subject()->associate($this->user);
        $this->grants[0]->object()->associate($this->company->directory);
        $this->grants[0]->save();
        $this->grants[1]->subject()->associate($this->user);
        $this->grants[1]->object()->associate($directory);
        $this->grants[1]->save();
    }

    public function provideInvalidAccessData()
    {
        return [
            [null, 'random', Response::HTTP_UNAUTHORIZED],
            [null, 'user', Response::HTTP_UNAUTHORIZED],
            [self::$USER, 'random', Response::HTTP_NOT_FOUND],
            [self::$USER, 'user', Response::HTTP_FORBIDDEN],
            [self::$OTHER_ADMIN, 'user', Response::HTTP_FORBIDDEN],
        ];
    }

    /**
     * @param string $userKey
     * @param string $subjectKey
     * @param int $statusCode
     * @dataProvider provideInvalidAccessData
     * @throws Exception
     */
    public function testInvalidAccess($userKey, $subjectKey, $statusCode)
    {
        if ($userKey != null) {
            $user = $this->getUser($userKey);
            $this->actingAs($user);
        }
        if ($subjectKey === 'user') {
            $uri = '/users/' . $this->user->id . '/grants';
        } else {
            $uri = '/users/' . Uuid::uuid4()->toString() . '/grants';
        }
        $this->json('GET', $uri);
        $this->seeStatusCode($statusCode);
        $this->seeHeader('Content-Type', 'application/json');
    }

    public function provideValidAccessData()
    {
        return [
            [self::$ADMIN, 'user', false],
            [self::$SUPER_ADMIN, 'user', false],
            [self::$OTHER_ADMIN, 'otherUser', true],
        ];
    }

    /**
     * @param $userKey
     * @param $subjectKey
     * @param $expectEmpty
     * @dataProvider provideValidAccessData
     */
    public function testValidAccess($userKey, $subjectKey, $expectEmpty)
    {
        $user = $this->getUser($userKey);
        $this->actingAs($user);
        if ($subjectKey === 'user') {
            $uri = '/users/' . $this->user->id . '/grants';
        } else {
            $uri = '/users/' . $this->otherUser->id . '/grants';
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
                        'id',
                        'subjectId',
                        'subjectType',
                        'objectId',
                        'objectType',
                        'objectName',
                        'view',
                        'index',
                        'update',
                        'delete',
                    ],
                    [
                        'id',
                        'subjectId',
                        'subjectType',
                        'objectId',
                        'objectType',
                        'objectName',
                        'view',
                        'index',
                        'update',
                        'delete',
                    ],
                ],
            ]);

            foreach ($this->grants as $grant) {
                $this->seeJsonContains([
                    'id' => $grant->id,
                    'subjectId' => $grant->subject->id,
                    'subjectType' => $grant->subjectType,
                    'objectId' => $grant->object->id,
                    'objectType' => $grant->objectType,
                    'view' => $grant->view,
                    'index' => $grant->index,
                    'update' => $grant->update,
                    'delete' => $grant->delete,
                ]);
            }
        }
    }
}
