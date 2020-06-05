<?php

use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Ramsey\Uuid\Uuid;

class UpdateDirectoryTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * @var \App\Directory $directory
     */
    protected $directory;

    /**
     * @var \App\Directory $directory
     */
    protected $otherDirectory;

    public function setUp()
    {
        parent::setUp();
        $this->directory = $this->makeFakeDirectory(['with_description']);
        $this->directory->save();
        $this->company->directory->entry($this->directory)->save();
        $this->otherDirectory = $this->makeFakeDirectory(['with_description']);
        $this->otherDirectory->save();
        $this->company->directory->entry($this->otherDirectory)->save();
    }

    public function provideInvalidAccessData()
    {
        return [
            [null, 'random', Response::HTTP_UNAUTHORIZED],
            [null, 'directory', Response::HTTP_UNAUTHORIZED],
            [self::$USER, 'random', Response::HTTP_NOT_FOUND],
            [self::$USER, 'directory', Response::HTTP_FORBIDDEN],
            [self::$ADMIN, 'random', Response::HTTP_NOT_FOUND],
            [self::$ADMIN, 'root_directory', Response::HTTP_FORBIDDEN],
            [self::$OTHER_ADMIN, 'directory', Response::HTTP_FORBIDDEN],
        ];
    }

    /**
     * @param string $userKey
     * @param boolean $directoryKey
     * @param int $statusCode
     * @dataProvider provideInvalidAccessData
     */
    public function testInvalidAccess($userKey, $directoryKey, $statusCode)
    {
        if ($userKey != null) {
            $user = $this->getUser($userKey);
            $this->actingAs($user);
        }
        if ($directoryKey === 'directory') {
            $uri = '/directories/' . $this->directory->id;
        }else if ($directoryKey === 'root_directory') {
            $uri = '/directories/' . $this->company->directory->id;
        } else {
            $uri = '/directories/' . Uuid::uuid4()->toString();
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
            [self::$ADMIN, 'description', 'description', null],
            [self::$ADMIN, 'description', 'description', 'description'],
            [self::$SUPER_ADMIN, 'description', 'description', null],
            [self::$SUPER_ADMIN, 'description', 'description', 'description'],
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
        if ($value == 'otherDirectory') {
            $value = $this->otherDirectory->id;
        }
        $data = [$attribute => $value];
        $this->json('PATCH', '/directories/' . $this->directory->id, $data);
        $this->seeStatusCode(Response::HTTP_NO_CONTENT);
        $this->seeInDatabase('directories', [
            'id' => $this->directory->id,
            $dbAttribute => $value,
        ]);
    }

    public function provideInvalidEntities()
    {
        return [
            [self::$ADMIN, 'name', null],
            [self::$ADMIN, 'name', 123],
            [self::$ADMIN, 'name',  str_repeat('Long', 32) . 'Name'],
            [self::$ADMIN, 'description', 123],
            [self::$ADMIN, 'parentId', null],
            [self::$ADMIN, 'parentId', Uuid::uuid4()->toString()],
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
        $this->json('PATCH', '/directories/' . $this->directory->id, $data);
        $this->seeStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->seeHeader('Content-Type', 'application/json');
    }
}
