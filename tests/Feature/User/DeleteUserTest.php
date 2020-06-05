<?php

use App\User;
use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Ramsey\Uuid\Uuid;

class DeleteUserTest extends TestCase
{
    use DatabaseMigrations;

    public function provideInvalidAccessData()
    {
        return [
            [null, 'random', Response::HTTP_UNAUTHORIZED],
            [null, 'user', Response::HTTP_UNAUTHORIZED],
            [self::$USER, 'random', Response::HTTP_NOT_FOUND],
            [self::$USER, 'user', Response::HTTP_FORBIDDEN, false],
            [self::$ADMIN, 'user', Response::HTTP_FORBIDDEN, false],
            [self::$OTHER_USER, 'user', Response::HTTP_FORBIDDEN],
            [self::$OTHER_ADMIN, 'user', Response::HTTP_FORBIDDEN],
        ];
    }

    /**
     * @param string $userKey
     * @param boolean $userIdKey
     * @param int $statusCode
     * @param boolean $validSubscription
     * @dataProvider provideInvalidAccessData
     */
    public function testInvalidAccess($userKey, $userIdKey, $statusCode,
                                      $validSubscription = true)
    {
        if ($userKey != null) {
            $user = $this->getUser($userKey);
            $this->actingAs($user);
            if (!$validSubscription) {
                $subscription = $user->company->companySubscription;
                $subscription->deleteUser = false;
                $subscription->save();
            }
        }
        if ($userIdKey === 'user') {
            $uri = '/users/' . $this->user->id;
        } else {
            $uri = '/users/' . Uuid::uuid4()->toString();
        }
        $this->json('DELETE', $uri);
        $this->seeStatusCode($statusCode);
        $this->seeHeader('Content-Type', 'application/json');
    }

    public function provideValidAccessData()
    {
        return [
            [self::$USER],
            [self::$ADMIN],
            [self::$SUPER_ADMIN],
        ];
    }

    /**
     * @param $userKey
     * @dataProvider provideValidAccessData
     */
    public function testValidAccess($userKey)
    {
        $user = $this->getUser($userKey);
        $this->actingAs($user);
        $this->json('DELETE', '/users/' . $this->user->id);
        $this->seeStatusCode(Response::HTTP_NO_CONTENT);
        $this->assertSoftDeleted(
            'users',
            ['id' => $this->user->id],
            User::DELETED_AT);
    }
}
