<?php

use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;
use \App\Notification;
use \Ramsey\Uuid\Uuid;

class UpdateNotificationTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * @var \App\Notification
     */
    protected $notification;

    public function setUp()
    {
        parent::setUp();

        $this->notification = factory(Notification::class)->make();
        $this->notification->id = Uuid::uuid4()->toString();
        $this->notification->save();
    }

    public function provideValidEntities()
    {
        return [
            [self::$USER, 1],
            [self::$OTHER_USER, 1],
            [self::$OTHER_ADMIN, 1],
            [self::$SUPER_ADMIN, 1],
            [self::$USER, 0],
            [self::$OTHER_USER, 0],
            [self::$OTHER_ADMIN, 0],
            [self::$SUPER_ADMIN, 0],
        ];
    }

    /**
     * @group notifications
     * @dataProvider provideValidEntities
     * @param $userKey
     * @param $read
     */
    public function testValidEntities($userKey, $read)
    {
        $this->actingAs($this->getUser($userKey));

        $this->json('PATCH', '/notifications/read/' . $this->notification->id, ['read' => $read]);
        $this->seeStatusCode(Response::HTTP_NO_CONTENT);

        /**
         * Check if the id is matching with an entry in the database.
         * Also check now if read shall was set to the correct value
         */
        $this->seeInDatabase('notifications', ['read' => $read, 'id' => $this->notification->id]);
    }

    /**
     * Provide valid test data
     *
     * @return array
     */
    public function provideInvalidAccessData()
    {
        $feedback = [];

        foreach ([self::$USER, self::$OTHER_ADMIN, self::$SUPER_ADMIN] as $userKey) {
            foreach ([0, 1] as $read) {
                $feedback[] = [$userKey, 1, Response::HTTP_NOT_FOUND, $read];
                $feedback[] = [$userKey, 2, Response::HTTP_NOT_FOUND, $read];
                $feedback[] = [$userKey, 3, Response::HTTP_NOT_FOUND, $read];
            }
        }

        return $feedback;
    }

    /**
     * @param $userKey
     * @param $notificationId
     * @param $awaitedResponse
     * @param $read
     * @dataProvider provideInvalidAccessData
     * @group notifications
     */
    public function testInvalidAccess($userKey, $notificationId, $awaitedResponse, $read)
    {
        $this->actingAs($this->getUser($userKey));

        $this->json('PATCH', '/notifications/read/' . $notificationId, ['read' => $read]);
        $this->seeStatusCode($awaitedResponse);
    }
}