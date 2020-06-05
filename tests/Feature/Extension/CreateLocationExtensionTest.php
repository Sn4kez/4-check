<?php

use App\Country;
use App\LocationExtension;
use App\LocationType;
use App\LocationState;
use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Ramsey\Uuid\Uuid;

class CreateLocationExtensionTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * @var \App\Checkpoint
     */
    protected $checkpoint;

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
        $this->checkpoint = $this->makeFakeCheckpoint();
        $this->checkpoint->scoringScheme()->associate($scoringScheme);
        $evaluationScheme = $this->makeFakeChoiceScheme();
        $evaluationScheme->save();
        $this->checkpoint->evaluationScheme()->associate($evaluationScheme);
        $this->checkpoint->save();
        $section->entry($this->checkpoint)->save();
        $this->location = $this->makeFakeLocation();
        $this->location->type()->associate(LocationType::where('name', '=', 'building')->first());
        $this->location->state()->associate(LocationState::where('name', '=', 'active')->first());
        $this->location->company()->associate($this->company);
        $this->location->country()->associate(Country::find('de'));
        $this->location->save();
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
        /** @var LocationExtension $extension1 */
        $extension1 = factory(LocationExtension::class)->make();
        $extension1->location()->associate($this->location);
        /** @var LocationExtension $extension2 */
        $extension2 = factory(LocationExtension::class)->make();
        $extension2->location()->associate($this->location);
        $uri = '/checkpoints/' . $this->checkpoint->id . '/extensions';
        $this->json('POST', $uri, [
            'type' => 'location',
            'data' => [
                'locationId' => $extension1->location->id,
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
                    'locationId',
                    'fixed',
                ],
            ],
        ])->seeJsonNotNull([
            'data' => [
                'id',
            ],
        ])->seeJsonContains([
            'type' => 'location',
            'object' => [
                'locationId' => $extension1->location->id,
                'fixed' => $extension1->fixed,
            ],
        ]);
        $this->json('POST', $uri, [
            'type' => 'location',
            'data' => [
                'locationId' => $extension2->location->id,
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
                    'locationId',
                    'fixed',
                ],
            ],
        ])->seeJsonNotNull([
            'data' => [
                'id',
            ],
        ])->seeJsonContains([
            'type' => 'location',
            'object' => [
                'locationId' => $extension2->location->id,
                'fixed' => $extension2->fixed,
            ],
        ]);
        $this->seeInDatabase('location_extensions', [
            'locationId' => $extension1->location->id,
            'fixed' => $extension1->fixed,
        ])->seeInDatabase('location_extensions', [
            'locationId' => $extension2->location->id,
            'fixed' => $extension2->fixed,
        ]);
        $this->assertCount(2, $this->checkpoint->entries);
    }

    public function provideValidEntities()
    {
        return [
            ['locationId', null],
        ];
    }

    /**
     * @param $attribute
     * @param $value
     * @dataProvider provideValidEntities
     */
    public function testValidEntities($attribute, $value)
    {
        /** @var LocationExtension $extension */
        $extension= factory(LocationExtension::class)->make();
        $extension->location()->associate($this->location);
        $data = array_merge([
            'locationId' => $extension->location->id,
            'fixed' => $extension->fixed,
        ], [$attribute => $value]);
        $this->actingAs($this->admin);
        $url = '/checkpoints/' . $this->checkpoint->id . '/extensions';
        $this->json('POST', $url, [
            'type' => 'location',
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
        $this->seeInDatabase('location_extensions', [
            $attribute => $value,
        ]);
    }

    public function provideInvalidEntities()
    {
        return [
            ['locationId', 'unknown'],
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
        /** @var LocationExtension $extension */
        $extension= factory(LocationExtension::class)->make();
        $extension->location()->associate($this->location);
        $data = array_merge([
            'locationId' => $extension->location->id,
            'fixed' => $extension->fixed,
        ], [$attribute => $value]);
        $this->actingAs($this->admin);
        $uri = '/checkpoints/' . $this->checkpoint->id . '/extensions';
        $this->json('POST', $uri, [
            'type' => 'location',
            'data' => $data,
        ]);
        $this->seeStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->seeHeader('Content-Type', 'application/json');
    }
}
