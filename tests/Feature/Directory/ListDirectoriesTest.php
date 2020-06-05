<?php

use App\Directory;
use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Ramsey\Uuid\Uuid;

class ListDirectoriesTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * @var array
     */
    protected $directories;

    /**
     * @var array
     */
    protected $checklists;

    public function setUp()
    {
        parent::setUp();
        $this->directories = [
            $this->makeFakeDirectory(),
            $this->makeFakeDirectory(),
        ];
        $this->checklists = [
            $this->makeFakeChecklist(),
            $this->makeFakeChecklist(),
        ];
        foreach ($this->directories as $directory) {
            $directory->save();
            $this->company->directory->entry($directory)->save();
        }
        foreach ($this->checklists as $checklist) {
            $checklist->save();
            $this->company->directory->entry($checklist)->save();
        }
    }

    public function provideInvalidAccessData()
    {
        return [
            [null, 'random', Response::HTTP_UNAUTHORIZED],
            [null, 'directory', Response::HTTP_UNAUTHORIZED],
            [self::$OTHER_ADMIN, 'directory', Response::HTTP_FORBIDDEN],
        ];
    }

    /**
     * @param string $userKey
     * @param string $directoryKey
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
            $uri = '/directories/' . $this->company->directory->id . '/entries';
        } else {
            $uri = '/directories/' . Uuid::uuid4()->toString() . '/entries';
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
            [self::$USER, 'directory', false]
        ];
    }

    /**
     * @param $userKey
     * @param $companyKey
     * @param $expectEmpty
     * @dataProvider provideValidAccessData
     */
    public function testValidAccess($userKey, $companyKey, $expectEmpty)
    {
        $user = $this->getUser($userKey);
        $this->actingAs($user);
        if ($companyKey === 'directory') {
            $uri = '/directories/' . $this->company->directory->id . '/entries';
        } else {
            $uri = '/directories/' . $this->otherCompany->directory->id .
                '/entries';
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
                        'parentId',
                        'objectType',
                        'object' => [
                            'id',
                            'name',
                            'description',
                        ],
                    ],
                    [
                        'id',
                        'parentId',
                        'objectType',
                        'object' => [
                            'id',
                            'name',
                            'description',
                        ],
                    ],
                    [
                        'id',
                        'parentId',
                        'objectType',
                        'object' => [
                            'id',
                            'name',
                            'description',
                        ],
                    ],
                    [
                        'id',
                        'parentId',
                        'objectType',
                        'object' => [
                            'id',
                            'name',
                            'description',
                        ],
                    ],
                ],
            ]);
            foreach ($this->directories as $directory) {
                $this->seeJsonContains([
                    'parentId' => $directory->parentEntry->parent->id,
                    'objectType' => 'directory',
                    'object' => [
                        'id' => $directory->id,
                        'name' => $directory->name,
                        'description' => $directory->description,
                    ]
                ]);
            }
            foreach ($this->checklists as $checklist) {
                $this->seeJsonContains([
                    'parentId' => $checklist->parentEntry->parent->id,
                    'objectType' => 'directory',
                    'object' => [
                        'id' => $checklist->id,
                        'name' => $checklist->name,
                        'description' => $checklist->description,
                    ]
                ]);
            }
        }
    }
}
