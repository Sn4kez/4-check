<?php

use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Ramsey\Uuid\Uuid;

class ListObjectAccessGrantsTest extends TestCase
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
        $this->grants[0]->subject()->associate($this->user);
        $this->grants[0]->object()->associate($this->company->directory);
        $this->grants[0]->save();
        $this->grants[1]->subject()->associate($this->admin);
        $this->grants[1]->object()->associate($this->company->directory);
        $this->grants[1]->save();
    }

    public function provideInvalidAccessData()
    {
        return [
            [null, 'random', Response::HTTP_UNAUTHORIZED],
            [null, 'directory', Response::HTTP_UNAUTHORIZED],
            [self::$USER, 'random', Response::HTTP_NOT_FOUND],
            [self::$OTHER_ADMIN, 'directory', Response::HTTP_FORBIDDEN],
        ];
    }

    /**
     * @param string $userKey
     * @param string $objectKey
     * @param int $statusCode
     * @dataProvider provideInvalidAccessData
     */
    public function testInvalidAccess($userKey, $objectKey, $statusCode)
    {
        if ($userKey != null) {
            $user = $this->getUser($userKey);
            $this->actingAs($user);
        }
        if ($objectKey === 'directory') {
            $uri = '/directories/' . $this->company->directory->id . '/grants';
        } else {
            $uri = '/directories/' . Uuid::uuid4()->toString() . '/grants';
        }
        $this->json('GET', $uri);
        $this->seeStatusCode($statusCode);
        $this->seeHeader('Content-Type', 'application/json');
    }

    public function provideValidAccessData()
    {
        return [
            [self::$ADMIN, 'directory', false],
            [self::$SUPER_ADMIN, 'directory', false],
            [self::$OTHER_ADMIN, 'otherDirectory', true],
        ];
    }

    /**
     * @param $userKey
     * @param $objectKey
     * @param $expectEmpty
     * @dataProvider provideValidAccessData
     */
    public function testValidAccess($userKey, $objectKey, $expectEmpty)
    {
        $user = $this->getUser($userKey);
        $this->actingAs($user);
        if ($objectKey === 'directory') {
            $uri = '/directories/' . $this->company->directory->id . '/grants';
        } else {
            $uri = '/directories/' . $this->otherCompany->directory->id .
                '/grants';
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
