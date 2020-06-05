<?php

namespace App\Http\Controllers;

use App\Company;
use App\Payment;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use GuzzleHttp\Client;
use Carbon\Carbon;

class LoginController extends Controller {

    /** @var User */
    private $user;

    private $loginSuccess = false;
    private $loginMessage = "";

    public function __construct() {

    }

    public function post(Request $request) {
        $http = new Client();

        $email = $request->get('email');
        $password = $request->get('password');

        $this->loginMessage = "";
        $this->loginSuccess = true;

        $this->syncUserAndCompany($email);

	$url = env('API_URL') . '/api/auth/token';
	$url = 'https://next.4-check.com/api/v2/auth/token';

        if ($this->loginSuccess === true) {
            $response = $http->post($url, [
                'form_params' => [
                    'grant_type' => 'password',
                    'client_id' => env('PASSWORD_CLIENT_ID'),
                    'client_secret' => env('PASSWORD_CLIENT_SECRET'),
                    'username' => $email,
                    'password' => $password,
                    'scope' => '',
                ],
                'allow_redirects' => false
            ]);
            return response($response->getBody(), Response::HTTP_OK);
        } else {
            return response(json_encode([
                'message' => $this->loginMessage,
                'success' => false,
                'code' => 401
            ]), 401);
        }
    }

    public function refresh(Request $request) {
        $http = new Client();
        $token = $request->get('refresh_token');

        $response = $http->post(env('APP_URL') . '/oauth/token', [
            'form_params' => [
                'grant_type' => 'refresh_token',
                'refresh_token' => $token,
                'client_id' => env('PASSWORD_CLIENT_ID'),
                'client_secret' => env('PASSWORD_CLIENT_SECRET'),
                'scope' => '',
            ],
        ]);

        return json_decode((string)$response->getBody(), true);
    }

    private function syncUserAndCompany($email) {
        $this->user = User::where('email', '=', $email)->first();

        if (!is_null($this->user)) {
            $this->syncCompanySubscription();
            $this->loginSuccess = $this->checkLogin();
        }
    }

    private function syncCompanySubscription() {
        if (!is_null($this->user)) {
            /** @var Company $company */
            $company = $this->user->company;
            $subscription = Payment::getSubscription($company);

            if (!is_null($subscription)) {
                $subscriptionData = $subscription->asStripeSubscription();

                $end = date('Y-m-d', $subscriptionData->current_period_end);
                $quantity = $subscriptionData->quantity;
                $trialEndsAt = $subscriptionData->trial_end;

                $subscription->ends_at = $end;
                $subscription->quantity = $quantity;
                $subscription->trial_ends_at = $trialEndsAt;
                $subscription->save();
            }
        }
    }

    private function checkLogin() {
        if (!is_null($this->user)) {
            $company = $this->user->company;
            $userActive = (bool)$this->user->isActive;
            $companyActive = (bool)$company->isActive;

            $this->user->lastLogin = Carbon::now();
            $this->user->save();

            if ($userActive && $companyActive) {
                /** @var \Laravel\Cashier\Subscription $subscription */
                $subscription = Payment::getSubscription($company);

                if (!is_null($subscription)) {
                    $subscriptionData = $subscription->asStripeSubscription();
                    $end = date('Y-m-d', $subscriptionData->current_period_end);
                    $currentDate = date('Y-m-d', time());

                    if ($currentDate <= $end) {
                        $this->loginMessage = "";
                        return true;
                    } else {
                        $this->loginMessage = "subscription is not valid anymore";
                    }
                } else {
                    $this->loginMessage = "subscription is null";
                }
            } else {
                $this->loginMessage = "user or company is not active";
            }
        } else {
            $this->loginMessage = "invalid user";
        }

        return false;
    }
}
