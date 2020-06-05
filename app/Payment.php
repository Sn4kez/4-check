<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Stripe\Charge;
use Stripe\Source;
use Stripe\Stripe;
use Stripe\Token;

/**
 * See https://stripe.com/docs/testing for dev data!
 * - 4242424242424242 > VISA
 * - 4000056655665556 > VISA Debit
 * - 5555555555554444 > MASTERCARD
 *
 * Class Payment
 * @package App
 */
class Payment extends Model {
    /**
     * Is responsible for ALL subscriptions.
     * Because we only have ONE subscription, this MUST be the one and only name of the subscription.
     * It COULD be different, but that would not make sense - right now.
     */
    const SUBSCRIPTION_NAME = 'main';

    const SUBSCRIPTION_KEY_BASIC_MONTHLY_DEV = 'DEV_BASIC_MONTHLY';
    const SUBSCRIPTION_KEY_BASIC_YEARLY_DEV = 'DEV_BASIC_YEARLY';
    const SUBSCRIPTION_KEY_PREMIUM_MONTHLY_DEV = 'DEV_PREMIUM_YEARLY';
    const SUBSCRIPTION_KEY_PREMIUM_YEARLY_DEV = 'DEV_PREMIUM_YEARLY';
    const SUBSCRIPTION_KEY_DELUXE_MONTHLY_DEV = 'DEV_DELUXE_YEARLY';
    const SUBSCRIPTION_KEY_DELUXE_YEARLY_DEV = 'DEV_DELUXE_YEARLY';

    const PARAM_NAME_TOKEN = 'token';
    const PARAM_NAME_PACKAGE = 'package';
    const PARAM_NAME_METHOD = 'method';
    const PARAM_NAME_QUANTITY = 'qty';
    const PARAM_NAME_REFERENCE = 'reference';

    const METHOD_NAME_CC = 'creditcard';

    const METHOD_NAME_SEPA = 'sepa';

    const COMMON_COMPANY_PAYMENT_COLUMNS = [
        'reference' => 'reference'
    ];

    /**
     * Key = database attribute column
     * Value = POST key
     */
    const PARAMETERS_FOR_SEPA = [
        'sepa_iban' => 'iban'
    ];

    const METHOD_NAME_INVOICE = 'invoice';

    /**
     * Value = POST key
     */
    const PARAMETERS_FOR_INVOICE = [
        'invoice_company' => 'company',
        'invoice_street' => 'street',
        'invoice_houseno' => 'housenumber',
        'invoice_city' => 'city',
        'invoice_postcode' => 'postcode',
        'invoice_country' => 'country',
        'reference' => 'reference'
    ];

    /**
     * Key = POST key
     * Value = stripe key
     *
     * see: https://stripe.com/docs/api/customers/update
     */
    const PARAMETERS_FOR_INVOICE_ADDRESS = [
        'invoice_street' => 'line1',
        'invoice_houseno' => 'line2',
        'invoice_city' => 'city',
        'invoice_postcode' => 'postal_code',
        'invoice_country' => 'country',
    ];

    const PACKAGES = [
        'DEV_BASIC_MONTHLY' => [
            'plan_id' => 'plan_DEwXudAZ2s7xP9',
            'name' => 'BASIC monatlich'
        ],
        'DEV_BASIC_YEARLY' => [
            'plan_id' => 'plan_DEwXcKLA588uT7',
            'name' => 'BASIC jährlich'
        ],
        'DEV_DELUXE_MONTHLY' => [
            'plan_id' => 'plan_DEwXudAZ2s7xP9',
            'name' => 'BASIC monatlich'
        ],
        'DEV_DELUXE_YEARLY' => [
            'plan_id' => 'plan_DEwXcKLA588uT7',
            'name' => 'BASIC jährlich'
        ],
        'DEV_PREMIUM_MONTHLY' => [
            'plan_id' => 'plan_DEwXudAZ2s7xP9',
            'name' => 'BASIC monatlich'
        ],
        'DEV_PREMIUM_YEARLY' => [
            'plan_id' => 'plan_DEwXcKLA588uT7',
            'name' => 'BASIC jährlich'
        ],
        'BASIC_MONTHLY' => [
            'plan_id' => 'plan_DgLFuZ9iStBXUd',
            'name' => 'BASIC monatlich'
        ],
        'BASIC_YEARLY' => [
            'plan_id' => 'plan_DgLGWQ9ytkDq5n',
            'name' => 'BASIC jährlich'
        ],
        'DELUXE_MONTHLY' => [
            'plan_id' => 'plan_DgLJuolhKqACfk',
            'name' => 'DELUXE monatlich'
        ],
        'DELUXE_YEARLY' => [
            'plan_id' => 'plan_DgLJHjdt4RiqVe',
            'name' => 'DELUXE jährlich'
        ],
        'PREMIUM_MONTHLY' => [
            'plan_id' => 'plan_DgLHjVekM4YknS',
            'name' => 'PREMIUM monatlich'
        ],
        'PREMIUM_YEARLY' => [
            'plan_id' => 'plan_DgLIoLlHRTKpEK',
            'name' => 'PREMIUM jährlich'
        ],
    ];

    const CURRENT_FOR_SEPA = 'CHF';


    private static $apiKeySet = false;

    public static function setStripeToken() {
        if (self::isEnvironmentLive()) {
            self::setStripeLiveToken();
        } else {
            self::setStripeDevToken();
        }
    }

    /**
     * sets the correct token for stripe
     */

    private static function setStripeLiveToken() {
        self::setStripeAPIKeyIfIsNotCurrentlySet(env('STRIPE_PRODUCTION_KEY'));
    }

    /**
     * sets the correct dev token for stripe
     */

