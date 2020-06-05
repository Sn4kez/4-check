<?php

use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Ramsey\Uuid\Uuid;

class UpdateCheckpointTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * @var \App\Checkpoint
     */
    protected $checkpoint;

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
        $this->checkpoint = $this->makeFakeCheckpoint();
        $this->checkpoint->scoringScheme()->associate($scoringScheme);
        $evaluationScheme = $this->makeFakeChoiceScheme();
        $evaluationScheme->save();
        $this->checkpoint->evaluationScheme()->associate($evaluationScheme);
        $this->checkpoint->save();
        $section->entry($this->checkpoint)->save();
    }

    public function provideInvalidAccessData()
    {
        return [
            [null, 'random', Response::HTTP_UNAUTHORIZED],
            [null, 'checkpoint', Response::HTTP_UNAUTHORIZED],
            [self::$USER, 'random', Response::HTTP_NOT_FOUND],
            [self::$USER, 'checkpoint', Response::HTTP_FORBIDDEN],
            [self::$ADMIN, 'random', Response::HTTP_NOT_FOUND],
            [self::$OTHER_ADMIN, 'checkpoint', Response::HTTP_FORBIDDEN],
        ];
    }

    /**
     * @param string $userKey
     * @param string $checkpointKey
     * @param int $statusCode
     * @dataProvider provideInvalidAccessData
     */
    public function testInvalidAccess($userKey, $checkpointKey, $statusCode)
    {
        if ($userKey != null) {
            $user = $this->getUser($userKey);
            $this->actingAs($user);
        }
        if ($checkpointKey === 'checkpoint') {
            $uri = '/checkpoints/' . $this->checkpoint->id;
        } else {
            $uri = '/checkpoints/' . Uuid::uuid4()->toString();
        }
        $this->json('PATCH', $uri);
        $this->seeStatusCode($statusCode);
        $this->seeHeader('Content-Type', 'application/json');
    }

    public function provideValidEntities()
    {
        return [
            [self::$ADMIN, 'prompt', 'prompt', 'prompt'],
            [self::$SUPER_ADMIN, 'prompt', 'prompt', 'prompt'],
            [self::$ADMIN, 'mandatory', 'mandatory', true],
            [self::$SUPER_ADMIN, 'mandatory', 'mandatory', false],
            [self::$ADMIN, 'factor', 'factor', 0.4],
            [self::$SUPER_ADMIN, 'factor', 'factor', 0.7],
            [self::$ADMIN, 'index', 'index', '1234'],
            [self::$SUPER_ADMIN, 'index', 'index', '1234'],
        ];
    }

    /**
     * @param $userKey
     * @param $attribute
     * @param $dbAttribute
     * @param $value
     * @dataProvider provideValidEntities
     */
    public function testValidEntities($userKey, $attribute, $dbAttribute, $value)
    {
        $user = $this->getUser($userKey);
        $this->actingAs($user);
        $data = [$attribute => $value];
        $this->json('PATCH', '/checkpoints/' . $this->checkpoint->id, $data);
        $this->seeStatusCode(Response::HTTP_NO_CONTENT);
        $this->seeInDatabase('checkpoints', [
            'id' => $this->checkpoint->id,
            $dbAttribute => $value,
        ]);
    }

    public function provideInvalidEntities()
    {
        return [
            [self::$ADMIN, 'prompt', null],
            [self::$ADMIN, 'prompt', 123],
            [self::$ADMIN, 'prompt', str_repeat('Long', 32) . 'Prompt'],
            [self::$ADMIN, 'mandatory', null],
            [self::$ADMIN, 'mandatory', 123],
            [self::$ADMIN, 'factor', null],
            [self::$ADMIN, 'factor', 'string'],
            [self::$ADMIN, 'index', null],
            [self::$ADMIN, 'index', 123],
            [self::$ADMIN, 'index', str_repeat('Long', 32) . 'Index'],
        ];
    }

    /**
     * @param $userKey
     * @param $attribute
     * @param $value
     * @dataProvider provideInvalidEntities
     */
    public function testInvalidEntities($userKey, $attribute, $value)
    {
        $user = $this->getUser($userKey);
        $this->actingAs($user);
        $data = [$attribute => $value];
        $this->json('PATCH', '/checkpoints/' . $this->checkpoint->id, $data);
        $this->seeStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->seeHeader('Content-Type', 'application/json');
    }
}
