<?php

use App\Gender;
use App\ParticipantExtension;
use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Ramsey\Uuid\Uuid;

class CreateParticipantExtensionTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * @var \App\Checkpoint
     */
    protected $checkpoint;

    /**
     * @var \App\User
     */
    protected $participant;

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
        $this->participant = $this->makeFakeUser();
        $this->participant->company()->associate($this->company);
        $this->participant->gender()->associate(Gender::all()->random());
        $this->participant->save();
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
            $uri = '/checkpoints/' . $this->checkpoint->id . '/extensions';
        } else {
            $uri = '/checkpoints/' . Uuid::uuid4()->toString() . '/extensions';
        }
        $this->json('POST', $uri);
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
        /** @var ParticipantExtension $extension1 */
        $extension1 = factory(ParticipantExtension::class)->make();
        $extension1->user()->associate($this->participant);
        /** @var ParticipantExtension $extension2 */
        $extension2 = factory(ParticipantExtension::class)->make();
        $extension2->user()->associate($this->participant);
        $uri = '/checkpoints/' . $this->checkpoint->id . '/extensions';
        $this->json('POST', $uri, [
            'type' => 'participant',
            'data' => [
                'userId' => $extension1->user->id,
                'fixed' => $extension1->fixed,
            ],
        ]);
        $this->seeStatusCode(Response::HTTP_CREATED);
        $this->seeHeader('Content-Type', 'application/json');
        $this->seeJsonStructure([
            'data' => [
                'id',
                'type',
                'object' => [
                    'userId',
                    'fixed',
                ],
            ],
        ])->seeJsonNotNull([
            'data' => [
                'id',
            ],
        ])->seeJsonContains([
            'type' => 'participant',
            'object' => [
                'userId' => $extension1->user->id,
                'fixed' => $extension1->fixed,
            ],
        ]);
        $this->json('POST', $uri, [
            'type' => 'participant',
            'data' => [
                'userId' => $extension2->user->id,
                'fixed' => $extension2->fixed,
            ],
        ]);
        $this->seeStatusCode(Response::HTTP_CREATED);
        $this->seeHeader('Content-Type', 'application/json');
        $this->seeJsonStructure([
            'data' => [
                'id',
                'type',
                'object' => [
                    'userId',
                    'fixed',
                ],
            ],
        ])->seeJsonNotNull([
            'data' => [
                'id',
            ],
        ])->seeJsonContains([
            'type' => 'participant',
            'object' => [
                'userId' => $extension2->user->id,
                'fixed' => $extension2->fixed,
            ],
        ]);
        $this->seeInDatabase('participant_extensions', [
            'userId' => $extension1->user->id,
            'fixed' => $extension1->fixed,
        ])->seeInDatabase('participant_extensions', [
            'userId' => $extension2->user->id,
            'fixed' => $extension2->fixed,
        ]);
        $this->assertCount(2, $this->checkpoint->entries);
    }

    public function provideValidEntities()
    {
        return [
            ['userId', null],
        ];
    }

    /**
     * @param $attribute
     * @param $value
     * @dataProvider provideValidEntities
     */
    public function testValidEntities($attribute, $value)
    {
        /** @var ParticipantExtension $extension */
        $extension= factory(ParticipantExtension::class)->make();
        $extension->user()->associate($this->participant);
        $data = array_merge([
            'userId' => $extension->user->id,
            'fixed' => $extension->fixed,
        ], [$attribute => $value]);
        $this->actingAs($this->admin);
        $url = '/checkpoints/' . $this->checkpoint->id . '/extensions';
        $this->json('POST', $url, [
            'type' => 'participant',
            'data' => $data,
        ]);
        $this->seeStatusCode(Response::HTTP_CREATED);
        $this->seeHeader('Content-Type', 'application/json');
        $this->seeJsonStructure([
            'data' => [
                'object' => [
                    $attribute,
                ],
            ],
        ])->seeJsonContains([
            $attribute => $value,
        ]);
        $this->seeInDatabase('participant_extensions', [
            $attribute => $value,
        ]);
    }

    public function provideInvalidEntities()
    {
        return [
            ['userId', 'unknown'],
            ['fixed', null],
            ['fixed', 123],
        ];
    }

    /**
     * @param string $attribute
     * @param string $value
     * @dataProvider provideInvalidEntities
     */
    public function testInvalidEntities($attribute, $value)
    {
        /** @var ParticipantExtension $extension */
        $extension= factory(ParticipantExtension::class)->make();
        $extension->user()->associate($this->user);
        $data = array_merge([
            'userId' => $extension->user->id,
            'fixed' => $extension->fixed,
        ], [$attribute => $value]);
        $this->actingAs($this->admin);
        $uri = '/checkpoints/' . $this->checkpoint->id . '/extensions';
        $this->json('POST', $uri, [
            'type' => 'participant',
            'data' => $data,
        ]);
        $this->seeStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->seeHeader('Content-Type', 'application/json');
    }
}