    public static function setStripeDevToken() {
        self::setStripeAPIKeyIfIsNotCurrentlySet(env('STRIPE_DEV_KEY'), true);
    }

    private static function setStripeAPIKeyIfIsNotCurrentlySet($key, $force = false) {
        if (self::$apiKeySet === false || $force === true) {
            Stripe::setApiKey($key);

            self::$apiKeySet = true;
        }
    }

    /**
     * @return Token
     */
    public static function createValidStripeDevCreditCardToken() {
        return self::createStripeCreditCardToken('4242424242424242', 12, date("Y", time()) + 1, '123');
    }

    /**
     * @param $number
     * @param $expiryMonth
     * @param $expiryYear
     * @param $cvc
     * @return Token
     */
    public static function createStripeCreditCardToken($number, $expiryMonth, $expiryYear, $cvc) {
        return Token::create([
            'card' => [
                'number' => $number,
                'exp_month' => $expiryMonth,
                'exp_year' => $expiryYear,
                'cvc' => $cvc
            ]
        ]);
    }

    /**
     * @return Token
     */
    public static function createStripeIbanDevSuccessToken() {
        return self::createStripeIbanToken('DE', 'EUR', 'DE89370400440532013000');
    }

    /**
     * @return Token
     */
    public static function createStripeIbanDevFailToken() {
        return self::createStripeIbanToken('DE', 'EUR', 'DE62370400440532013001');
    }

    /**
     * @param $country
     * @param $currency
     * @param $iban
     * @return Token
     */
    public static function createStripeIbanToken($country, $currency, $iban) {
        return Token::create([
            'bank_account' => [
                'country' => $country,
                'currency' => $currency,
                'account_number' => $iban
            ]
        ]);
    }

    public static function chargeSEPA($iban) {
        self::setStripeToken();

        /** @var Source $source */
        $source = Source::create([
            'type' => 'sepa_debit',
            'sepa_debit' => [
                'iban' => $iban
            ],
            'currency' => self::CURRENT_FOR_SEPA
        ]);

        $sourceId = $source->id;

        $customer = \Stripe\Customer::create([
            "email" => "paying.user@example.com",
            "source" => "src_18eYalAHEMiOZZp1l9ZTjSU0",
        ]);

        $charge = Charge::create([
            'amount' => 1099,
            'currency' => self::CURRENT_FOR_SEPA,
            'customer' => ''
        ]);
    }

    public static function isCurrentSubscriptionPaidWithCreditcard(Company $company) {
        self::setStripeToken();

        if ($company->subscribed(Payment::SUBSCRIPTION_NAME)) {

        }

        return false;
    }

    public static function getPackageByKey($packageKey) {
        return self::getPackageProperty($packageKey, 'plan_id');
    }

    public static function getPackageNameByKey($packageKey) {
        return self::getPackageProperty($packageKey, 'name');
    }

    private static function getPackageProperty($packageKey, $property) {
        self::setStripeToken();

        if (!self::isEnvironmentLive()) {
            $first4Signs = substr($packageKey, 0, 4);

            if ($first4Signs !== "DEV_") {
                $packageKey = sprintf("DEV_%s", $packageKey);
            }
        }

        if (array_key_exists($packageKey, self::PACKAGES)) {
            return self::PACKAGES[$packageKey][$property];
        }

        return $packageKey;
    }

    public static function getNewTableColumns() {
        return array_merge(self::COMMON_COMPANY_PAYMENT_COLUMNS, self::PARAMETERS_FOR_SEPA, self::PARAMETERS_FOR_INVOICE);
    }

    public static function getCompanyPackageNameByUser(User $user) {
        self::setStripeToken();

        return self::getCompanyPackageName($user->company);
    }

    public static function getCompanyPackageName(Company $company) {
        self::setStripeToken();

        $package = $company->current_package;
        return self::getPackageNameByKey($package);
    }

    public static function getCompaniesMaxUsers(Company $company) {
        return $company->quantity;
    }

    private static function getCompaniesUserCount(Company $company) {
        return count($company->users);
    }

    private static function getCompaniesInvitationCount(Company $company) {
        return UserInvitation::where('companyId', '=', $company->id)->count();
    }

    public static function incrementCompanySubscriptionIfNeeded(Company $company) {
        self::setStripeToken();

        $maxUsers = self::getCompaniesMaxUsers($company);
        $currentUsers = self::getCompaniesUserCount($company);
        $currentInvitations = self::getCompaniesInvitationCount($company);
        $totalUsers = $currentInvitations + $currentUsers;

        if ($totalUsers >= $maxUsers) {
            $company->subscription(Payment::SUBSCRIPTION_NAME)->incrementQuantity();
        }
    }

    public static function canCompanyUpdateToXUsers(Company $company, int $newMaxUsers) {
        $companyUsersCount = self::getCompaniesUserCount($company);

        return $newMaxUsers > 0 && $newMaxUsers > $companyUsersCount;
    }

    public static function canCompanyInviteAUser(Company $company) {
        $allowedUsers = self::getCompaniesMaxUsers($company);
        $companyUsersCount = self::getCompaniesUserCount($company);
        $companyInvitationCount = self::getCompaniesInvitationCount($company);
        $companyTheoreticalUserCount = $companyUsersCount + $companyInvitationCount;

        return (int)$companyTheoreticalUserCount < (int)$allowedUsers;
    }

    public static function getSubscription(Company $company) {
        self::setStripeToken();

        return $company->subscription(Payment::SUBSCRIPTION_NAME);
    }

    public static function isSubscriptionCancelled(Company $company) {
        self::setStripeToken();

        return self::getSubscription($company)->cancelled();
    }

    public static function isEnvironmentLive() {
        return env('STRIPE_ENV') !== 'dev';
    }
}
