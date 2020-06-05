<?php

use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Ramsey\Uuid\Uuid;

class UpdateSectionTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * @var \App\Checklist
     */
    protected $checklist;

    /**
     * @var \App\Section
     */
    protected $section;

    public function setUp()
    {
        parent::setUp();
        $this->checklist = $this->makeFakeChecklist(['with_description']);
        $this->checklist->save();
        $this->company->directory->entry($this->checklist)->save();
        $this->section = $this->makeFakeSection();
        $this->section->save();
        $this->checklist->entry($this->section)->save();
    }

    public function provideInvalidAccessData()
    {
        return [
            [null, 'random', Response::HTTP_UNAUTHORIZED],
            [null, 'section', Response::HTTP_UNAUTHORIZED],
            [self::$USER, 'random', Response::HTTP_NOT_FOUND],
            [self::$USER, 'section', Response::HTTP_FORBIDDEN],
            [self::$ADMIN, 'random', Response::HTTP_NOT_FOUND],
            [self::$OTHER_ADMIN, 'section', Response::HTTP_FORBIDDEN],
        ];
    }

    /**
     * @param string $userKey
     * @param string $sectionKey
     * @param int $statusCode
     * @dataProvider provideInvalidAccessData
     */
    public function testInvalidAccess($userKey, $sectionKey, $statusCode)
    {
        if ($userKey != null) {
            $user = $this->getUser($userKey);
            $this->actingAs($user);
        }
        if ($sectionKey === 'section') {
            $uri = '/sections/' . $this->section->id;
        } else {
            $uri = '/sections/' . Uuid::uuid4()->toString();
        }
        $this->json('PATCH', $uri);
        $this->seeStatusCode($statusCode);
        $this->seeHeader('Content-Type', 'application/json');
    }

    public function provideValidEntities()
    {
        return [
            [self::$ADMIN, 'title', 'title', 'title'],
            [self::$SUPER_ADMIN, 'title', 'title', 'title'],
            [self::$ADMIN, 'index', 'index', '1234'],
            [self::$SUPER_ADMIN, 'index', 'index', '1234'],
        ];
    }

    /**
     * @param $userKey
     * @param $attribute
     * @param $dbAttribute
     * @param $value
     * @dataProvider provideValidEntities
     */
    public function testValidEntities($userKey, $attribute, $dbAttribute, $value)
    {
        $user = $this->getUser($userKey);
        $this->actingAs($user);
        $data = [$attribute => $value];
        $this->json('PATCH', '/sections/' . $this->section->id, $data);
        $this->seeStatusCode(Response::HTTP_NO_CONTENT);
        $this->seeInDatabase('sections', [
            'id' => $this->section->id,
            $dbAttribute => $value,
        ]);
    }

    public function provideInvalidEntities()
    {
        return [
            [self::$ADMIN, 'title', null],
            [self::$ADMIN, 'title', 123],
            [self::$ADMIN, 'title', str_repeat('Long', 32) . 'Title'],
            [self::$ADMIN, 'index', null],
            [self::$ADMIN, 'index', 123],
            [self::$ADMIN, 'index', str_repeat('Long', 32) . 'Index'],
        ];
    }

    /**
     * @param $userKey
     * @param $attribute
     * @param $value
     * @dataProvider provideInvalidEntities
     */
    public function testInvalidEntities($userKey, $attribute, $value)
    {
        $user = $this->getUser($userKey);
        $this->actingAs($user);
        $data = [$attribute => $value];
        $this->json('PATCH', '/sections/' . $this->section->id, $data);
        $this->seeStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->seeHeader('Content-Type', 'application/json');
    }
}
