<?php

use App\Address;
use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Ramsey\Uuid\Uuid;

class DeleteMultipleDirectoriesTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * @var \App\Directory
     */
    protected $directories;

    public function setUp()
    {
        parent::setUp();
        $directory = $this->makeFakeDirectory();
        $directory->save();
        $this->company->directory->entry($directory)->save();

        $directory2 = $this->makeFakeDirectory();
        $directory2->save();
        $this->company->directory->entry($directory2)->save();

        $this->directories = [
        	$directory->id,
        	$directory2->id,
        ];
    }

    public function provideInvalidAccessData()
    {
        return [
            [null, Response::HTTP_UNAUTHORIZED],
            [self::$USER, Response::HTTP_FORBIDDEN],
            [self::$OTHER_ADMIN,  Response::HTTP_FORBIDDEN],
        ];
    }

    /**
     * @param string $userKey
     * @param int $statusCode
     * @dataProvider provideInvalidAccessData
     */
    public function testInvalidAccess($userKey, $statusCode)
    {
        if ($userKey != null) {
            $user = $this->getUser($userKey);
            $this->actingAs($user);
        }

        $this->json('DELETE', '/directories', [
        	'directories' => $this->directories
        ]);
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
        $this->json('DELETE', '/directories', [
        	'directories' => $this->directories,
        ]);
        $this->seeStatusCode(Response::HTTP_NO_CONTENT);
        foreach($this->directories as $id) {
        	$this->assertSoftDeleted(
            'directories',
            ['id' => $id],
            Address::DELETED_AT);
        }
    }
}
