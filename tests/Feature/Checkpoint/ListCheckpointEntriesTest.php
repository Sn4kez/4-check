<?php

use App\Checkpoint;
use App\Extension;
use App\Section;
use App\TextfieldExtension;
use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Ramsey\Uuid\Uuid;

class ListCheckpointEntriesTest extends TestCase
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
     * @var Extension
     */
    protected $extension;

    /**
     * @var TextfieldExtension
     */
    protected $textfieldExtension;

    public function setUp()
    {
        parent::setUp();
        $checklist = $this->makeFakeChecklist(['with_description']);
        $checklist->save();
        $this->company->directory->entry($checklist)->save();
        $this->checkpoint = $this->createFakeCheckpoint();
        $this->checkpoint->save();
        $checklist->entry($this->checkpoint)->save();
        $this->otherCheckpoint = $this->createFakeCheckpoint();
        $this->otherCheckpoint->save();
        $checklist->entry($this->otherCheckpoint)->save();
        $this->textfieldExtension = $this->makeFakeTextfieldExtension();
        $this->textfieldExtension->save();
        $this->extension = $this->checkpoint->extension($this->textfieldExtension);
        $this->extension->save();
        $this->checkpoint->entry($this->extension)->save();
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
     * @param string $sectionKey
     * @param int $statusCode
     * @dataProvider provideInvalidAccessData
     */
    public function testInvalidAccess($userKey, $sectionKey, $statusCode)
    {
        if ($userKey != null) {
            $user = $this->getUser($userKey);
            $this->actingAs($user);
        }
        if ($sectionKey === 'checkpoint') {
            $uri = '/checkpoints/' . $this->checkpoint->id . '/entries';
        } else {
            $uri = '/checkpoints/' . Uuid::uuid4()->toString() . '/entries';
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
        ];
    }

    /**
     * @param $userKey
     * @param $sectionKey
     * @param $expectEmpty
     * @dataProvider provideValidAccessData
     */
    public function testValidAccess($userKey, $sectionKey, $expectEmpty)
    {
        $user = $this->getUser($userKey);
        $this->actingAs($user);
        if ($sectionKey === 'checkpoint') {
            $uri = '/checkpoints/' . $this->checkpoint->id . '/entries';
        } else {
            $uri = '/checkpoints/' . $this->otherCheckpoint->id . '/entries';
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
                        'parentId',
                        'objectType',
                        'object' => [
                            'id',
                            'type',
                            'object' => [
                                'text',
                                'fixed',
                            ],
                        ],
                    ],
                ],
            ]);
            $this->seeJsonContains([
                'parentId' => $this->checkpoint->id,
                'objectType' => 'extension',
                'object' => [
                    'type' => 'textfield',
                    'id' => $this->extension->id,
                    'object' => [
                        'text' => $this->textfieldExtension->text,
                        'fixed' => $this->textfieldExtension->fixed,
                    ],
                ]
            ]);
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
