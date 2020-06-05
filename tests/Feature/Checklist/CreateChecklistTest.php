<?php

use App\Checklist;
use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Ramsey\Uuid\Uuid;

class CreateChecklistTest extends TestCase {
    use DatabaseMigrations;

    private $arr;

    public function provideInvalidAccessData() {
        return [
            [null, Response::HTTP_UNAUTHORIZED],
        ];
    }

    /**
     * @param string $userKey
     * @param int $statusCode
     * @dataProvider provideInvalidAccessData
     */
    public function testInvalidAccess($userKey, $statusCode) {
        if ($userKey != null) {
            $user = $this->getUser($userKey);
            $this->actingAs($user);
        }
        $this->json('POST', '/checklists');
        $this->seeStatusCode($statusCode);
        $this->seeHeader('Content-Type', 'application/json');
    }

    public function provideValidAccessData() {
        return [
            [self::$ADMIN, false],
            [self::$SUPER_ADMIN, false],
            [self::$ADMIN, true],
            [self::$SUPER_ADMIN, true],
        ];
    }

    /**
     * @param string $userKey
     * @param $parent
     * @dataProvider provideValidAccessData
     */
    public function testValidAccess($userKey, $parent) {
        $user = $this->getUser($userKey);
        $this->actingAs($user);
        $checklist = factory(Checklist::class)->make();

        $r = $this->json('POST', '/checklists', [
            'name' => $checklist->name,
            'parentId' => $parent === true ? $this->company->directory->id : null,
            'numberQuestions' => $checklist->numberQuestions,
            'approvers' => [
                [
                    'objectType' => 'user',
                    'objectId' => $user->id,
                ]
            ],
        ]);

        $this->seeStatusCode(Response::HTTP_CREATED);
        $this->seeHeader('Content-Type', 'application/json');
        $this->seeJsonStructure([
            'data' => [
                'id',
                'name',
                'description',
                'parentEntryId',
                'numberQuestions',
                'chooseRandom'
            ],
        ])->seeJsonNotNull([
            'data' => array_merge(['id'], $parent === true ? ['parentEntryId'] : [])
        ])->seeJsonContains([
            'name' => $checklist->name,
            'numberQuestions' => $checklist->numberQuestions,
        ]);
        $this->seeInDatabase('checklists', [
            'name' => $checklist->name,
            'numberQuestions' => $checklist->numberQuestions,
        ]);
    }

    public function provideValidEntities() {
        return [
            ['description', 'description', true],
            ['description', 'description', false],
        ];
    }

    /**
     * @param $attribute
     * @param $value
     * @param $parent
     * @dataProvider provideValidEntities
     */

    public function testValidEntities($attribute, $value, $parent) {
        $checklist = factory(Checklist::class)->make();
        $data = array_merge([
            'name' => $checklist->name,
            'parentId' => $parent === true ? $this->company->directory->id : null,
            'numberQuestions' => $checklist->numberQuestions,
        ], [$attribute => $value]);
        $this->actingAs($this->admin);
        $this->json('POST', '/checklists', $data);
        $this->seeStatusCode(Response::HTTP_CREATED);
        $this->seeHeader('Content-Type', 'application/json');
        $this->seeJsonStructure([
            'data' => [
                $attribute,
            ],
        ])->seeJsonContains([
            $attribute => $value,
        ]);
        $this->seeInDatabase('checklists', [
            'name' => $checklist->name,
            $attribute => $value,
        ]);
    }

    public function provideInvalidEntities() {
        $this->arr = [
            ['name', null],
            ['name', 123],
            ['name', str_repeat('Long', 32) . 'Name'],
            ['description', 123],
            ['parentId', Uuid::uuid4()->toString()],
        ];
        return $this->arr;
    }

    /**
     * @param string $attribute
     * @param string $value
     * @dataProvider provideInvalidEntities
     */
    public function testInvalidEntities($attribute, $value) {
        $checklist = factory(Checklist::class)->make();
        $data = array_merge([
            'name' => $checklist->name,
            'parentId' => $this->company->directory->id,
            'numberQuestions' => $checklist->numberQuestions,
        ], [$attribute => $value]);
        $this->actingAs($this->admin);
        $this->json('POST', '/checklists', $data);
        $this->seeStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->seeHeader('Content-Type', 'application/json');
    }
}
