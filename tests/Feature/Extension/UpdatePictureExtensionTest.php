<?php

use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Ramsey\Uuid\Uuid;
use App\PictureExtension;

class UpdatePictureExtensionTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * @var \App\Extension
     */
    protected $extension;

    /**
     * @var \App\PictureExtension
     */
    protected $pictureExtension;

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
        $evaluationScheme = $this->makeFakeChoiceScheme();
        $evaluationScheme->save();
        $checkpoint->evaluationScheme()->associate($evaluationScheme);
        $checkpoint->save();
        $section->entry($checkpoint)->save();
        $this->pictureExtension = factory(PictureExtension::class)->make();
        $this->pictureExtension->save();
        $this->extension = $checkpoint->extension($this->pictureExtension);
        $this->extension->save();
        $checkpoint->entry($this->extension)->save();
    }

    public function provideInvalidAccessData()
    {
        return [
            [null, 'random', Response::HTTP_UNAUTHORIZED],
            [null, 'extension', Response::HTTP_UNAUTHORIZED],
            [self::$USER, 'random', Response::HTTP_NOT_FOUND],
            [self::$USER, 'extension', Response::HTTP_FORBIDDEN],
            [self::$ADMIN, 'random', Response::HTTP_NOT_FOUND],
            [self::$OTHER_ADMIN, 'extension', Response::HTTP_FORBIDDEN],
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
        if ($checkpointKey === 'extension') {
            $uri = '/extensions/' . $this->extension->id;
        } else {
            $uri = '/extensions/' . Uuid::uuid4()->toString();
        }
        $this->json('PATCH', $uri);
        $this->seeStatusCode($statusCode);
        $this->seeHeader('Content-Type', 'application/json');
    }

    public function provideValidEntities()
    {
        return [
            [self::$ADMIN, 'image', 'image', '1'],
            [self::$SUPER_ADMIN, 'image', 'image', '123'],
            [self::$ADMIN, 'type', 'type', 'media'],
            [self::$ADMIN, 'type', 'type', 'signature'],
            [self::$SUPER_ADMIN, 'type', 'type', 'media'],
            [self::$SUPER_ADMIN, 'type', 'type', 'signature'],
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

        $this->json('PATCH', '/extensions/' . $this->extension->id, $data);

        $this->seeStatusCode(Response::HTTP_NO_CONTENT);
        $this->seeInDatabase('picture_extensions', [
            'id' => $this->pictureExtension->id,
            $dbAttribute => $value,
        ]);
    }

    public function provideInvalidEntities()
    {
        return [
            [self::$ADMIN, 'image', null],
            [self::$ADMIN, 'image', false],
            [self::$ADMIN, 'type', null],
            [self::$ADMIN, 'type', false]
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

        $this->json('PATCH', '/extensions/' . $this->extension->id, $data);

        $this->seeStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->seeHeader('Content-Type', 'application/json');
    }
}
