<?php

use App\NotificationPreferences;

use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Ramsey\Uuid\Uuid;

class UpdateNotificationPreferencesTest extends TestCase
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

        $this->json('PATCH', '/users/preferences/notifications/' . $this->user->id);
        $this->seeStatusCode($statusCode);
        $this->seeHeader('Content-Type', 'application/json');
    }

    public function provideValidEntities()
    {
        return [
            [self::$USER, 'checklistNeedsActivityNotification', 'checklistNeedsActivityNotification', 0],
            [self::$SUPER_ADMIN, 'checklistNeedsActivityNotification', 'checklistNeedsActivityNotification', 0],
            [self::$USER, 'checklistNeedsActivityNotification', 'checklistNeedsActivityNotification', 1],
            [self::$SUPER_ADMIN, 'checklistNeedsActivityNotification', 'checklistNeedsActivityNotification', 1],
            [self::$USER, 'checklistCompletedNotification', 'checklistCompletedNotification', 0],
            [self::$SUPER_ADMIN, 'checklistCompletedNotification', 'checklistCompletedNotification', 0],
            [self::$USER, 'checklistCompletedNotification', 'checklistCompletedNotification', 1],
            [self::$SUPER_ADMIN, 'checklistCompletedNotification', 'checklistCompletedNotification', 1],
            [self::$USER, 'checklistDueNotification', 'checklistDueNotification', 0],
            [self::$SUPER_ADMIN, 'checklistDueNotification', 'checklistDueNotification', 0],
            [self::$USER, 'checklistDueNotification', 'checklistDueNotification', 1],
            [self::$SUPER_ADMIN, 'checklistDueNotification', 'checklistDueNotification', 1],
            [self::$USER, 'checklistAssignedNotification', 'checklistAssignedNotification', 0],
			[self::$SUPER_ADMIN, 'checklistAssignedNotification', 'checklistAssignedNotification', 0],
			[self::$USER, 'checklistAssignedNotification', 'checklistAssignedNotification', 1],
			[self::$SUPER_ADMIN, 'checklistAssignedNotification', 'checklistAssignedNotification', 1],
            [self::$USER, 'checklistCriticalRatingNotification', 'checklistCriticalRatingNotification', 0],
            [self::$SUPER_ADMIN, 'checklistCriticalRatingNotification', 'checklistCriticalRatingNotification', 0],
            [self::$USER, 'checklistCriticalRatingNotification', 'checklistCriticalRatingNotification', 1],
            [self::$SUPER_ADMIN, 'checklistCriticalRatingNotification', 'checklistCriticalRatingNotification', 1],
			[self::$USER, 'taskCompletedNotification', 'taskCompletedNotification', 0],
			[self::$SUPER_ADMIN, 'taskCompletedNotification', 'taskCompletedNotification', 0],
			[self::$USER, 'taskCompletedNotification', 'taskCompletedNotification', 1],
			[self::$SUPER_ADMIN, 'taskCompletedNotification', 'taskCompletedNotification', 1],
			[self::$USER, 'taskAssignedNotification', 'taskAssignedNotification', 0],
			[self::$SUPER_ADMIN, 'taskAssignedNotification', 'taskAssignedNotification', 0],
			[self::$USER, 'taskAssignedNotification', 'taskAssignedNotification', 1],
			[self::$SUPER_ADMIN, 'taskAssignedNotification', 'taskAssignedNotification', 1],
            [self::$USER, 'taskUpdatedNotification', 'taskUpdatedNotification', 0],
            [self::$SUPER_ADMIN, 'taskUpdatedNotification', 'taskUpdatedNotification', 0],
            [self::$USER, 'taskUpdatedNotification', 'taskUpdatedNotification', 1],
            [self::$SUPER_ADMIN, 'taskUpdatedNotification', 'taskUpdatedNotification', 1],
            [self::$USER, 'taskDeletedNotification', 'taskDeletedNotification', 0],
            [self::$SUPER_ADMIN, 'taskDeletedNotification', 'taskDeletedNotification', 0],
            [self::$USER, 'taskDeletedNotification', 'taskDeletedNotification', 1],
            [self::$SUPER_ADMIN, 'taskDeletedNotification', 'taskDeletedNotification', 1],
            [self::$USER, 'checklistNeedsActivityMail', 'checklistNeedsActivityMail', 0],
            [self::$SUPER_ADMIN, 'checklistNeedsActivityMail', 'checklistNeedsActivityMail', 0],
            [self::$USER, 'checklistNeedsActivityMail', 'checklistNeedsActivityMail', 1],
            [self::$SUPER_ADMIN, 'checklistNeedsActivityMail', 'checklistNeedsActivityMail', 1],
            [self::$USER, 'checklistCompletedMail', 'checklistCompletedMail', 0],
            [self::$SUPER_ADMIN, 'checklistCompletedMail', 'checklistCompletedMail', 0],
            [self::$USER, 'checklistCompletedMail', 'checklistCompletedMail', 1],
            [self::$SUPER_ADMIN, 'checklistCompletedMail', 'checklistCompletedMail', 1],
            [self::$USER, 'checklistDueMail', 'checklistDueMail', 0],
            [self::$SUPER_ADMIN, 'checklistDueMail', 'checklistDueMail', 0],
            [self::$USER, 'checklistDueMail', 'checklistDueMail', 1],
            [self::$SUPER_ADMIN, 'checklistDueMail', 'checklistDueMail', 1],
            [self::$USER, 'checklistAssignedMail', 'checklistAssignedMail', 0],
            [self::$SUPER_ADMIN, 'checklistAssignedMail', 'checklistAssignedMail', 0],
            [self::$USER, 'checklistAssignedMail', 'checklistAssignedMail', 1],
            [self::$SUPER_ADMIN, 'checklistAssignedMail', 'checklistAssignedMail', 1],
            [self::$USER, 'checklistCriticalRatingMail', 'checklistCriticalRatingMail', 0],
            [self::$SUPER_ADMIN, 'checklistCriticalRatingMail', 'checklistCriticalRatingMail', 0],
            [self::$USER, 'checklistCriticalRatingMail', 'checklistCriticalRatingMail', 1],
            [self::$SUPER_ADMIN, 'checklistCriticalRatingMail', 'checklistCriticalRatingMail', 1],
            [self::$USER, 'taskCompletedMail', 'taskCompletedMail', 0],
            [self::$SUPER_ADMIN, 'taskCompletedMail', 'taskCompletedMail', 0],
            [self::$USER, 'taskCompletedMail', 'taskCompletedMail', 1],
            [self::$SUPER_ADMIN, 'taskCompletedMail', 'taskCompletedMail', 1],
            [self::$USER, 'taskAssignedMail', 'taskAssignedMail', 0],
            [self::$SUPER_ADMIN, 'taskAssignedMail', 'taskAssignedMail', 0],
            [self::$USER, 'taskAssignedMail', 'taskAssignedMail', 1],
            [self::$SUPER_ADMIN, 'taskAssignedMail', 'taskAssignedMail', 1],
            [self::$USER, 'taskUpdatedMail', 'taskUpdatedMail', 0],
            [self::$SUPER_ADMIN, 'taskUpdatedMail', 'taskUpdatedMail', 0],
            [self::$USER, 'taskUpdatedMail', 'taskUpdatedMail', 1],
            [self::$SUPER_ADMIN, 'taskUpdatedMail', 'taskUpdatedMail', 1],
            [self::$USER, 'taskDeletedMail', 'taskDeletedMail', 0],
            [self::$SUPER_ADMIN, 'taskDeletedMail', 'taskDeletedMail', 0],
            [self::$USER, 'taskDeletedMail', 'taskDeletedMail', 1],
            [self::$SUPER_ADMIN, 'taskDeletedMail', 'taskDeletedMail', 1],

            [self::$USER, 'auditAssignedNotification', 'auditAssignedNotification', 1],
            [self::$USER, 'auditAssignedNotification', 'auditAssignedNotification', 0],
            [self::$SUPER_ADMIN, 'auditAssignedNotification', 'auditAssignedNotification', 1],
            [self::$SUPER_ADMIN, 'auditAssignedNotification', 'auditAssignedNotification', 0],

            [self::$USER, 'auditCompletedNotification', 'auditCompletedNotification', 1],
            [self::$USER, 'auditCompletedNotification', 'auditCompletedNotification', 0],
            [self::$SUPER_ADMIN, 'auditCompletedNotification', 'auditCompletedNotification', 1],
            [self::$SUPER_ADMIN, 'auditCompletedNotification', 'auditCompletedNotification', 0],

            [self::$USER, 'auditOverdueNotification', 'auditOverdueNotification', 1],
            [self::$USER, 'auditOverdueNotification', 'auditOverdueNotification', 0],
            [self::$SUPER_ADMIN, 'auditOverdueNotification', 'auditOverdueNotification', 1],
            [self::$SUPER_ADMIN, 'auditOverdueNotification', 'auditOverdueNotification', 0],

            [self::$USER, 'auditReleaseRequiredNotification', 'auditReleaseRequiredNotification', 1],
            [self::$USER, 'auditReleaseRequiredNotification', 'auditReleaseRequiredNotification', 0],
            [self::$SUPER_ADMIN, 'auditReleaseRequiredNotification', 'auditReleaseRequiredNotification', 1],
            [self::$SUPER_ADMIN, 'auditReleaseRequiredNotification', 'auditReleaseRequiredNotification', 0],
        ];
    }

    /**
     * @param $userKey
     * @param $attribute
     * @param $dbAttribute
     * @param $value
     * @dataProvider provideValidEntities
     * @group notepref
     */
    public function testValidEntities($userKey, $attribute, $dbAttribute, $value)
    {
        $user = $this->getUser($userKey);
        $this->actingAs($user);
       
        $data = [$attribute => $value];
        $this->json('PATCH', '/users/preferences/notifications/' . $this->user->id, $data);
        $this->seeStatusCode(Response::HTTP_NO_CONTENT);
        $this->seeInDatabase('notification_preferences', [
            'id' => $this->preferences->id,
            $dbAttribute => $value,
        ]);
    }
}
