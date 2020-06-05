<?php

use App\Rule;
use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Ramsey\Uuid\Uuid;

class UpdateUserTest extends TestCase
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
                $subscription->updateUser = false;
                $subscription->save();
            }
        }
        if ($userIdKey === 'user') {
            $uri = '/users/' . $this->user->id;
        } else {
            $uri = '/users/' . Uuid::uuid4()->toString();
        }
        $this->json('PATCH', $uri);
        $this->seeStatusCode($statusCode);
        $this->seeHeader('Content-Type', 'application/json');
    }

    public function provideValidEntities()
    {
        return [
            [self::$USER, 'all', '', ''],
            [self::$USER, 'source_b64', 'source_b64', 'logo.jpg'],
            [self::$USER, 'source_b64', 'source_b64', null],
            [self::$USER, 'email', 'email', self::$USER_MAIL], // Unchanged
            [self::$USER, 'email', 'email', 'test@4check.com'],
            [self::$ADMIN, 'email', 'email', 'test@4check.com'],
            [self::$SUPER_ADMIN, 'email', 'email', 'test@4check.com'],
            [self::$USER, 'firstName', 'firstName', null],
            [self::$ADMIN, 'firstName', 'firstName', null],
            [self::$SUPER_ADMIN, 'firstName', 'firstName', null],
            [self::$USER, 'firstName', 'firstName', 'firstName'],
            [self::$ADMIN, 'firstName', 'firstName', 'firstName'],
            [self::$SUPER_ADMIN, 'firstName', 'firstName', 'firstName'],
            [self::$USER, 'middleName', 'middleName', null],
            [self::$ADMIN, 'middleName', 'middleName', null],
            [self::$SUPER_ADMIN, 'middleName', 'middleName', null],
            [self::$USER, 'middleName', 'middleName', 'middleName'],
            [self::$ADMIN, 'middleName', 'middleName', 'middleName'],
            [self::$SUPER_ADMIN, 'middleName', 'middleName', 'middleName'],
            [self::$USER, 'lastName', 'lastName', null],
            [self::$ADMIN, 'lastName', 'lastName', null],
            [self::$SUPER_ADMIN, 'lastName', 'lastName', null],
            [self::$USER, 'lastName', 'lastName', 'lastName'],
            [self::$ADMIN, 'lastName', 'lastName', 'lastName'],
            [self::$SUPER_ADMIN, 'lastName', 'lastName', 'lastName'],
            [self::$USER, 'gender', 'genderId', 'male'],
            [self::$ADMIN, 'gender', 'genderId', 'female'],
            [self::$USER, 'locale', 'localeId', 'de'],
            [self::$ADMIN, 'locale', 'localeId', 'de'],
            [self::$SUPER_ADMIN, 'locale', 'localeId', 'de'],
            [self::$USER, 'timezone', 'timezone', 'Europe/Berlin'],
            [self::$ADMIN, 'timezone', 'timezone', 'Europe/Berlin'],
            [self::$SUPER_ADMIN, 'timezone', 'timezone', 'Europe/Berlin'],
            [self::$ADMIN, 'role', 'roleId', 'admin'],
            [self::$SUPER_ADMIN, 'role', 'roleId', 'admin'],
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

        if ($attribute === "source_b64" && !is_null($value)) {
            $fileLocation = sprintf("%s/files/%s", dirname(__FILE__), $value);

            if (file_exists($fileLocation)) {
                $value = base64_encode(file_get_contents($fileLocation));
            }
        }

        $data = [$attribute => $value];

        if ($attribute === 'all') {
            $data = [
                'email' => 'phpunit@4-check.com',
                'gender' => 'male',
                'firstName' => 'Chris1',
                'lastName' => 'Schrut',
                'middleName' => null,
                'locale' => 'de',
                'timezone' => 'Europe/Berlin',
                'image' => null,
                //'role' => 'admin',
                'isActive' => '1'
            ];
        }

        $this->json('PATCH', '/users/' . $this->user->id, $data);
        $this->seeStatusCode(Response::HTTP_NO_CONTENT);

        if (!in_array($attribute, ['password', 'source_b64', 'all'])) {
            $this->seeInDatabase('users', [
                'id' => $this->user->id,
                $dbAttribute => $value,
            ]);
        }
    }

    public function provideInvalidEntities()
    {
        return [
            [self::$USER, 'email', 123],
            [self::$USER, 'email', str_repeat('Long', 32) . 'Name'],
            [self::$USER, 'email', 'invalid@mail'],
            [self::$USER, 'firstName', 123],
            [self::$USER, 'firstName', str_repeat('Long', 32) . 'Name'],
            [self::$USER, 'middleName', 123],
            [self::$USER, 'middleName', str_repeat('Long', 32) . 'Name'],
            [self::$USER, 'lastName', 123],
            [self::$USER, 'lastName', str_repeat('Long', 32) . 'Name'],
            [self::$USER, 'gender', null],
            [self::$USER, 'gender', 123],
            [self::$USER, 'gender', 'unknown'],
            [self::$USER, 'locale', null],
            [self::$USER, 'locale', 123],
            [self::$USER, 'locale', 'unknown'],
            [self::$USER, 'timezone', null],
            [self::$USER, 'timezone', 123],
            [self::$USER, 'timezone', 'unknown'],
            [self::$ADMIN, 'role', 'superadmin'],
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
        $this->json('PATCH', '/users/' . $this->user->id, $data);
        $this->seeStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->seeHeader('Content-Type', 'application/json');
    }

    public function provideValidPasswords()
    {
        return [
            [self::$USER, 'Secret456'],
            [self::$ADMIN, 'Secret456'],
            [self::$SUPER_ADMIN, 'Secret456'],
        ];
    }

    /**
     * @param $userKey
     * @param $value
     * @dataProvider provideValidPasswords
     */
    public function testChangePasswordValid($userKey, $value)
    {
        $user = $this->getUser($userKey);
        $this->actingAs($user);
        $data = [
            'newPassword' => $value,
            'currentPassword' => self::$PASSWORD,
        ];
        $this->json('PATCH', '/users/' . $this->user->id, $data);
        $this->seeStatusCode(Response::HTTP_NO_CONTENT);
    }

    public function provideInvalidPasswords()
    {
        return [
            [self::$USER, null],
            [self::$USER, 'Sh0rt'],
            [self::$USER, 'Secret 123'],
            [self::$USER, 'n0upperc4se'],
            [self::$USER, 'N0LOWERC4SE'],
            [self::$USER, 'NoNumber'],
        ];
    }

    /**
     * @param $userKey
     * @param $value
     * @dataProvider provideInvalidPasswords
     */
    public function testChangePasswordInvalid($userKey, $value)
    {
        $user = $this->getUser($userKey);
        $this->actingAs($user);
        $data = [
            'newPassword' => $value,
            'currentPassword' => self::$PASSWORD,
        ];
        $this->json('PATCH', '/users/' . $this->user->id, $data);
        $this->seeStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->seeHeader('Content-Type', 'application/json');
    }

    public function provideRoles()
    {
        return [
            [self::$USER, 'user'],
            [self::$USER, 'admin'],
        ];
    }
}
