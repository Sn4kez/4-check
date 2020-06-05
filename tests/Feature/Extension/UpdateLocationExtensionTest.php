<?php

use App\Country;
use App\LocationType;
use App\LocationState;
use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Ramsey\Uuid\Uuid;

class UpdateLocationExtensionTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * @var \App\Extension
     */
    protected $extension;

    /**
     * @var \App\LocationExtension
     */
    protected $locationExtension;

    /**
     * @var \App\Location
     */
    protected $location;

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
        $this->location = $this->makeFakeLocation();
        $this->location->type()->associate(LocationType::where('name', '=', 'building')->first());
        $this->location->state()->associate(LocationState::where('name', '=', 'active')->first());
        $this->location->company()->associate($this->company);
        $this->location->country()->associate(Country::find('de'));
        $this->location->save();
        $this->locationExtension = $this->makeFakeLocationExtension();
        $this->locationExtension->location()->associate($this->location);
        $this->locationExtension->save();
        $this->extension = $checkpoint->extension($this->locationExtension);
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
            [self::$ADMIN, 'locationId', 'locationId', 'locationId'],
            [self::$SUPER_ADMIN, 'locationId', 'locationId', 'locationId'],
            [self::$ADMIN, 'fixed', 'fixed', true],
            [self::$SUPER_ADMIN, 'fixed', 'fixed', false],
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
        if ($value === 'locationId') {
            $value = $this->location->id;
        }
        $user = $this->getUser($userKey);
        $this->actingAs($user);
        $data = [
            'data' => [
                $attribute => $value
            ]
        ];
        $this->json('PATCH', '/extensions/' . $this->extension->id, $data);
        $this->seeStatusCode(Response::HTTP_NO_CONTENT);
        $this->seeInDatabase('location_extensions', [
            'id' => $this->locationExtension->id,
            $dbAttribute => $value,
        ]);
    }

    public function provideInvalidEntities()
    {
        return [
            [self::$ADMIN, 'locationId', UUID::uuid4()],
            [self::$ADMIN, 'fixed', null],
            [self::$ADMIN, 'fixed', 123],
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
        $data = [
            'data' => [
                $attribute => $value
            ]
        ];
        $this->json('PATCH', '/extensions/' . $this->extension->id, $data);
        $this->seeStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->seeHeader('Content-Type', 'application/json');
    }
}
