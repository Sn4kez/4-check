<?php

use App\Company;
use App\Gender;
use App\Payment;
use App\Phone;
use App\Sector;
use App\User;
use Laravel\Lumen\Testing\DatabaseMigrations;

/**
 * @property int inactiveUserId
 * @property User inactiveUser
 */
class LoginPostTest extends TestCase {

    use DatabaseMigrations;

    const PASSWORD = "TestPassword123";
    const EMAIL_INACTIVE = "inactive@4-check.com";
    const EMAIL_INACTIVE_SUBSCRIPTION = "inactivesub@4-check.com";
    const EMAIL_NON_EXISTENT = "nonexistent@4-check.com";
    const EMAIL_VALID = "valid@4-check.com";

    public function setUp() {
        parent::setUp(); // TODO: Change the autogenerated stub
    }

    public function testValidAccess() {
        Payment::setStripeDevToken();

        $user = $this->user;
        $this->actingAs($user);

        $user->company()->associate($this->company);
        $user->save();
        $this->createTestPayment();
        $this->seeStatusCode(201);

        $subscription = Payment::getSubscription($this->company);

        if ($subscription !== null) {
            $this->json('POST', '/login', [
                'email' => self::PASSWORD,
                'password' => self::PASSWORD
            ]);

            $this->seeStatusCode(200);
        }
    }

    public function testInvalidAccess() {
        $user = factory(User::class)->make();
        $user->gender()->associate(Gender::all()->random());
        $company = factory(Company::class)->make();
        $company->sector()->associate(Sector::find('cleaning'));
        $phone = factory(Phone::class)->make();

        $this->json('POST', '/login', [
            'email' => self::EMAIL_NON_EXISTENT,
            'password' => self::PASSWORD . "1"
        ]);

        $this->seeStatusCode(401);

        $this->json('POST', '/users', [
            'email' => self::EMAIL_INACTIVE,
            'password' => self::PASSWORD,
            'gender' => $user->gender->id,
            'isActive' => 0,
            'company' => [
                'name' => $company->name,
                'sector' => $company->sector->id,
            ],
            'phone' => [
                'countryCode' => $phone->countryCode,
                'nationalNumber' => $phone->countryCode,
            ],
        ]);

        $this->json('POST', '/login', [
            'email' => self::EMAIL_INACTIVE,
            'password' => self::PASSWORD
        ]);

        $this->seeStatusCode(401);

        $this->json('POST', '/users', [
            'email' => self::EMAIL_INACTIVE_SUBSCRIPTION,
            'password' => self::PASSWORD,
            'gender' => $user->gender->id,
            'isActive' => 1,
            'company' => [
                'name' => $company->name,
                'sector' => $company->sector->id,
            ],
            'phone' => [
                'countryCode' => $phone->countryCode,
                'nationalNumber' => $phone->countryCode,
            ],
        ]);

        $this->json('POST', '/login', [
            'email' => self::EMAIL_INACTIVE_SUBSCRIPTION,
            'password' => self::PASSWORD
        ]);

        $this->seeStatusCode(401);
    }
}