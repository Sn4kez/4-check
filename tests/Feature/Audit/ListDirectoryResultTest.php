<?php

use App\Directory;
use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Ramsey\Uuid\Uuid;

class ListDirectoryResultTest extends TestCase
{
	use DatabaseMigrations;

	private $checklists;
	private $entryIds;

	private $directories;

    public function setUp()
    {
        parent::setUp();

        $this->directories = [
            $this->makeFakeDirectory(),
            $this->makeFakeDirectory()
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

        $this->entryIds = [];
        $this->root = $this->company->directory->id;

        foreach($this->company->directory->entries as $entry) {
        	$this->entryIds[] = $entry->objectId;
        }
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
     * @group test2
     */
    public function testValidAccess($userKey)
    {
        $user = $this->getUser($userKey);
        $this->actingAs($user);

        $this->json('GET', 'audits/directory/' . $this->entryIds[0]);
        $this->seeStatusCode(Response::HTTP_OK);
    }
}