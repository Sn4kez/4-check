<?php

use App\ScoreCondition;
use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Ramsey\Uuid\Uuid;

class DeleteScoreConditionTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * @var \App\ScoreCondition
     */
    protected $scoreCondition;

    public function setUp()
    {
        parent::setUp();
        $checklist = $this->makeFakeChecklist(['with_description']);
        $checklist->save();
        $this->company->directory->entry($checklist)->save();
        $section = $this->makeFakeSection();
        $section->save();
        $checklist->entry($section)->save();
        $scoringScheme = $this->makeFakeScoringScheme();
        $this->company->scoringSchemes()->save($scoringScheme);
        $score = $this->makeFakeScore();
        $scoringScheme->scores()->save($score);
        $checkpoint = $this->makeFakeCheckpoint();
        $checkpoint->scoringScheme()->associate($scoringScheme);
        $valueScheme = $this->makeFakeValueScheme();
        $valueScheme->save();
        $checkpoint->evaluationScheme()->associate($valueScheme);
        $checkpoint->save();
        $section->entry($checkpoint)->save();
        $this->scoreCondition = $this->makeFakeScoreCondition();
        $this->scoreCondition->score()->associate($score);
        $valueScheme->conditions()->save($this->scoreCondition);
    }

    public function provideInvalidAccessData()
    {
        return [
            [null, 'random', Response::HTTP_UNAUTHORIZED],
            [null, 'condition', Response::HTTP_UNAUTHORIZED],
            [self::$USER, 'random', Response::HTTP_NOT_FOUND],
            [self::$USER, 'condition', Response::HTTP_FORBIDDEN],
            [self::$ADMIN, 'random', Response::HTTP_NOT_FOUND],
            [self::$OTHER_ADMIN, 'condition', Response::HTTP_FORBIDDEN],
        ];
    }

    /**
     * @param string $userKey
     * @param boolean $sectionKey
     * @param int $statusCode
     * @dataProvider provideInvalidAccessData
     */
    public function testInvalidAccess($userKey, $sectionKey, $statusCode)
    {
        if ($userKey != null) {
            $user = $this->getUser($userKey);
            $this->actingAs($user);
        }
        if ($sectionKey === 'condition') {
            $uri = '/conditions/' . $this->scoreCondition->id;
        } else {
            $uri = '/conditions/' . Uuid::uuid4()->toString();
        }
        $this->json('DELETE', $uri);
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
        $this->json('DELETE', '/conditions/' . $this->scoreCondition->id);
        $this->seeStatusCode(Response::HTTP_NO_CONTENT);
        $this->assertSoftDeleted(
            'score_conditions',
            ['id' => $this->scoreCondition->id],
            ScoreCondition::DELETED_AT);
    }
}
