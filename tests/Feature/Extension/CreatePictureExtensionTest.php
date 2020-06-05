<?php

use App\PictureExtension;
use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Ramsey\Uuid\Uuid;

class CreatePictureExtensionTest extends TestCase
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

        /** @var PictureExtension $extension */
        $extension = factory(PictureExtension::class)->make();

        $uri = '/checkpoints/' . $this->checkpoint->id . '/extensions';

        $this->json('POST', $uri, [
            'type' => 'picture',
            'data' => [
                'image' => $extension->image,
                'type' => $extension->type
            ],
        ]);

        $this->seeStatusCode(Response::HTTP_CREATED);
        $this->seeHeader('Content-Type', 'application/json');

        $this->seeJsonStructure([
            'data' => [
                'id',
                'type',
                'object' => [
                    'image',
                ],
            ],
        ])->seeJsonNotNull([
            'data' => [
                'id',
            ],
        ]);

        $this->assertCount(1, $this->checkpoint->entries);
    }

    public function provideValidEntities()
    {
        return [
            ['image', '123123'],
            ['image', '1'],
            ['image', '1231231fihiuqfgiu']
        ];
    }

    /**
     * @param $attribute
     * @param $value
     * @dataProvider provideValidEntities
     */
    public function testValidEntities($attribute, $value)
    {
        $extension = factory(PictureExtension::class)->make();
        $data = array_merge([
            'image' => $extension->image,
            'type' => $extension->type
        ], [$attribute => $value]);

        $this->actingAs($this->admin);

        $url = '/checkpoints/' . $this->checkpoint->id . '/extensions';
        $this->json('POST', $url, [
            'type' => 'picture',
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
        $this->seeInDatabase('picture_extensions', [
            $attribute => $value,
        ]);
    }
}
