<?php

use App\ScoreNotification;
use App\Checklist;
use App\ScoringScheme;
use App\Score;
use App\Checkpoint;
use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Ramsey\Uuid\Uuid;

class CreateScoreNotificationTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * @var \App\ScoreCondition
     */
    protected $score;

    /**
     * @var \App\ScoreCondition
     */
    protected $checklist;

    public function setUp()
    {
        parent::setUp();
        $this->checklist = $this->makeFakeChecklist(['with_description']);
        $this->checklist->save();
        $this->company->directory->entry($this->checklist)->save();
        $section = $this->makeFakeSection();
        $section->save();
        $this->checklist->entry($section)->save();
        $scoringScheme = $this->makeFakeScoringScheme();
        $this->company->scoringSchemes()->save($scoringScheme);
        $this->score = $this->makeFakeScore();
        $scoringScheme->scores()->save($this->score);
        $checkpoint = $this->makeFakeCheckpoint();
        $checkpoint->scoringScheme()->associate($scoringScheme);
        $valueScheme = $this->makeFakeValueScheme();
        $valueScheme->save();
        $checkpoint->evaluationScheme()->associate($valueScheme);
        $checkpoint->save();
        $section->entry($checkpoint)->save();
        $this->scoreCondition = $this->makeFakeScoreCondition();
        $this->scoreCondition->score()->associate($this->score);
        $valueScheme->conditions()->save($this->scoreCondition);
    }

    public function provideInvalidAccessData()
    {
        return [
            [null, 'random', Response::HTTP_UNAUTHORIZED],
            [null, 'scheme', Response::HTTP_UNAUTHORIZED],
            [self::$USER, 'random', Response::HTTP_NOT_FOUND],
            [self::$USER, 'score', Response::HTTP_FORBIDDEN],
            [self::$OTHER_ADMIN, 'score', Response::HTTP_FORBIDDEN],
        ];
    }

    /**
     * @param string $userKey
     * @param string $scoreKey
     * @param int $statusCode
     * @dataProvider provideInvalidAccessData
     */
    public function testInvalidAccess($userKey, $scoreKey, $statusCode)
    {
    	$userId = null;
        if ($userKey != null) {
            $user = $this->getUser($userKey);
            $this->actingAs($user);
            $userId = $user->id;
        }

        if ($scoreKey === 'score') {
            $uri = '/scores/' . $this->score->id . '/notice';
        } else {
            $uri = '/scores/' . Uuid::uuid4()->toString() . '/notice';
        }
        $this->json('POST', $uri, [
        	'checklistId' => $this->checklist->id,
        	'objectType' => 'user',
        	'objectId' => $userId,
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
     * @dataProvider provideValidAccessData
     */
    public function testValidAccess($userKey)
    {
        $user = $this->getUser($userKey);
        $this->actingAs($user);

        $uri = '/scores/' . $this->score->id . '/notice';

        $this->json('POST', $uri, [
        	'checklistId' => $this->checklist->id,
        	'objectType' => 'user',
        	'objectId' => $user->id,
        ]);
        $this->seeStatusCode(Response::HTTP_CREATED);
        $this->seeHeader('Content-Type', 'application/json');
        $this->seeJsonStructure([
            'data' => [
                'id',
                'checklistId',
                'scoreId',
                'objectId',
                'objectType'
            ],
        ])->seeJsonNotNull([
            'data' => [
                'id',
                'object'
            ],
        ])->seeJsonContains([
            'checklistId' => $this->checklist->id,
            'scoreId' => $this->score->id,
            'objectId' => $user->id,
            'objectType' => 'user',
        ]);
    }
}