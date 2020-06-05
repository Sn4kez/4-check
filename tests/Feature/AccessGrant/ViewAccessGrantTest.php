<?php

use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Ramsey\Uuid\Uuid;

class ViewAccessGrantTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * @var \App\AccessGrant
     */
    protected $grant;

    public function setUp()
    {
        parent::setUp();
        $this->grant = $this->makeFakeAccessGrant();
        $this->grant->subject()->associate($this->user);
        $this->grant->object()->associate($this->company->directory);
        $this->grant->save();
    }

    public function provideInvalidAccessData()
    {
        return [
            [null, 'random', Response::HTTP_UNAUTHORIZED],
            [null, 'grant', Response::HTTP_UNAUTHORIZED],
            [self::$USER, 'random', Response::HTTP_NOT_FOUND],
            [self::$ADMIN, 'random', Response::HTTP_NOT_FOUND],
            [self::$OTHER_ADMIN, 'grant', Response::HTTP_FORBIDDEN],
        ];
    }

    /**
     * @param string $userKey
     * @param boolean $grantKey
     * @param int $statusCode
     * @dataProvider provideInvalidAccessData
     */
    public function testInvalidAccess($userKey, $grantKey, $statusCode)
    {
        if ($userKey != null) {
            $user = $this->getUser($userKey);
            $this->actingAs($user);
        }
        if ($grantKey === 'grant') {
            $uri = '/grants/' . $this->grant->id;
        } else {
            $uri = '/grants/' . Uuid::uuid4()->toString();
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
        $this->json('GET', '/grants/' . $this->grant->id);
        $this->seeStatusCode(Response::HTTP_OK);
        $this->seeHeader('Content-Type', 'application/json');
        $this->seeJsonStructure([
            'data' => [
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
        ])->seeJsonContains([
            'id' => $this->grant->id,
            'subjectId' => $this->grant->subject->id,
            'subjectType' => $this->grant->subjectType,
            'objectId' => $this->grant->object->id,
            'objectType' => $this->grant->objectType,
            'view' => $this->grant->view,
            'index' => $this->grant->index,
            'update' => $this->grant->update,
            'delete' => $this->grant->delete,
        ]);
    }
}
