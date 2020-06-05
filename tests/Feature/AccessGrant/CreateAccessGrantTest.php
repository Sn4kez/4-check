<?php

use App\AccessGrant;
use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Ramsey\Uuid\Uuid;

class CreateAccessGrantTest extends TestCase
{
    use DatabaseMigrations;

    public function provideInvalidAccessData()
    {
        return [
            [null, 'random', Response::HTTP_UNAUTHORIZED],
            [null, 'directory', Response::HTTP_UNAUTHORIZED],
            [self::$USER, 'random', Response::HTTP_NOT_FOUND],
            [self::$USER, 'directory', Response::HTTP_FORBIDDEN],
            [self::$OTHER_ADMIN, 'directory', Response::HTTP_FORBIDDEN],
        ];
    }

    /**
     * @param string $userKey
     * @param string $companyKey
     * @param int $statusCode
     * @dataProvider provideInvalidAccessData
     */
    public function testInvalidAccess($userKey, $companyKey, $statusCode)
    {
        if ($userKey != null) {
            $user = $this->getUser($userKey);
            $this->actingAs($user);
        }
        if ($companyKey === 'directory') {
            $uri = '/directories/' . $this->company->directory->id . '/grants';
        } else {
            $uri = '/directories/' . Uuid::uuid4()->toString() . '/grants';
        }
        $this->json('POST', $uri);
        $this->seeStatusCode($statusCode);
    }

    public function provideValidAccessData()
    {
        return [
            [self::$ADMIN],
            [self::$SUPER_ADMIN],
        ];
    }

    /**
     * @param string $userKey
     * @dataProvider provideValidAccessData
     */
    public function testValidAccess($userKey)
    {
        $user = $this->getUser($userKey);
        $this->actingAs($user);
        $grant = factory(AccessGrant::class)->make();
        $grant->object()->associate($this->company->directory);
        $grant->subject()->associate($this->user);
        $url = '/directories/' . $this->company->directory->id . '/grants';
        $this->json('POST', $url, [
            'subjectId' => $grant->subject->id,
            'view' => $grant->view,
            'index' => $grant->index,
            'update' => $grant->update,
            'delete' => $grant->delete,
        ]);
        $this->seeStatusCode(Response::HTTP_CREATED);
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
        ])->seeJsonNotNull([
            'data' => [
                'id',
            ],
        ])->seeJsonContains([
            'subjectId' => $grant->subject->id,
            'subjectType' => AccessGrant::SUBJECT_TYPE_USER,
            'objectId' => $grant->object->id,
            'objectType' => AccessGrant::OBJECT_TYPE_DIRECTORY,
            'view' => $grant->view,
            'index' => $grant->index,
            'update' => $grant->update,
            'delete' => $grant->delete,
        ]);
        $this->seeInDatabase('access_grants', [
            'subjectId' => $grant->subject->id,
            'objectId' => $grant->object->id,
            'view' => $grant->view,
            'index' => $grant->index,
            'update' => $grant->update,
            'delete' => $grant->delete,
        ]);
    }

    public function provideInvalidEntities()
    {
        return [
            ['subjectId', null],
            ['subjectId', Uuid::uuid4()->toString()],
            ['view', null],
            ['view', 123],
            ['index', null],
            ['index', 123],
            ['update', null],
            ['update', 123],
            ['delete', null],
            ['delete', 123],
        ];
    }

    /**
     * @param string $attribute
     * @param string $value
     * @dataProvider provideInvalidEntities
     */
    public function testInvalidEntities($attribute, $value)
    {
        $grant = factory(AccessGrant::class)->make();
        $grant->object()->associate($this->company->directory);
        $grant->subject()->associate($this->user);
        $data = array_merge([
            'subjectId' => $grant->subject->id,
            'view' => $grant->view,
            'index' => $grant->index,
            'update' => $grant->update,
            'delete' => $grant->delete,
        ], [$attribute => $value]);
        $this->actingAs($this->admin);
        $url = '/directories/' . $this->company->directory->id . '/grants';
        $this->json('POST', $url, $data);
        $this->seeStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->seeHeader('Content-Type', 'application/json');
    }

    public function testDuplicateAccessGrant()
    {
        $user = $this->getUser(self::$ADMIN);
        $this->actingAs($user);
        $grant = factory(AccessGrant::class)->make();
        $grant->object()->associate($this->company->directory);
        $grant->subject()->associate($this->user);
        $grant->save();
        $url = '/directories/' . $this->company->directory->id . '/grants';
        $this->json('POST', $url, [
            'subjectId' => $grant->subject->id,
            'view' => $grant->view,
            'index' => $grant->index,
            'update' => $grant->update,
            'delete' => $grant->delete,
        ]);
        $this->seeStatusCode(Response::HTTP_BAD_REQUEST);
        $this->seeHeader('Content-Type', 'application/json');
    }
}
