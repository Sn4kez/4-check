<?php

use Laravel\Lumen\Testing\DatabaseMigrations;
use App\Payment;

class CreatePaymentTokenTest extends TestCase
{
    use DatabaseMigrations;

    /**
     * Tests the credit card token creation
     */
    public function testCreditCardTokenCreation() {
        Payment::setStripeDevToken();

        /** @var \Stripe\Token $token */
        $token = Payment::createValidStripeDevCreditCardToken();

        $this->assertNotNull($token->id);
    }

    /**
     * Tests the iban token creation
     */
    public function testIBANTokenCreation() {
        Payment::setStripeDevToken();

        /** @var \Stripe\Token $token */
        $token = Payment::createStripeIbanDevSuccessToken();

        $this->assertNotNull($token->id);
    }

}