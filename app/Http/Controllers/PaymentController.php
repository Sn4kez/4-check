<?php

namespace App\Http\Controllers;

use App\Company;
use App\Payment;
use App\Policies\PaymentPolicy;
use App\CompanySubscription;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Carbon\Carbon;

class PaymentController extends Controller {
    /**
     * @var Request
     */
    private $request;

    /**
     * @var Company
     */
    private $company;

    /**
     * @var User
     */
    private $user;

    public function createSubscription(Request $request) {
        $this->setVarsAndSetStripeToken($request);
        $this->setStripeToken();

        if ($this->isRequestAValidPaymentRequest()) {
            if ($this->isRequestACreditCardRequest()) {
                return $this->processCreditCardRequest();
            } elseif ($this->isRequestAValidSEPARequest()) {
                return $this->processSEPARequest();
            } elseif ($this->isRequestAValidInvoiceRequest()) {
                return $this->processInvoiceRequest();
            }
        }

        return $this->getInvalidResponse();
    }

    private function getInvalidResponse() {
        return response('', Response::HTTP_BAD_REQUEST);
    }

    public function updateSubscription(Request $request) {
        $this->setVarsAndSetStripeToken($request);
        $this->setStripeToken();

        $isCurrentSubscriptionPaidWithCreditCard = Payment::isCurrentSubscriptionPaidWithCreditcard($this->company);

        if ($this->isRequestAValidPaymentRequest()) {
            if ($this->isRequestACreditCardRequest()) {
                if ($isCurrentSubscriptionPaidWithCreditCard) {
                    $this->changeCreditCard();
                } else {
                    $this->swapPaymentFromSEPAToCC();
                }
            } elseif ($this->isRequestAValidSEPARequest()) {
                if (!$isCurrentSubscriptionPaidWithCreditCard) {
                    $this->changeSEPA();
                } else {
                    $this->swapPaymentFromCCToSEPA();
                }
            } elseif ($this->isRequestAValidInvoiceRequest()) {

            }
        }

        return $this->getInvalidResponse();
    }

    /**
     * x cancel CC subscription
     * x save sepa data
     * - TODO: do further sepa things, which i shall be done in "save sepa data"
     */
    private function swapPaymentFromCCToSEPA() {
        $this->cancelCreditCardSubscription();
        $this->processSEPARequest();
    }

    /**
     * x process CC subscription
     * x truncate SEPA values from the companies table
     */
    private function swapPaymentFromSEPAToCC() {
        $this->processCreditCardRequest();
        $this->truncateSEPAValuesFromCompany();
    }

    /**
     * x update card call from cashier
     */
    private function changeCreditCard() {
        $this->company->updateCard($this->getToken());
    }

    /**
     * x save sepa data
     * - TODO: do further sepa things, which i shall be done in "save sepa data"
     */
    private function changeSEPA() {
        $this->processSEPARequest();
    }

    private function truncateSEPAValuesFromCompany() {
        foreach (Payment::PARAMETERS_FOR_SEPA as $dbAttribute => $postKey) {
            $this->company->{$dbAttribute} = '';
        }

        $this->company->save();
    }

    private function setVarsAndSetStripeToken(Request $request) {
        $this->request = $request;
        $this->user = $request->user();
        $this->company = $this->user->company;
    }

    private function cancelCreditCardSubscription() {
        $this->company->subscription(Payment::SUBSCRIPTION_NAME)->cancel();
    }

    private function setStripeToken() {
        Payment::setStripeToken();
    }

    private function processSEPARequest() {
        //$company->newSubscription(Payment::SUBSCRIPTION_NAME, $package)->create();
        $this->applyPostDataInCompany(array_merge(Payment::PARAMETERS_FOR_SEPA, Payment::COMMON_COMPANY_PAYMENT_COLUMNS));
        $this->updateCompanyPaymentDetails($this->getQuantity(), Payment::METHOD_NAME_SEPA, $this->getPackageRaw());
        $this->createEmptySubscriptionIfNotExists(Payment::METHOD_NAME_SEPA, $this->getQuantity());

        return response('', Response::HTTP_CREATED);
    }

    private function processInvoiceRequest() {
        $additionalSubscriptionMetaData = $this->getAdditionalSubscriptionMetaData(Payment::PARAMETERS_FOR_INVOICE);
        $stripeData = $this->getStripeSubscriptionDetails('INVOICE Subscription', $additionalSubscriptionMetaData);
        $this->company->newSubscription(Payment::SUBSCRIPTION_NAME, $this->getPackage())->create(null, $stripeData);

        if ($this->company->subscribed(Payment::SUBSCRIPTION_NAME)) {
            $qty = $this->getQuantity();

            if ($qty > 1) {
                $this->company->subscription(Payment::SUBSCRIPTION_NAME)->updateQuantity($qty);
            }

            $customer = $this->company->asStripeCustomer();
            $customer->address = $this->buildStripeData(Payment::PARAMETERS_FOR_INVOICE_ADDRESS);
            $customer->shipping = [
                "name" => $this->getName(),
                "address" => $customer->address
            ];
            $customer->save();

            $this->applyPostDataInCompany(array_merge(Payment::PARAMETERS_FOR_INVOICE, Payment::COMMON_COMPANY_PAYMENT_COLUMNS));
            $this->updateCompanyPaymentDetails($this->getQuantity(), Payment::METHOD_NAME_INVOICE, $this->getPackageRaw());
            $this->createEmptySubscriptionIfNotExists(Payment::METHOD_NAME_INVOICE, $this->getQuantity());

            return response('', Response::HTTP_CREATED);
        }

        return $this->getInvalidResponse();
    }

