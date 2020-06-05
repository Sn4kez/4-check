<?php

use App\Group;
use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Ramsey\Uuid\Uuid;

class CreateGroupTest extends TestCase
{
    use DatabaseMigrations;

    public function provideInvalidAccessData()
    {
        return [
            [null, 'random', Response::HTTP_UNAUTHORIZED],
            [null, 'company', Response::HTTP_UNAUTHORIZED],
            [self::$USER, 'random', Response::HTTP_NOT_FOUND],
            [self::$USER, 'company', Response::HTTP_FORBIDDEN],
            [self::$OTHER_ADMIN, 'company', Response::HTTP_FORBIDDEN],
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
        if ($companyKey === 'company') {
            $uri = '/companies/' . $this->company->id . '/groups';
        } else {
            $uri = '/companies/' . Uuid::uuid4()->toString() . '/groups';
        }
        $this->json('POST', $uri);
        $this->seeStatusCode($statusCode);
        $this->seeHeader('Content-Type', 'application/json');
    }

    public function provideValidAccessData()
    {
        return [
            [self::$ADMIN, 'logo.jpg'],
            [self::$SUPER_ADMIN, 'logo.jpg'],
        ];
    }

    /**
     * @param string $userKey
     * @dataProvider provideValidAccessData
     */
    public function testValidAccess($userKey, $image)
    {
        $user = $this->getUser($userKey);
        $this->actingAs($user);

        $group1 = factory(Group::class)->make();
        $group2 = factory(Group::class)->make();

        $fileLocation = sprintf("%s/files/%s", dirname(__FILE__), $image);
        $sourceBase64 = base64_encode(file_get_contents($fileLocation));

        $this->json('POST', '/companies/' . $this->company->id . '/groups', [
            'name' => $group1->name,
            'source_b64' => $sourceBase64
        ]);

        $this->seeStatusCode(Response::HTTP_CREATED);
        $this->seeHeader('Content-Type', 'application/json');
        $this->seeJsonStructure([
            'data' => [
                'id',
                'name',
                'image'
            ],
        ])->seeJsonNotNull([
            'data' => [
                'id',
                'name',
                'image'
            ],
        ])->seeJsonContains([
            'name' => $group1->name,
        ]);

        $this->json('POST', '/companies/' . $this->company->id . '/groups', [
            'name' => $group2->name,
            'source_b64' => null
        ]);

        $this->seeStatusCode(Response::HTTP_CREATED);
        $this->seeHeader('Content-Type', 'application/json');

        $this->seeJsonStructure([
            'data' => [
                'id',
                'name',
                'image'
            ],
        ])->seeJsonNotNull([
            'data' => [
                'id',
                'name'
            ],
        ])->seeJsonContains([
            'name' => $group2->name,
        ]);

        $this->seeInDatabase('groups', [
            'companyId' => $this->company->id,
            'name' => $group1->name,
        ])->seeInDatabase('groups', [
            'companyId' => $this->company->id,
            'name' => $group2->name,
        ]);
        $this->assertCount(2, $this->company->groups);
    }

    public function provideInvalidEntities()
    {
        return [
            ['name', null],
            ['name', 123],
            ['name', str_repeat('Long', 32) . 'Name'],
        ];
    }

    /**
     * @param string $attribute
     * @param string $value
     * @dataProvider provideInvalidEntities
     */
    public function testInvalidEntities($attribute, $value)
    {
        $group = factory(Group::class)->make();
        $data = array_merge([
            'name' => $group->name,
        ], [$attribute => $value]);
        $this->actingAs($this->admin);
        $this->json(
            'POST',
            '/companies/' . $this->company->id . '/groups',
            $data);
        $this->seeStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->seeHeader('Content-Type', 'application/json');
    }
}
