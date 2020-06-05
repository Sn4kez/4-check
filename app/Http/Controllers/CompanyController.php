<?php

namespace App\Http\Controllers;

use App\Company;
use App\Http\Resources\CompanyResource;
use App\Http\Resources\UserResource;
use App\Payment;
use App\Sector;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Stripe\Subscription;

class CompanyController extends Controller {
    /**
     * @var Company
     */
    protected $company;

    /**
     * @var Sector
     */
    protected $sector;

    /**
     * Create a new controller instance.
     *
     * @param Company $company
     * @param Sector $sector
     */
    public function __construct(Company $company, Sector $sector) {
        $this->company = $company;
        $this->sector = $sector;
    }

    /**
     * List all companies.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index(Request $request) {
        /* @var \App\User $loggedInUser */
        $loggedInUser = $request->user();
        if ($loggedInUser->isSuperAdmin()) {
            $companies = $this->company->all();
        } else {
            $company = $loggedInUser->company;
            $this->authorize('index', $company);
            $companies = collect([$company]);
        }
        return CompanyResource::collection($companies)->response()->setStatusCode(Response::HTTP_OK);
    }

    /**
     * View a company.
     *
     * @param string $companyId
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function view(string $companyId) {
        $company = $this->company->findOrFail($companyId);
        $this->authorize($company);
        return CompanyResource::make($company)->response()->setStatusCode(Response::HTTP_OK);
    }

    /**
     * Update a company.
     *
     * @param Request $request
     * @param string $companyId
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function update(Request $request, string $companyId) {
        /* @var Company $company */
        $company = $this->company->findOrFail($companyId);
        $this->authorize($company);
        $data = $this->validate($request, Company::rules('update'));
        if (array_key_exists('sector', $data)) {
            $sector = $this->sector->findOrFail($data['sector']);
            $company->sector()->associate($sector);
        }
        $company->update($data);
        return response('', Response::HTTP_NO_CONTENT);
    }

    /**
     * Delete a company.
     *
     * @param string $companyId
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function delete(string $companyId) {
        $company = $this->company->findOrFail($companyId);
        $this->authorize($company);
        $company->delete();
        return response('', Response::HTTP_NO_CONTENT);
    }


    /**
     * lists user of company.
     *
     * @param string $companyId
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */

    public function indexUser(string $companyId) {
        $company = $this->company->findOrFail($companyId);

        $this->authorize('update', $company);

        return UserResource::collection($company->users)->response()->setStatusCode(Response::HTTP_OK);
    }

    /**
     * Lists the subscription of the comapny
     *
     * @param Request $request
     * @param $companyId
     * @return Response|\Laravel\Lumen\Http\ResponseFactory
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function subscription(Request $request, $companyId) {
        /** @var Company $company */
        $company = $this->company->findOrFail($companyId);
        $this->authorize('view', $company);

        $user = $request->user();
        $packageName = Payment::getCompanyPackageNameByUser($user);

        /** @var \Stripe\Subscription $stripeSubscription */
        $stripeSubscription = $company->subscription(Payment::SUBSCRIPTION_NAME)->asStripeSubscription();

        $data = [
            'package' => $packageName,
            'start' => date('Y-m-d', $stripeSubscription->current_period_start),
            'end' => date('Y-m-d', $stripeSubscription->current_period_end)
        ];

        return response(['data' => $data], Response::HTTP_OK);
    }
}