    private function getAdditionalSubscriptionMetaData($attributes) {
        $additionalMetaData = [];

        foreach ($attributes as $postKey => $tableAttribute) {
            if ($this->request->has($postKey)) {
                $additionalMetaData[$postKey] = $this->request->input($postKey);
            }
        }

        return $additionalMetaData;
    }

    private function buildStripeData($attributes) {
        $stripeData = [];

        if (count($attributes) > 0) {
            foreach ($attributes as $postKey => $stripeKey) {
                if ($this->request->has($postKey)) {
                    $stripeData[$stripeKey] = $this->request->input($postKey);
                }
            }
        }

        return $stripeData;
    }

    private function applyPostDataInCompany($attributes) {
        $companyChanged = false;

        foreach ($attributes as $tableAttribute => $postKey) {
            if ($this->request->has($postKey)) {
                $this->company->{$tableAttribute} = $this->request->input($postKey);
                $companyChanged = true;
            }
        }

        if ($companyChanged) {
            $this->company->save();
        }
    }

    private function processCreditCardRequest() {
        $token = $this->getToken();
        $this->company->newSubscription(Payment::SUBSCRIPTION_NAME, $this->getPackage())->create($token, $this->getStripeSubscriptionDetails('CC Subscription', []));

        if ($this->company->subscribed(Payment::SUBSCRIPTION_NAME)) {
            $qty = $this->getQuantity();

            if ($qty > 1) {
                $this->company->subscription(Payment::SUBSCRIPTION_NAME)->updateQuantity($qty);
            }

            $this->updateCompanyPaymentDetails($qty, Payment::METHOD_NAME_CC, $this->getPackageRaw());
            $this->createEmptySubscriptionIfNotExists(Payment::METHOD_NAME_CC, $this->getQuantity());
            $this->applyPostDataInCompany(Payment::COMMON_COMPANY_PAYMENT_COLUMNS);

            return response('', Response::HTTP_CREATED);
        }

        return $this->getInvalidResponse();
    }

    /**
     * @param $description
     * @param array $additionalMeta
     * @param $stripeAttributes
     * @return array
     */
    private function getStripeSubscriptionDetails($description, $additionalMeta) {
        $name = $this->getName();

        return [
            'name' => $name,
            'email' => $this->user->email,
            'description' => $description,
            'metadata' => array_merge(['name' => $name], $additionalMeta)
        ];
    }

    /**
     * Create empty subscriptions, e.g. for SEPA or INVOICE, where we dont save subscriptions from stripe.
     * stripe subscriptions are always saved in the subscriptions table via cashier, the other methods
     * are not saved automatically, so we need to do this.
     *
     * @param $paymentMethod
     * @param $qty
     */
    private function createEmptySubscriptionIfNotExists($paymentMethod, $qty) {
        $countSubscriptions = CompanySubscription::where('companyId', '=', $this->company->id)->count();

        if ($countSubscriptions === 0) {
            $subscription = new CompanySubscription();
            $subscription->companyId = $this->company->id;
            $subscription->save();
        }
    }

    private function getPackage() {
        return Payment::getPackageByKey($this->getPackageRaw());
    }

    private function getPackageRaw() {
        return $this->request->input(Payment::PARAM_NAME_PACKAGE);
    }

    private function getQuantity() {
        return $this->request->input(Payment::PARAM_NAME_QUANTITY);
    }

    private function getToken() {
        return $this->request->input(Payment::PARAM_NAME_TOKEN);
    }

    private function isRequestAValidPaymentRequest() {
        return ($this->request->has(Payment::PARAM_NAME_PACKAGE) && $this->request->has(Payment::PARAM_NAME_QUANTITY)) && ($this->isRequestAValidInvoiceRequest() || $this->isRequestAValidSEPARequest() || $this->isRequestACreditCardRequest());
    }

    private function isRequestACreditCardRequest() {
        if ($this->request->has(Payment::PARAM_NAME_TOKEN) && $this->request->has(Payment::PARAM_NAME_PACKAGE)) {
            if (strlen($this->request->input(Payment::PARAM_NAME_PACKAGE)) > 0) {
                if (strlen($this->request->input(Payment::PARAM_NAME_TOKEN)) > 0) {
                    return true;
                }
            }
        }

        return false;
    }

    private function isRequestAValidSEPARequest() {
        return self::isPaymentMethodRequested(Payment::METHOD_NAME_SEPA);
    }

    private function isRequestAValidInvoiceRequest() {
        return self::isPaymentMethodRequested(Payment::METHOD_NAME_INVOICE);
    }

    private function isPaymentMethodRequested($awaitedPaymentMethodName) {
        if ($this->request->has(Payment::PARAM_NAME_METHOD)) {
            $method = $this->request->input(Payment::PARAM_NAME_METHOD);

            if ($method === $awaitedPaymentMethodName) {
                return $this->request->input(Payment::PARAM_NAME_METHOD) === $awaitedPaymentMethodName;
            }
        }

        return false;
    }

    private function updateCompanyPaymentDetails($qty, $paymentMethod, $package) {
        $this->company->quantity = $qty;
        $this->company->current_payment_method = $paymentMethod;
        $this->company->current_package = $package;
        $this->company->save();
    }

    private function getName() {
        return sprintf("%s %s", $this->user->firstName, $this->user->lastName);
    }
}