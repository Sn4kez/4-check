<?php

use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Ramsey\Uuid\Uuid;

class SyncNotifiedTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * @var \App\Score
     */
    protected $score;

    /**
     * @var \App\ScoringScheme
     */
    protected $scoringScheme;

    public function setUp()
    {
        parent::setUp();
        $this->scoringScheme = $this->makeFakeScoringScheme();
        $this->company->scoringSchemes()->save($this->scoringScheme );
        $this->score = $this->makeFakeScore();
        $this->scoringScheme->scores()->save($this->score);
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
     * @param $attribute
     * @param $dbAttribute
     * @param $value
     * @dataProvider provideValidAccessData
     */
    public function testValidAccess($userKey)
    {
    	$user = $this->getUser($userKey);
        $this->actingAs($user);
        $data = [
	        'data' => [
	        	[
	        		'id' => $this->score->id,
	        		'entries' => [
	        			[
	        				'objectType' => 'user',
	        				'objectId' => $user->id,
	        			]
	        		],
	        	]
        	],
        ];
        $this->json('POST', '/scores/notice', $data);
        $this->seeStatusCode(Response::HTTP_OK);
        $this->seeInDatabase('criticals', [
            'critical_id' => $user->id
        ]);
    }
}