<?php

use App\TaskType;
use App\Company;
use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;

class CreateTaskTypeTest extends TestCase
{
    use DatabaseMigrations;

    public function provideInvalidAccessData()
    {
        return [
            [null, Response::HTTP_UNAUTHORIZED],
            [self::$USER, Response::HTTP_UNAUTHORIZED]
        ];
    }

    /**
     * @param string $userKey
     * @param string $userIdKey
     * @param int $statusCode
     * @dataProvider provideInvalidAccessData
     */
    public function testInvalidAccess($userKey, $statusCode)
    {
        $this->json('POST', '/tasks/types');
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
        $taskType = factory(TaskType::class)->make();
        $taskType->company()->associate($this->company);

        $this->json('POST', '/tasks/types', [
            'name' => $taskType->name,
            'company' => $taskType->company->id
        ]);
        $this->seeStatusCode(Response::HTTP_CREATED);
        $this->seeHeader('Content-Type', 'application/json');
        
        $this->seeJsonStructure([
            'data' => [
                'id',
                'name'
            ],
        ])->seeJsonNotNull([
            'data' => [
                'id',
                'name'
            ],
        ])->seeJsonContains([
            'name' => $taskType->name,
        ]);
    }

    public function provideInvalidEntities()
    {
        return [
            ['name', null],
            ['name', 123],
            ['name', str_repeat('123', 128)],
        ];
    }

    /**
     * @param string $attribute
     * @param string $value
     * @dataProvider provideInvalidEntities
     */
    public function testInvalidEntities($attribute, $value)
    {
        $this->actingAs($this->user);

        $taskType = factory(TaskType::class)->make();
        $taskType->company()->associate($this->company);

        $data = array_merge([
            'id' => $taskType->id,
            'company' => $taskType->company->id
        ], [$attribute => $value]);

        $this->json('POST', '/tasks/types', $data);
        $this->seeStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->seeHeader('Content-Type', 'application/json');
    }
}
