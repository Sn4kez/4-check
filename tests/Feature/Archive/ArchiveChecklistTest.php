<?php

use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Ramsey\Uuid\Uuid;

class ArchiveChecklistTest extends TestCase
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
        foreach ($this->directories as $directory) {
            $directory->save();
            $this->company->directory->entry($directory)->save();
        }
        foreach ($this->checklists as $checklist) {
            $checklist->save();
            $this->company->directory->entry($checklist)->save();
        }

        $this->entryIds = [];

        foreach($this->company->directory->entries as $entry) {
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
        $uri = '/directories/'. $this->entryIds[3] . '/archive';
        
        $this->json('PATCH', $uri);
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
     * @param $companyKey
     * @param $expectEmpty
     * @dataProvider provideValidAccessData
     */

    public function testValidAccess($userKey)
    {
        if ($userKey != null) {
            $user = $this->getUser($userKey);
            $this->actingAs($user);
        }
        $uri = '/directories/'. $this->entryIds[3] . '/archive';
        
        $this->json('PATCH', $uri, [
            'company' => $this->company->id
        ]);
        $this->seeStatusCode(Response::HTTP_NO_CONTENT);
    }
}
