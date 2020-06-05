<?php

use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;
use App\Payment;

class ViewCompaniesSubscriptionTest extends TestCase {
    use DatabaseMigrations;

    public function provideValidAccessData() {
        return [
            [self::$USER],
            [self::$ADMIN],
            [self::$OTHER_ADMIN],
        ];
    }

    /**
     * @param $userKey
     * @dataProvider provideValidAccessData
     */
    public function testValidAccess($userKey) {
        $user = $this->getUser($userKey);
        $this->actingAs($user);
        $companyId = $user->company->id;

        $data = [
            'company' => 'Test Company',
            'street' => 'Teststrasse',
            'housenumber' => '123',
            'postcode' => '22222',
            'city' => 'Hamburg',
            'country' => 'DE',
            'token' => null,
            Payment::PARAM_NAME_METHOD => Payment::METHOD_NAME_INVOICE
        ];

        $qty = 2;
        $reference = 'test reference';
        $currentPaymentMethod = Payment::METHOD_NAME_INVOICE;
        $package = Payment::SUBSCRIPTION_KEY_PREMIUM_YEARLY_DEV;

        $this->json('POST', '/payment/create', array_merge([
            Payment::PARAM_NAME_PACKAGE => $package,
            Payment::PARAM_NAME_QUANTITY => $qty,
            Payment::PARAM_NAME_REFERENCE => $reference
        ], $data));
        $this->seeStatusCode(Response::HTTP_CREATED);

        $this->seeInDatabase('companies', [
            'id' => $user->company->id,
            'quantity' => $qty,
            'current_payment_method' => $currentPaymentMethod,
            'current_package' => $package,
            'reference' => $reference
        ]);

        $this->seeInDatabase('subscriptions', [
            'company_id' => $user->company->id,
            'quantity' => $qty
        ]);

        $checkForValidity = [
            'data' => [
                'package',
                'start',
                'end'
            ]
        ];

        $this->json('GET', sprintf('/companies/%s/subscription', $companyId));
        $this->seeJsonStructure($checkForValidity);
        $this->seeJsonNotNull($checkForValidity);
    }
}
