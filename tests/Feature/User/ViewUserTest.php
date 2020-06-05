<?php

use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Ramsey\Uuid\Uuid;
use App\Media;

class ViewUserTest extends TestCase
{
    use DatabaseMigrations;

    private $mediaBase64ContentCheckString;

    public function setUp()
    {
        parent::setUp();

        $uploadFeedback = Media::uploadUnitTestTestImage(sprintf("%s/files/logo.jpg", dirname(__FILE__)));
        $this->mediaBase64ContentCheckString = $uploadFeedback["base64Content"];
        $mediaName = $uploadFeedback["mediaName"];

        $this->user->image = $mediaName;
        $this->user->save();
    }

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
     * @throws Exception
     */
    public function testInvalidAccess($userKey, $userIdKey, $statusCode,
                                      $validSubscription = true)
    {

        if ($userKey != null) {
            $user = $this->getUser($userKey);
            $this->actingAs($user);
            if (!$validSubscription) {
                $subscription = $user->company->companySubscription;
                $subscription->viewUser = false;
                $subscription->save();
            }
        }
        if ($userIdKey === 'user') {
            $uri = '/users/' . $this->user->id;
        } else {
            $uri = '/users/' . Uuid::uuid4()->toString();
        }
        $this->json('GET', $uri);
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

        $this->json('GET', '/users/' . $this->user->id);
        $this->seeStatusCode(Response::HTTP_OK);
        $this->seeHeader('Content-Type', 'application/json');
        $this->seeJsonStructure([
            'data' => [
                'id',
                'email',
                'firstName',
                'middleName',
                'lastName',
                'gender',
                'locale',
                'timezone',
                'image'
            ],
        ])->seeJsonNotNull([
            'data' => [
                'id',
                'image'
            ],
        ])->seeJsonContains([
            'email' => $this->user->email,
            'firstName' => $this->user->firstName,
            'middleName' => $this->user->middleName,
            'lastName' => $this->user->lastName,
            'gender' => $this->user->gender->id,
            'locale' => $this->user->locale->id,
            'timezone' => $this->user->timezone
        ]);
    }
}
