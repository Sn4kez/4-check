<?php

use App\Payment;
use App\UserInvitation;

use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;

class CreateUserInvitationTest extends TestCase {
    use DatabaseMigrations;

    public function provideInvalidAccessData() {
        return [
            [
                null,
                Response::HTTP_UNAUTHORIZED
            ],
            [
                self::$USER,
                Response::HTTP_UNAUTHORIZED
            ],
        ];
    }

    /**
     * @param string $userKey
     * @param int $statusCode
     * @dataProvider provideInvalidAccessData
     */
    public function testInvalidAccess($userKey, $statusCode) {
        $this->json('POST', '/users/invitations');
        $this->seeStatusCode($statusCode);
        $this->seeHeader('Content-Type', 'application/json');
    }

    public function provideValidAccessData() {
        return [
            [self::$ADMIN],
            [self::$SUPER_ADMIN],
        ];
    }

    /**
     * @param string $userKey
     * @dataProvider provideValidAccessData
     */
    public function testValidAccess($userKey) {
        $user = $this->getUser($userKey);
        $this->actingAs($user);

        $user->company->quantity = 1;
        $user->company->save();

        $invitation = new UserInvitation();
        $invitation->email = 'test@test.com';
        $invitation->company()->associate($user->company->id);;

        Payment::setStripeDevToken();
        $user->company->newSubscription(Payment::SUBSCRIPTION_NAME, Payment::getPackageByKey(Payment::SUBSCRIPTION_KEY_PREMIUM_YEARLY_DEV))->create();

        $this->json('POST', '/users/invitations', [
            'email' => $invitation->email,
            'company' => $invitation->company->id,
        ]);
        $this->seeStatusCode(Response::HTTP_CREATED);
        $this->seeHeader('Content-Type', 'application/json');
        $this->seeJsonStructure([
            'data' => [
                'token',
                'email',
                'company',
            ],
        ])->seeJsonNotNull([
            'data' => array_merge([
                'token',
                'email',
                'company'
            ]),
        ])->seeJsonContains([
            'email' => $invitation->email,
            'company' => $invitation->company->id,
        ]);
    }

    public function provideInvalidEntities() {
        return [
            [null],
            [123],
            [str_repeat('123', 128)],
        ];
    }

    /**
     * @param string $value
     * @dataProvider provideInvalidEntities
     */
    public function testInvalidEntities($value) {
        $user = $this->getUser(self::$ADMIN);
        $this->actingAs($user);

        $invitation = new UserInvitation();
        $invitation->email = $value;
        $invitation->company()->associate($user->company->id);;


        $this->json('POST', '/users/invitations', [
            'email' => $invitation->email,
            'company' => $invitation->company->id,
        ]);
        $this->seeStatusCode(Response::HTTP_UNPROCESSABLE_ENTITY);
        $this->seeHeader('Content-Type', 'application/json');
    }

    public function testIncrementSubscriptionUsers() {
        $user = $this->getUser(self::$ADMIN);
        $this->actingAs($user);

        $user->company->quantity = 1;
        $user->company->save();

        $invitation = new UserInvitation();
        $invitation->email = 'test@4-check.com';
        $invitation->company()->associate($user->company->id);;

        Payment::setStripeDevToken();
        $user->company->newSubscription(Payment::SUBSCRIPTION_NAME, Payment::getPackageByKey(Payment::SUBSCRIPTION_KEY_PREMIUM_YEARLY_DEV))->create();

        $this->json('POST', '/users/invitations', [
            'email' => $invitation->email,
            'company' => $invitation->company->id,
        ]);

        $this->seeStatusCode(Response::HTTP_CREATED);

        $this->seeInDatabase('subscriptions', [
            'company_id' => $user->company->id,
            'quantity' => 2
        ]);
    }
}
