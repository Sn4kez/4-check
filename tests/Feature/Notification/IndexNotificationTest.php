<?php

use App\Notification;
use Symfony\Component\HttpFoundation\Response;

class IndexNotificationTest extends TestCase
{
    use \Laravel\Lumen\Testing\DatabaseMigrations;

    const DEFAULT_LIMIT = 20;

    /**
     * @group notifications
     */
    public function testValidAccessDefault()
    {
        $this->checkValidAccess('/notifications', self::$USER);
    }

    public function provideValidAccessWithLimit()
    {
        return [
            [10, self::$USER],
            [20, self::$USER],
            [30, self::$USER]
        ];
    }

    public function checkValidAccess($route, $userKey)
    {
        $user = $this->getUser($userKey);
        $this->actingAs($user);

        $this->json('GET', $route);

        $this->seeJsonHeader();
        $this->seeStatusCode(Response::HTTP_OK);
    }

    /**
     * Returns a condition for a json header
     */
    private function seeJsonHeader()
    {
        $this->seeHeader('Content-Type', 'application/json');
    }
}