<?php

use App\Checklist;
use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Ramsey\Uuid\Uuid;

class DeleteMultipleChecklistsTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * @var \App\Checklist
     */
    protected $checklists;

    public function setUp()
    {
        parent::setUp();
        $checklist = $this->makeFakeChecklist(['with_description']);
        $checklist->save();
        $this->company->directory->entry($checklist)->save();

        $checklist2 = $this->makeFakeChecklist(['with_description']);
        $checklist2->save();
        $this->company->directory->entry($checklist2)->save();

        $this->checklists = [
        	$checklist->id,
        	$checklist2->id
        ];
    }

    public function provideInvalidAccessData()
    {
        return [
            [null, Response::HTTP_UNAUTHORIZED],
            [self::$USER, Response::HTTP_FORBIDDEN],
            [self::$OTHER_ADMIN, Response::HTTP_FORBIDDEN],
        ];
    }

    /**
     * @param string $userKey
     * @param boolean $checklistKey
     * @param int $statusCode
     * @dataProvider provideInvalidAccessData
     */
    public function testInvalidAccess($userKey, $statusCode)
    {
        if ($userKey != null) {
            $user = $this->getUser($userKey);
            $this->actingAs($user);
        }
   
        $this->json('DELETE', '/checklists', [
        	'checklists' => $this->checklists
        ]);
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
        $this->json('DELETE', '/checklists', [
        	'checklists' => $this->checklists
        ]);
        $this->seeStatusCode(Response::HTTP_NO_CONTENT);
        foreach($this->checklists as $id) {
        	$this->assertSoftDeleted(
            'checklists',
            ['id' => $id],
            Checklist::DELETED_AT);
        }
        
    }
}
