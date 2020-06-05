<?php

use App\Directory;
use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Ramsey\Uuid\Uuid;

class CopyMultipleDirectoriesTest extends TestCase
{
	use DatabaseMigrations;

	private $entryIds;
    private $root;

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

        $uri = '/directories/copy';
        
        $this->json('PATCH', $uri,[
        	'entries' => [
        		[
            		'objectId' => $this->entryIds[0],
                    'objectType' => 'directory',
            		'targetId' => $this->entryIds[1],
	        	],
	        	[
	        		'objectId' => $this->entryIds[1],
                    'objectType' => 'directory',
	        		'targetId' => $this->entryIds[0],
	        	],
        	]
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
     * @param string $userKey
     * @param string $directoryKey
     * @param int $statusCode
     * @dataProvider provideValidAccessData
     */
    public function testValidAccess($userKey)
    {
        if ($userKey != null) {
            $user = $this->getUser($userKey);
            $this->actingAs($user);
        }

        $uri = '/directories/copy';
        
        $this->json('PATCH', $uri,[
        	'entries' => [
        		[
                    'objectId' => $this->entryIds[0],
                    'objectType' => 'directory',
                    'targetId' => $this->entryIds[1],
                ],
                [
                    'objectId' => $this->entryIds[1],
                    'objectType' => 'directory',
                    'targetId' => $this->entryIds[0],
                ],
        	]
        ]);
        $this->seeStatusCode(Response::HTTP_NO_CONTENT);
        $this->seeInDatabase('directory_entries', [
            'parentId' => $this->entryIds[1],
        ]);
        $this->seeInDatabase('directory_entries', [
            'parentId' => $this->entryIds[0],
        ]);		
    }

}
