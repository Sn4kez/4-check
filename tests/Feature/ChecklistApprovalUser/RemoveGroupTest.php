<?php

use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;
use App\User;
use App\Checklist;
use App\Gender;
use App\Role;
use App\Group;
use Ramsey\Uuid\Uuid;

class RemoveGroupTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * @var \App\Checklist
     */
    protected $checklist;

    /**
     * @var \App\User $otherUser
     */

    protected $otherUser;

    public function setUp()
    {
        parent::setUp();

        $this->otherUser = factory(User::class)->make();
        $this->otherUser->company()->associate($this->company);
        $this->otherUser->gender()->associate(Gender::all()->random());
        $this->otherUser->role()->associate(Role::admin());
        $this->otherUser->save();

        $this->checklist = $this->makeFakeChecklist(['with_description']);
        $this->checklist->id = Uuid::uuid4()->toString();
        $this->checklist->company()->associate($this->company);
        $this->checklist->approvingByUsers()->attach($this->otherUser); 
        $this->checklist->save();
        $this->company->directory->entry($this->checklist)->save();

        $this->group = $this->makeFakeGroup();
        $this->group->company()->associate($this->company);
        $this->group->save();

        $this->group->users()->attach($this->otherUser);
    }

    public function provideInvalidAccessData()
    {
        return [
            [null,  Response::HTTP_UNAUTHORIZED],
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
            $id = $this->group->id;
        } else {
        	$id = Uuid::uuid4()->toString();
        }


        $uri = '/checklists/' . $this->checklist->id . '/approvers/group/' . $id;

        $this->json('DELETE', $uri);
        $this->seeStatusCode($statusCode);
        $this->seeHeader('Content-Type', 'application/json');
    }

    public function provideValidAccess()
    {
        return [
            [self::$ADMIN],
            [self::$SUPER_ADMIN],
        ];
    }

    /**
     * @param $userKey
     * @param $attribute
     * @param $dbAttribute
     * @param $value
     * @dataProvider provideValidAccess
     */

    public function testValidAccess($userKey)
    {
    	$user = $this->getUser($userKey);
        $this->actingAs($user);

        $uri = '/checklists/' . $this->checklist->id . '/approvers/group/' . $this->group->id;
        $this->json('DELETE', $uri);
        $this->seeStatusCode(Response::HTTP_OK);
    }
}

