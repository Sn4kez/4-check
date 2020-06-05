<?php

use App\Directory;
use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Ramsey\Uuid\Uuid;

class CreateDirectoryTest extends TestCase
{
    use DatabaseMigrations;

    public function provideInvalidAccessData()
    {
        return [
            [null, Response::HTTP_UNAUTHORIZED],
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
        $this->json('POST', '/directories');
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
     * @param string $userKey
     * @dataProvider provideValidAccessData
     */
    public function testValidAccess($userKey)
    {
        $user = $this->getUser($userKey);
        $this->actingAs($user);
        $directory = factory(Directory::class)->make();
        $this->json('POST', '/directories', [
            'name' => $directory->name,
            'parentId' => $this->company->directory->id,
        ]);
        $this->seeStatusCode(Response::HTTP_CREATED);
        $this->seeHeader('Content-Type', 'application/json');
        $this->seeJsonStructure([
            'data' => [
                'id',
                'name',
                'description',
                'parentEntryId',
            ],
        ])->seeJsonNotNull([
            'data' => [
                'id',
                'parentEntryId',
            ],
        ])->seeJsonContains([
            'name' => $directory->name,
        ]);
        $this->seeInDatabase('directories', [
            'name' => $directory->name,
        ]);
    }

    public function provideValidEntities()
    {
        return [
            ['description', 'description'],
        ];
    }

    /**
     * @param $attribute
     * @param $value
     * @dataProvider provideValidEntities
     */
    public function testValidEntities($attribute, $value)
    {
        $directory = factory(Directory::class)->make();
        $data = array_merge([
            'name' => $directory->name,
            'parentId' => $this->company->directory->id,
        ], [$attribute => $value]);
        $this->actingAs($this->admin);
        $this->json('POST', '/directories', $data);
        $this->seeStatusCode(Response::HTTP_CREATED);
        $this->seeHeader('Content-Type', 'application/json');
        $this->seeJsonStructure([
            'data' => [
                $attribute,
            ],
        ])->seeJsonContains([
            $attribute => $value,
        ]);
        $this->seeInDatabase('directories', [
            'name' => $directory->name,
            $attribute => $value,
        ]);
    }

    public function provideInvalidEntities()
    {
        return [
            ['name', null],
            ['name', 123],
            ['name', str_repeat('Long', 32) . 'Name'],
            ['description', 123],
            ['parentId', null],
            ['parentId', Uuid::uuid4()->toString()],
        ];
    }

    /**
     * @param string $attribute
     * @param string $value
     * @dataProvider provideInvalidEntities
     */
    public function testInvalidEntities($attribute, $value)
    {
        $directory = factory(Directory::class)->make();
        $data = array_merge([
            'name' => $directory->name,
            'parentId' => $this->company->directory->id,
        ], [$attribute => $value]);
        $this->actingAs($this->admin);
        $this->json('POST', '/directories', $data);
        $this->seeStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->seeHeader('Content-Type', 'application/json');
    }
}
