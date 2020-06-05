<?php

use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;
use App\Payment;

class CreatePaymentTest extends TestCase {
    use DatabaseMigrations;

    public function provideValidEntities() {
        return [
            [self::$USER, "INVOICE", Payment::SUBSCRIPTION_KEY_PREMIUM_YEARLY_DEV, 1, 'ref123'],
            [self::$USER, "INVOICE", Payment::SUBSCRIPTION_KEY_BASIC_MONTHLY_DEV, 10, null],
            [self::$USER, "INVOICE", Payment::SUBSCRIPTION_KEY_PREMIUM_MONTHLY_DEV, 8, 'otherref'],
            [self::$USER, "INVOICE", Payment::SUBSCRIPTION_KEY_BASIC_YEARLY_DEV, 12, null],
            [self::$USER, "CC", Payment::SUBSCRIPTION_KEY_BASIC_MONTHLY_DEV, 5, 'ref'],
            //[self::$USER, "IBAN", Payment::SUBSCRIPTION_KEY_BASIC_MONTHLY_DEV, 3, 'refrefref']
        ];
    }

    /**
     * @param $userKey
     * @param $paymentMethod
     * @param $package
     * @param $qty
     * @param $reference
     * @dataProvider provideValidEntities
     */
    public function testValidEntities($userKey, $paymentMethod, $package, $qty, $reference) {
        Payment::setStripeDevToken();

        $user = $this->getUser($userKey);
        $this->actingAs($user);

        $user->company()->associate($this->companyWithoutSubscription);
        $user->save();

        $currentPaymentMethod = Payment::METHOD_NAME_CC;
        $data = [];

        switch ($paymentMethod) {
            case "CC":
                $token = Payment::createValidStripeDevCreditCardToken();
                $data = [
                    Payment::PARAM_NAME_TOKEN => $token->id,
                ];

                break;
            case "IBAN":
                $currentPaymentMethod = Payment::METHOD_NAME_SEPA;
                $data = [
                    'iban' => 'DE89370400440532013000',
                    'token' => null,
                    Payment::PARAM_NAME_METHOD => Payment::METHOD_NAME_SEPA
                ];
                break;
            case "INVOICE":
                $currentPaymentMethod = Payment::METHOD_NAME_INVOICE;
                $data = [
                    'invoice_company' => 'UT company',
                    'invoice_street' => 'UT street',
                    'invoice_houseno' => 'UT housenumber',
                    'invoice_city' => 'UT city',
                    'invoice_postcode' => 'UT postcode',
                    'invoice_country' => 'UT country',
                    'reference' => 'UT reference',
                    'token' => null,
                    Payment::PARAM_NAME_METHOD => Payment::METHOD_NAME_INVOICE
                ];
                break;
            default:
                $tokenId = null;
        }

        $this->json('POST', '/payment/create', array_merge([
            Payment::PARAM_NAME_PACKAGE => $package,
            Payment::PARAM_NAME_QUANTITY => $qty,
            Payment::PARAM_NAME_REFERENCE => $reference
        ], $data));

        $this->seeStatusCode(Response::HTTP_CREATED);

        $this->seeInDatabase('subscriptions', [
            'company_id' => $user->company->id,
            'quantity' => $qty
        ]);

        if ($paymentMethod !== "CC") {
            $this->seeInDatabase('company_subscriptions', [
                'companyId' => $user->company->id
            ]);
        }
    }

}