<?php

use App\AuditState;
use App\Company;
use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Ramsey\Uuid\Uuid;

class ListAuditStateTest extends TestCase
{
    use DatabaseMigrations;

    /* @var App\AuditState $auditStates */
    protected $auditStates;

    public function setUp()
    {
    	parent::setUp();
    	$this->auditStates = [
    		AuditState::where('name', '=', 'draft')->first(),
    		AuditState::where('name', '=', 'finished')->first(),
    	];    	
    }

    public function provideInvalidAccessData()
    {
        return [
            [null, 'random', Response::HTTP_UNAUTHORIZED],
            [null, 'company', Response::HTTP_UNAUTHORIZED],
        ];
    }

    /**
     * @param string $userKey
     * @param string $userIdKey
     * @param int $statusCode
     * @dataProvider provideInvalidAccessData
     */
    public function testInvalidAccess($userKey, $companyIdKey, $statusCode)
    {
    	if ($companyIdKey === 'company') {
            $uri = '/audits/states/company/' . $this->user->company->id;
        } else {
        	$uri = '/audits/states/company/' . Uuid::uuid4()->toString();
        }
        $this->json('GET', $uri);
        $this->seeStatusCode($statusCode);
        $this->seeHeader('Content-Type', 'application/json');
    }

    public function provideValidAccessData()
    {
        return [
            [self::$ADMIN, 'company', false],
            [self::$SUPER_ADMIN, 'company', false],
            [self::$OTHER_ADMIN, 'otherCompany', true],
        ];
    }

    /**
     * @param $userKey
     * @param $companyKey
     * @param $expectEmpty
     * @dataProvider provideValidAccessData
     */
    public function testValidAccess($userKey, $companyKey, $expectEmpty)
    {
        $user = $this->getUser($userKey);
        $this->actingAs($user);
        if ($companyKey === 'company') {
            $uri = '/audits/states/company/' . $this->user->company->id;
        } else {
            $uri = '/audits/states/company/' . $this->otherUser->company->id;
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
                    	'id',
                        'name'
                    ],
                    [
                    	'id',
                        'name'  
                    ],
                ],
            ]);
            foreach ($this->auditStates as $auditState) {
                $this->seeJsonContains([
                    'id' => $auditState->id,
                    'name' => $auditState->name
                ]);
            }
        }
    }
}
