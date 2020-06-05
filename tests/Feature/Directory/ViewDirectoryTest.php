<?php

use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Ramsey\Uuid\Uuid;

class ViewDirectoryTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * @var \App\Directory
     */
    protected $directory;

    /**
     * @var \App\Directory
     */
    protected $parent1;

    /**
     * @var \App\Directory
     */
    protected $parent2;

    public function setUp()
    {
        parent::setUp();
        $this->parent1 = $this->makeFakeDirectory(['with_description']);
        $this->parent1->save();
        $this->company->directory->entry($this->parent1)->save();
        $this->parent2 = $this->makeFakeDirectory(['with_description']);
        $this->parent2->save();
        $this->parent1->entry($this->parent2)->save();
        $this->directory = $this->makeFakeDirectory(['with_description']);
        $this->directory->save();
        $this->parent2->entry($this->directory)->save();
    }

    public function provideInvalidAccessData()
    {
        return [
            [null, 'random', Response::HTTP_UNAUTHORIZED],
            [null, 'directory', Response::HTTP_UNAUTHORIZED],
            [self::$USER, 'random', Response::HTTP_NOT_FOUND],
            [self::$USER, 'directory', Response::HTTP_FORBIDDEN],
            [self::$ADMIN, 'random', Response::HTTP_NOT_FOUND],
            [self::$OTHER_ADMIN, 'directory', Response::HTTP_FORBIDDEN],
        ];
    }

    /**
     * @param string $userKey
     * @param boolean $directoriesKey
     * @param int $statusCode
     * @dataProvider provideInvalidAccessData
     */
    public function testInvalidAccess($userKey, $directoriesKey, $statusCode)
    {
        if ($userKey != null) {
            $user = $this->getUser($userKey);
            $this->actingAs($user);
        }
        if ($directoriesKey === 'directory') {
            $uri = '/directories/' . $this->directory->id;
        } else {
            $uri = '/directories/' . Uuid::uuid4()->toString();
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
        $this->json('GET', '/directories/' . $this->directory->id);
        $this->seeStatusCode(Response::HTTP_OK);
        $this->seeHeader('Content-Type', 'application/json');
        $this->seeJsonStructure([
            'data' => [
                'id',
                'path' => [
                    [
                        'id',
                        'name',
                    ],
                ],
                'name',
                'description',
            ],
        ])->seeJsonContains([
            'id' => $this->directory->id,
            'path' => [
                [
                    'id' => $this->parent1->id,
                    'name' => $this->parent1->name,
                ],
                [
                    'id' => $this->parent2->id,
                    'name' => $this->parent2->name,
                ]
            ],
            'name' => $this->directory->name,
            'description' => $this->directory->description,
        ]);
    }

    /**
     * @param $userKey
     * @dataProvider provideValidAccessData
     */
    public function testViewRootDirectory($userKey)
    {
        $user = $this->getUser($userKey);
        $this->actingAs($user);
        $this->json('GET', '/directories/' . $this->company->directory->id);
        $this->seeStatusCode(Response::HTTP_OK);
        $this->seeHeader('Content-Type', 'application/json');
        $this->seeJsonStructure([
            'data' => [
                'id',
                'path' => [],
                'name',
                'description',
            ],
        ])->seeJsonContains([
            'id' => $this->company->directory->id,
            'path' => [],
            'name' => $this->company->directory->name,
            'description' => $this->company->directory->description,
        ]);
    }
}
