<?php

use App\LocationType;
use App\Company;
use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Ramsey\Uuid\Uuid;

class ListLocationTypeTest extends TestCase
{
    use DatabaseMigrations;

    /* @var App\LocationType $locationTypes */
    protected $locationTypes;

    public function setUp()
    {
    	parent::setUp();

    	$locationType1 = factory(LocationType::class)->make();
    	$locationType1->company()->associate($this->company);
        $locationType1->id = Uuid::uuid4()->toString();
    	$locationType1->save();

    	$locationType2 = factory(LocationType::class)->make();
    	$locationType2->company()->associate($this->company);
        $locationType2->id = Uuid::uuid4()->toString();
    	$locationType2->save();

    	$this->locationTypes = [
    		LocationType::where('name', '=', 'building')->first(),
    		LocationType::where('name', '=', 'room')->first(),
    		LocationType::where('name', '=', 'floor')->first(),
    		LocationType::where('name', '=', 'area')->first(),
    		LocationType::where('name', '=', 'machine')->first(),
    		LocationType::where('name', '=', 'customer')->first(),
    		$locationType1,
    		$locationType2
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
            $uri = '/locations/types/company/' . $this->user->company->id;
        } else {
        	$uri = '/locations/types/company/' . Uuid::uuid4()->toString();
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
            $uri = '/locations/types/company/' . $this->user->company->id;
        } else {
            $uri = '/locations/types/company/' . $this->otherUser->company->id;
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
                    [
                    	'id',
                        'name'
                    ],
                    [
                    	'id',
                        'name'  
                    ],
                    [
                    	'id',
                        'name'
                    ],
                    [
                    	'id',
                        'name'  
                    ],
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
            foreach ($this->locationTypes as $locationType) {
                $this->seeJsonContains([
                    'id' => $locationType->id,
                    'name' => $locationType->name
                ]);
            }
        }
    }
}
