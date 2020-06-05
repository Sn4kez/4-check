<?php

use Illuminate\Http\Response;
use Laravel\Lumen\Testing\DatabaseMigrations;
use App\Payment;

class BugfixPaymentTest extends TestCase {
    use DatabaseMigrations;

    public function testErrors() {
        $this->test500Error();
    }

    private function test500Error() {
        $this->actingAs($this->user);

        $this->json('POST', '/payment/create', [
            'method' => 'invoice',
            'package' => 'PREMIUM_YEARLY',
            'qty' => 1,
        ]);

        $this->ddLastContent();
    }
}