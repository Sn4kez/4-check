<?php

use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Ramsey\Uuid\Uuid;
use App\ArchiveDirectory;

class RestoreChecklistTest extends TestCase
{
    use DatabaseMigrations;

    private $entryIds;

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
        $i = 0;

        foreach ($this->directories as $directory) {
        	$directory->save();
            $this->company->directory->entry($directory)->save();

            $tmp = new ArchiveDirectory();
            $tmp->id = Uuid::uuid4()->toString();
	        $tmp->name = $directory->name;
	        $tmp->description = $directory->description;
	        $tmp->icon = $directory->icon;
	        $tmp->company()->associate($directory->company);
	        $tmp->save();

	        $this->company->archive->entry($tmp)->save();

        }
        foreach ($this->checklists as $checklist) {
        	$checklist->save();
            $this->company->directory->entry($checklist)->save();
            $this->company->archive->entry($checklist)->save();
        }

        $this->entryIds = [];

        foreach($this->company->directory->entries as $entry) {
        	$entry->archived = 1;
        	$entry->save();
        }

        foreach($this->company->archive->entries as $entry) {
        	$this->entryIds[] = $entry->id;
        }
    }

    public function provideInvalidAccessData()
    {
        return [
            [null, Response::HTTP_UNAUTHORIZED],
            [self::$OTHER_ADMIN, Response::HTTP_FORBIDDEN],
        ];
    }

    /**
     * @param string $userKey
     * @param string $directoryKey
     * @param int $statusCode
     * @dataProvider provideInvalidAccessData
     */
    public function testInvalidAccess($userKey, $statusCode)
    {
        if ($userKey != null) {
            $user = $this->getUser($userKey);
            $this->actingAs($user);
        }
        $uri = '/directories/'. $this->entryIds[2] . '/restore';
        
        $this->json('PATCH', $uri, [
            'company' => $this->company->id
        ]);
        $this->seeStatusCode($statusCode);
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
        if ($userKey != null) {
            $user = $this->getUser($userKey);
            $this->actingAs($user);
        }
        $uri = '/directories/'. $this->entryIds[2] . '/restore';
        
        $this->json('PATCH', $uri, [
            'company' => $this->company->id
        ]);
        $this->seeStatusCode(Response::HTTP_NO_CONTENT);
    }
}