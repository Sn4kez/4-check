<?php

use App\LocationState;
use App\Company;
use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;

class CreateLocationStateTest extends TestCase
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
        $this->json('POST', '/locations/states');
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
        $locationState = factory(LocationState::class)->make();
        $locationState->company()->associate($this->company);

        $this->json('POST', '/locations/states', [
            'name' => $locationState->name,
            'company' => $locationState->company->id
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
            'name' => $locationState->name,
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

        $locationState = factory(LocationState::class)->make();
        $locationState->company()->associate($this->company);

        $data = array_merge([
            'name' => $locationState->name,
            'company' => $locationState->company->id
        ], [$attribute => $value]);

        $this->json('POST', '/locations/states', $data);
        $this->seeStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->seeHeader('Content-Type', 'application/json');
    }
}
