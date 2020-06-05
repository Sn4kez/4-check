<?php

use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Ramsey\Uuid\Uuid;

class ListSectionsTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * @var \App\Checklist
     */
    protected $checklist;

    /**
     * @var \App\Checklist
     */
    protected $otherChecklist;

    /**
     * @var array
     */
    protected $sections;

    public function setUp()
    {
        parent::setUp();
        $this->checklist = $this->makeFakeChecklist(['with_description']);
        $this->checklist->save();
        $this->company->directory->entry($this->checklist)->save();
        $this->otherChecklist = $this->makeFakeChecklist(['with_description']);
        $this->otherChecklist->save();
        $this->otherCompany->directory->entry($this->otherChecklist)->save();
        $this->sections = [
            $this->makeFakeSection(),
            $this->makeFakeSection(),
        ];
        foreach ($this->sections as $section) {
            $section->save();
            $this->checklist->entry($section)->save();
        }
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
        $this->json('GET', $uri);
        $this->seeStatusCode($statusCode);
        $this->seeHeader('Content-Type', 'application/json');
    }

    public function provideValidAccessData()
    {
        return [
            [self::$ADMIN, 'checklist', false],
            [self::$SUPER_ADMIN, 'checklist', false],
            [self::$OTHER_ADMIN, 'otherChecklist', true],
        ];
    }

    /**
     * @param $userKey
     * @param $checklistKey
     * @param $expectEmpty
     * @dataProvider provideValidAccessData
     */
    public function testValidAccess($userKey, $checklistKey, $expectEmpty)
    {
        $user = $this->getUser($userKey);
        $this->actingAs($user);
        if ($checklistKey === 'checklist') {
            $uri = '/checklists/' . $this->checklist->id . '/sections';
        } else {
            $uri = '/checklists/' . $this->otherChecklist->id . '/sections';
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
                        'title',
                        'index',
                    ],
                    [
                        'title',
                        'index',
                    ],
                ],
            ]);
            foreach ($this->sections as $section) {
                $this->seeJsonContains([
                    'title' => $section->title,
                    'index' => $section->index,
                ]);
            }
        }
    }
}
