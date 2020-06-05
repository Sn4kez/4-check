<?php

use App\Checkpoint;
use App\Extension;
use App\Section;
use App\TextfieldExtension;
use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Ramsey\Uuid\Uuid;

class ListSectionEntriesTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * @var \App\Section
     */
    protected $section;

    /**
     * @var \App\Checklist
     */
    protected $otherSection;

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
        $checklist = $this->makeFakeChecklist(['with_description']);
        $checklist->save();
        $this->company->directory->entry($checklist)->save();
        $this->section = $this->makeFakeSection();
        $this->section->save();
        $checklist->entry($this->section)->save();
        $this->otherSection = $this->makeFakeSection();
        $this->otherSection->save();
        $checklist->entry($this->otherSection)->save();
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
        $this->section->entry($this->checkpoint)->save();
        $this->textfieldExtension = $this->makeFakeTextfieldExtension();
        $this->textfieldExtension->save();
        $this->extension = $this->section->extension($this->textfieldExtension);
        $this->extension->save();
        $this->section->entry($this->extension)->save();
    }

    public function provideInvalidAccessData()
    {
        return [
            [null, 'random', Response::HTTP_UNAUTHORIZED],
            [null, 'section', Response::HTTP_UNAUTHORIZED],
            [self::$USER, 'random', Response::HTTP_NOT_FOUND],
            [self::$USER, 'section', Response::HTTP_FORBIDDEN],
            [self::$OTHER_ADMIN, 'section', Response::HTTP_FORBIDDEN],
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
        if ($sectionKey === 'section') {
            $uri = '/sections/' . $this->section->id . '/entries';
        } else {
            $uri = '/sections/' . Uuid::uuid4()->toString() . '/entries';
        }
        $this->json('GET', $uri);
        $this->seeStatusCode($statusCode);
        $this->seeHeader('Content-Type', 'application/json');
    }

    public function provideValidAccessData()
    {
        return [
            [self::$ADMIN, 'section', false],
            [self::$ADMIN, 'otherSection', true],
            [self::$SUPER_ADMIN, 'section', false],
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
        if ($sectionKey === 'section') {
            $uri = '/sections/' . $this->section->id . '/entries';
        } else {
            $uri = '/sections/' . $this->otherSection->id . '/entries';
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
                'parentId' => $this->section->id,
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
                'parentId' => $this->section->id,
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
