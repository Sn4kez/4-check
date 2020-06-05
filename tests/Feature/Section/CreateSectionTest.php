<?php

use App\Section;
use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Ramsey\Uuid\Uuid;

class CreateSectionTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * @var \App\Checklist
     */
    protected $checklist;

    public function setUp()
    {
        parent::setUp();
        $this->checklist = $this->makeFakeChecklist(['with_description']);
        $this->checklist->save();
        $this->company->directory->entry($this->checklist)->save();
    }

    public function provideInvalidAccessData()
    {
        return [
            [null, 'random', Response::HTTP_UNAUTHORIZED],
            [null, 'checklist', Response::HTTP_UNAUTHORIZED],
            [self::$USER, 'random', Response::HTTP_NOT_FOUND],
            [self::$USER, 'checklist', Response::HTTP_FORBIDDEN],
            [self::$OTHER_ADMIN, 'checklist', Response::HTTP_FORBIDDEN],
        ];
    }

    /**
     * @param string $userKey
     * @param string $checklistKey
     * @param int $statusCode
     * @dataProvider provideInvalidAccessData
     */
    public function testInvalidAccess($userKey, $checklistKey, $statusCode)
    {
        if ($userKey != null) {
            $user = $this->getUser($userKey);
            $this->actingAs($user);
        }
        if ($checklistKey === 'checklist') {
            $uri = '/checklists/' . $this->checklist->id . '/sections';
        } else {
            $uri = '/checklists/' . Uuid::uuid4()->toString() . '/sections';
        }
        $this->json('POST', $uri);
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
        $section1 = factory(Section::class)->make();
        $section2 = factory(Section::class)->make();
        $uri = '/checklists/' . $this->checklist->id . '/sections';
        $this->json('POST', $uri, [
            'title' => $section1->title,
            'index' => $section1->index,
        ]);
        $this->seeStatusCode(Response::HTTP_CREATED);
        $this->seeHeader('Content-Type', 'application/json');
        $this->seeJsonStructure([
            'data' => [
                'id',
                'title',
                'index',
            ],
        ])->seeJsonNotNull([
            'data' => [
                'id',
            ],
        ])->seeJsonContains([
            'title' => $section1->title,
            'index' => $section1->index,
        ]);
        $this->json('POST', $uri, [
            'title' => $section2->title,
            'index' => $section2->index,
        ]);
        $this->seeStatusCode(Response::HTTP_CREATED);
        $this->seeHeader('Content-Type', 'application/json');
        $this->seeJsonStructure([
            'data' => [
                'id',
                'title',
            ],
        ])->seeJsonNotNull([
            'data' => [
                'id',
            ],
        ])->seeJsonContains([
            'title' => $section2->title,
            'index' => $section2->index,
        ]);
        $this->seeInDatabase('sections', [
            'title' => $section1->title,
            'index' => $section1->index,
        ])->seeInDatabase('sections', [
            'title' => $section2->title,
            'index' => $section2->index,
        ]);
        $this->assertCount(2, $this->checklist->entries);
    }

    public function provideInvalidEntities()
    {
        return [
            ['title', null],
            ['title', 123],
            ['title', str_repeat('Long', 32) . 'Title'],
        ];
    }

    /**
     * @param string $attribute
     * @param string $value
     * @dataProvider provideInvalidEntities
     */
    public function testInvalidEntities($attribute, $value)
    {
        $group = factory(Section::class)->make();
        $data = array_merge([
            'title' => $group->name,
        ], [$attribute => $value]);
        $this->actingAs($this->admin);
        $uri = '/checklists/' . $this->checklist->id . '/sections';
        $this->json('POST', $uri, $data);
        $this->seeStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->seeHeader('Content-Type', 'application/json');
    }
}
