<?php

use App\CompanySubscription;
use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;
use Ramsey\Uuid\Uuid;

class ViewCompanySubscriptionTest extends TestCase
{
    use DatabaseMigrations;

    public function provideInvalidAccessData()
    {
        return [
            [null, 'random', Response::HTTP_UNAUTHORIZED],
            [null, 'subscription', Response::HTTP_UNAUTHORIZED],
            [self::$USER, 'random', Response::HTTP_NOT_FOUND],
            [self::$USER, 'subscription', Response::HTTP_FORBIDDEN],
            [self::$ADMIN, 'subscription', Response::HTTP_FORBIDDEN, false],
            [self::$OTHER_USER, 'subscription', Response::HTTP_FORBIDDEN],
            [self::$OTHER_ADMIN, 'subscription', Response::HTTP_FORBIDDEN],
        ];
    }

    /**
     * @param string $userKey
     * @param string $subscriptionIdKey
     * @param int $statusCode
     * @param boolean $validSubscription
     * @dataProvider provideInvalidAccessData
     * @throws Exception
     */
    public function testInvalidAccess($userKey, $subscriptionIdKey, $statusCode,
                                      $validSubscription = true)
    {
        if ($userKey != null) {
            $user = $this->getUser($userKey);
            $this->actingAs($user);
            if (!$validSubscription) {
                $subscription = $user->company->companySubscription;
                $subscription->viewCompanySubscription = false;
                $subscription->save();
            }
        }
        if ($subscriptionIdKey === 'subscription') {
            $uri = '/subscriptions/' . $this->company->companySubscription->id;
        } else {
            $uri = '/subscriptions/' . Uuid::uuid4()->toString();
        }
        $this->json('GET', $uri);
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
     * @param $userKey
     * @dataProvider provideValidAccessData
     */
    public function testValidAccess($userKey)
    {
        $user = $this->getUser($userKey);
        $this->actingAs($user);
        $this->json('GET', '/subscriptions/' . $this->company->companySubscription->id);
        $this->seeStatusCode(Response::HTTP_OK);
        $this->seeHeader('Content-Type', 'application/json');
        $structure = [
            'id',
            'viewCompanySubscription',
        ];
        $content = [
            'viewCompanySubscription' => $this->company->companySubscription->viewCompanySubscription,
        ];
        foreach (CompanySubscription::PROTECTED_MODELS as $model) {
            $name = substr(strrchr($model, '\\'), 1);
            $structure[] ='index' . $name;
            $structure[] ='view' . $name;
            $structure[] ='create' . $name;
            $structure[] ='update' . $name;
            $structure[] ='delete' . $name;
            $content['index' . $name] = $this->company->companySubscription->{'index' . $name};
            $content['index' . $name] = $this->company->companySubscription->{'index' . $name};
            $content['view' . $name] = $this->company->companySubscription->{'view' . $name};
            $content['create' . $name] = $this->company->companySubscription->{'create' . $name};
            $content['update' . $name] = $this->company->companySubscription->{'update' . $name};
            $content['delete' . $name] = $this->company->companySubscription->{'delete' . $name};
        }
        $this->seeJsonStructure([
            'data' => $structure,
        ])->seeJsonNotNull([
            'data' => [
                'id',
            ],
        ])->seeJsonContains($content);
    }
}
