<?php

use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Ramsey\Uuid\Uuid;

class ListExtensionsTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * @var \App\Checkpoint
     */
    protected $checkpoint;

    /**
     * @var \App\Checkpoint
     */
    protected $otherCheckpoint;

    /**
     * @var \App\Extension
     */
    protected $extensions;

    /**
     * @var \App\TextfieldExtension
     */
    protected $textfieldExtensions;

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
        $this->checkpoint = $this->createFakeCheckpoint();
        $this->checkpoint->save();
        $this->otherCheckpoint = $this->createFakeCheckpoint();
        $this->otherCheckpoint->save();
        $section->entry($this->checkpoint)->save();
        $section->entry($this->otherCheckpoint)->save();
        $this->textfieldExtensions = [
            $this->makeFakeTextfieldExtension(),
            $this->makeFakeTextfieldExtension(),
        ];
        $this->extensions = [];
        foreach ($this->textfieldExtensions as $textfieldExtension) {
            $textfieldExtension->save();
            $extension = $this->checkpoint->extension($textfieldExtension);
            $extension->save();
            $this->extensions[] = $extension;
            $this->checkpoint->entry($extension)->save();
        }
    }

    public function provideInvalidAccessData()
    {
        return [
            [null, 'random', Response::HTTP_UNAUTHORIZED],
            [null, 'checkpoint', Response::HTTP_UNAUTHORIZED],
            [self::$USER, 'random', Response::HTTP_NOT_FOUND],
            [self::$USER, 'checkpoint', Response::HTTP_FORBIDDEN],
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
            $uri = '/checkpoints/' . $this->checkpoint->id . '/extensions';
        } else {
            $uri = '/checkpoints/' . Uuid::uuid4()->toString() . '/extensions';
        }
        $this->json('GET', $uri);
        $this->seeStatusCode($statusCode);
        $this->seeHeader('Content-Type', 'application/json');
    }

    public function provideValidAccessData()
    {
        return [
            [self::$ADMIN, 'checkpoint', false],
            [self::$ADMIN, 'otherCheckpoint', true],
            [self::$SUPER_ADMIN, 'checkpoint', false],
            [self::$SUPER_ADMIN, 'otherCheckpoint', true],
        ];
    }

    /**
     * @param $userKey
     * @param $checkpointKey
     * @param $expectEmpty
     * @dataProvider provideValidAccessData
     */
    public function testValidAccess($userKey, $checkpointKey, $expectEmpty)
    {
        $user = $this->getUser($userKey);
        $this->actingAs($user);
        if ($checkpointKey === 'checkpoint') {
            $uri = '/checkpoints/' . $this->checkpoint->id . '/extensions';
        } else {
            $uri = '/checkpoints/' . $this->otherCheckpoint->id . '/extensions';
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
                        'type',
                        'object' => [
                            'text',
                            'fixed',
                        ],
                    ],
                    [
                        'id',
                        'type',
                        'object' => [
                            'text',
                            'fixed',
                        ],
                    ],
                ],
            ]);
            foreach ($this->extensions as $key => $extension) {
                $this->seeJsonContains([
                    'id' => $extension->id,
                    'type' => 'textfield',
                    'object' => [
                        'text' => $this->textfieldExtensions[$key]->text,
                        'fixed' => $this->textfieldExtensions[$key]->fixed,
                    ],
                ]);
            }
        }
    }

    private function createFakeCheckpoint() {
        $scoringScheme = $this->makeFakeScoringScheme();
        $this->company->scoringSchemes()->save($scoringScheme);
        $score = $this->makeFakeScore();
        $scoringScheme->scores()->save($score);
        $checkpoint = $this->makeFakeCheckpoint();
        $checkpoint->scoringScheme()->associate($scoringScheme);
        $evaluationScheme = $this->makeFakeChoiceScheme();
        $evaluationScheme->save();
        $checkpoint->evaluationScheme()->associate($evaluationScheme);
        return $checkpoint;
    }
}
