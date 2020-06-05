<?php

use App\CompanySubscription;
use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Ramsey\Uuid\Uuid;

class UpdateSubscriptionTest extends TestCase
{
    use DatabaseMigrations;

    public function provideInvalidAccessData() {
        return [
            [null, 'random', Response::HTTP_UNAUTHORIZED],
            [null, 'subscription', Response::HTTP_UNAUTHORIZED],
            [self::$USER, 'random', Response::HTTP_NOT_FOUND],
            [self::$USER, 'subscription', Response::HTTP_FORBIDDEN],
            [self::$ADMIN, 'subscription', Response::HTTP_FORBIDDEN],
            [self::$OTHER_USER, 'subscription', Response::HTTP_FORBIDDEN],
            [self::$OTHER_ADMIN, 'subscription', Response::HTTP_FORBIDDEN],
        ];
    }

    /**
     *
     * @param string $userKey
     * @param boolean $subscriptionIdKey
     * @param int $statusCode
     * @dataProvider provideInvalidAccessData
     * @throws Exception
     */
    public function testInvalidAccess($userKey, $subscriptionIdKey, $statusCode) {
        if ($userKey != null) {
            $user = $this->getUser($userKey);
            $this->actingAs($user);
        }
        if ($subscriptionIdKey === 'subscription') {
            $uri = '/subscriptions/' . $this->company->companySubscription->id;
        } else {
            $uri = '/subscriptions/' . Uuid::uuid4()->toString();
        }
        $this->json('PATCH', $uri);
        $this->seeStatusCode($statusCode);
        $this->seeHeader('Content-Type', 'application/json');
    }

    public function provideValidEntities() {
        $tests = [
            [self::$SUPER_ADMIN, 'viewCompanySubscription', 'viewCompanySubscription', false],
        ];
        foreach (CompanySubscription::PROTECTED_MODELS as $model) {
            $name = substr(strrchr($model, '\\'), 1);
            $tests[] = [self::$SUPER_ADMIN, 'index' . $name, 'index' . $name, false];
            $tests[] = [self::$SUPER_ADMIN, 'view' . $name, 'view' . $name, false];
            $tests[] = [self::$SUPER_ADMIN, 'create' . $name, 'create' . $name, false];
            $tests[] = [self::$SUPER_ADMIN, 'update' . $name, 'update' . $name, false];
            $tests[] = [self::$SUPER_ADMIN, 'delete' . $name, 'delete' . $name, false];
        }
        return $tests;
    }

    /**
     * @param $userKey
     * @param $attribute
     * @param $dbAttribute
     * @param $value
     * @dataProvider provideValidEntities
     */
    public function testValidEntities($userKey, $attribute, $dbAttribute, $value) {
        $user = $this->getUser($userKey);
        $this->actingAs($user);

        $data = [$attribute => $value];

        $this->json('PATCH', '/subscriptions/' . $user->company->companySubscription->id, $data);

        $this->seeStatusCode(Response::HTTP_NO_CONTENT);
        $this->seeInDatabase('company_subscriptions', [
            'id' => $user->company->companySubscription->id,
            $dbAttribute => $value
        ]);
    }

    public function provideInvalidEntities() {
        $tests = [
            [self::$SUPER_ADMIN, 'viewCompanySubscription', null],
            [self::$SUPER_ADMIN, 'viewCompanySubscription', 123],
            [self::$SUPER_ADMIN, 'viewCompanySubscription', 'string'],
        ];
        foreach (CompanySubscription::PROTECTED_MODELS as $model) {
            $name = substr(strrchr($model, '\\'), 1);
            $tests[] = [self::$SUPER_ADMIN, 'view' . $name, null];
            $tests[] = [self::$SUPER_ADMIN, 'view' . $name, 123];
            $tests[] = [self::$SUPER_ADMIN, 'view' . $name, 'string'];
            $tests[] = [self::$SUPER_ADMIN, 'index' . $name, null];
            $tests[] = [self::$SUPER_ADMIN, 'index' . $name, 123];
            $tests[] = [self::$SUPER_ADMIN, 'index' . $name, 'string'];
            $tests[] = [self::$SUPER_ADMIN, 'create' . $name, null];
            $tests[] = [self::$SUPER_ADMIN, 'create' . $name, 123];
            $tests[] = [self::$SUPER_ADMIN, 'create' . $name, 'string'];
            $tests[] = [self::$SUPER_ADMIN, 'update' . $name, null];
            $tests[] = [self::$SUPER_ADMIN, 'update' . $name, 123];
            $tests[] = [self::$SUPER_ADMIN, 'update' . $name, 'string'];
            $tests[] = [self::$SUPER_ADMIN, 'delete' . $name, null];
            $tests[] = [self::$SUPER_ADMIN, 'delete' . $name, 123];
            $tests[] = [self::$SUPER_ADMIN, 'delete' . $name, 'string'];
        }
        return $tests;
    }

    /**
     * @param $userKey
     * @param $attribute
     * @param $value
     * @dataProvider provideInvalidEntities
     */
    public function testInvalidEntities($userKey, $attribute, $value) {
        $user = $this->getUser($userKey);
        $this->actingAs($user);
        $data = [$attribute => $value];
        $this->json('PATCH', '/subscriptions/' .
            $this->company->companySubscription->id, $data);
        $this->seeStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->seeHeader('Content-Type', 'application/json');
    }
}
