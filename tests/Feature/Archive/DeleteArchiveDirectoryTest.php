<?php

use App\Address;
use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Ramsey\Uuid\Uuid;

class DeleteArchiveDirectoriesTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * @var \App\Directory
     */
    protected $directory;

    public function setUp()
    {
        parent::setUp();
        $this->directory = $this->makeFakeArchiveDirectory();
        $this->directory->company()->associate($this->directory);
        $this->directory->save();
        $this->company->archive->entry($this->directory)->save();
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
            $uri = '/archives/' . $this->directory->id;
        }else if ($directoryKey === 'root_directory') {
            $uri = '/archives/' . $this->company->archive->id;
        } else {
            $uri = '/archives/' . Uuid::uuid4()->toString();
        }
        $this->json('DELETE', $uri);
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
        $this->json('DELETE', '/archives/' . $this->directory->id);
        $this->seeStatusCode(Response::HTTP_NO_CONTENT);
    }
}
