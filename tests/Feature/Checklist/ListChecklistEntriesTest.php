<?php

use App\Checkpoint;
use App\Extension;
use App\Section;
use App\TextfieldExtension;
use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Ramsey\Uuid\Uuid;

class ListChecklistEntriesTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * @var \App\Checklist
     */
    protected $checklist;

    /**
     * @var \App\Checklist
     */
    protected $otherChecklist;

    /**
     * @var Section
     */
    protected $section;

    /**
     * @var Checkpoint
     */
    protected $checkpoint;

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
        $this->checklist = $this->makeFakeChecklist(['with_description']);
        $this->checklist->save();
        $this->company->directory->entry($this->checklist)->save();
        $this->otherChecklist = $this->makeFakeChecklist(['with_description']);
        $this->otherChecklist->save();
        $this->company->directory->entry($this->otherChecklist)->save();
        $this->section = $this->makeFakeSection();
        $this->section->save();
        $this->checklist->entry($this->section)->save();
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
        $this->checklist->entry($this->checkpoint)->save();
        $this->textfieldExtension = $this->makeFakeTextfieldExtension();
        $this->textfieldExtension->save();
        $this->extension = $this->checklist->extension($this->textfieldExtension);
        $this->extension->save();
        $this->checklist->entry($this->extension)->save();
    }

    public function provideInvalidAccessData()
    {
        return [
            [null, 'random', Response::HTTP_UNAUTHORIZED],
            [null, 'checklist', Response::HTTP_UNAUTHORIZED],
            [self::$USER, 'random', Response::HTTP_NOT_FOUND],
            [self::$USER, 'checklist', Response::HTTP_FORBIDDEN],
            [self::$OTHER_ADMIN, 'checklist', Response::HTTP_FORBIDDEN],
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
        if ($sectionKey === 'checklist') {
            $uri = '/checklists/' . $this->checklist->id . '/entries';
        } else {
            $uri = '/checklists/' . Uuid::uuid4()->toString() . '/entries';
        }
        $this->json('GET', $uri);
        $this->seeStatusCode($statusCode);
        $this->seeHeader('Content-Type', 'application/json');
    }

    public function provideValidAccessData()
    {
        return [
            [self::$ADMIN, 'checklist', false],
            [self::$ADMIN, 'otherChecklist', true],
            [self::$SUPER_ADMIN, 'checklist', false],
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
        if ($sectionKey === 'checklist') {
            $uri = '/checklists/' . $this->checklist->id . '/entries';
        } else {
            $uri = '/checklists/' . $this->otherChecklist->id . '/entries';
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
                            'title',
                            'index',
                        ],
                    ],
                    [
                        'id',
                        'parentId',
                        'objectType',
                        'object' => [
                            'id',
                            'prompt',
                            'mandatory',
                            'factor',
                            'index',
                            'scoringSchemeId',
                            'evaluationSchemeId',
                        ],
                    ],
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
                'parentId' => $this->checklist->id,
                'objectType' => 'section',
                'object' => [
                    'id' => $this->section->id,
                    'title' => $this->section->title,
                ]
            ]);
            $this->seeJsonContains([
                'parentId' => $this->checklist->id,
                'objectType' => 'checkpoint',
                'object' => [
                    'id' => $this->checkpoint->id,
                    'prompt' => $this->checkpoint->prompt,
                    'mandatory' => $this->checkpoint->mandatory,
                    'factor' => $this->checkpoint->factor,
                    'index' => $this->checkpoint->index,
                ]
            ]);
            $this->seeJsonContains([
                'parentId' => $this->checklist->id,
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
}
