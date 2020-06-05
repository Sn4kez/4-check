<?php

use App\NotificationPreferences;
use App\Gender;
use App\User;

use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Ramsey\Uuid\Uuid;

class ViewNotificationPreferencesTest extends TestCase
{
    use DatabaseMigrations;
    
    /**
     * @var \App\NotificationPreferences $preferences
     */
    protected $preferences;

    public function setUp()
    {
        parent::setUp();

        $this->preferences = new NotificationPreferences();
        $this->preferences->id = Uuid::uuid4()->toString();

        $this->preferences->checklistNeedsActivityNotification = 1;
        $this->preferences->checklistCompletedNotification = 1;
        $this->preferences->checklistDueNotification = 1;
        $this->preferences->checklistAssignedNotification = 1;
        $this->preferences->checklistCriticalRatingNotification = 1;

        $this->preferences->taskCompletedNotification = 1;
        $this->preferences->taskAssignedNotification = 1;
        $this->preferences->taskUpdatedNotification = 1;
        $this->preferences->taskDeletedNotification = 1;

        $this->preferences->checklistNeedsActivityMail = 1;
        $this->preferences->checklistCompletedMail = 1;
        $this->preferences->checklistDueMail = 1;
        $this->preferences->checklistAssignedMail = 1;
        $this->preferences->checklistCriticalRatingMail = 1;

        $this->preferences->taskCompletedMail = 1;
        $this->preferences->taskAssignedMail = 1;
        $this->preferences->taskUpdatedMail = 1;
        $this->preferences->taskDeletedMail = 1;

        $this->preferences->auditAssignedNotification = 1;
        $this->preferences->auditCompletedNotification = 1;
        $this->preferences->auditOverdueNotification = 1;
        $this->preferences->auditReleaseRequiredNotification = 1;

        $this->preferences->user()->associate($this->user);
        $this->preferences->save();
    }

    public function provideInvalidAccessData()
    {
        return [
            [null, Response::HTTP_UNAUTHORIZED],
            [self::$ADMIN, Response::HTTP_FORBIDDEN],
            [self::$OTHER_ADMIN, Response::HTTP_FORBIDDEN],
        ];
    }

    /**
     * @param string $userKey
     * @param string $userIdKey
     * @param int $statusCode
     * @dataProvider provideInvalidAccessData
     * @group notepref
     */

    public function testInvalidAccess($userKey, $statusCode)
    {
        if ($userKey != null) {
            $user = $this->getUser($userKey);
            $this->actingAs($user);
        }

        $this->json('GET', '/users/preferences/notifications/' . $this->user->id);
        $this->seeStatusCode($statusCode);
        $this->seeHeader('Content-Type', 'application/json');
    }

    public function provideValidAccessData()
    {
        return [
            [self::$USER],
            [self::$SUPER_ADMIN],
        ];
    }

    /**
     * @param $userKey
     * @dataProvider provideValidAccessData
     * @group notepref
     */
    public function testValidAccess($userKey)
    {
        $user = $this->getUser($userKey);
        $this->actingAs($user);
        $this->json('GET', '/users/preferences/notifications/' . $this->user->id);
        $this->seeStatusCode(Response::HTTP_OK);
        $this->seeHeader('Content-Type', 'application/json');
        $this->seeJsonStructure([
            'data' => [
                'id',
                'checklistNeedsActivityNotification',
                'checklistCompletedNotification',
                'checklistDueNotification',
                'checklistAssignedNotification',
                'taskCompletedNotification',
                'taskAssignedNotification'
            ],
        ])->seeJsonNotNull([
            'data' => [
                'id',
                'checklistNeedsActivityNotification',
                'checklistCompletedNotification',
                'checklistDueNotification',
                'checklistAssignedNotification',
                'taskCompletedNotification',
                'taskAssignedNotification'
            ],
        ])->seeJsonContains([
            'id' => $this->preferences->id,
            'checklistNeedsActivityNotification' => (string) $this->preferences->checklistNeedsActivityNotification,
            'checklistCompletedNotification' => (string) $this->preferences->checklistCompletedNotification,
            'checklistDueNotification' => (string) $this->preferences->checklistDueNotification,
            'checklistAssignedNotification' => (string) $this->preferences->checklistAssignedNotification,
            'taskCompletedNotification' => (string) $this->preferences->taskCompletedNotification,
            'taskAssignedNotification' => (string) $this->preferences->taskAssignedNotification
        ]);
    }
}
