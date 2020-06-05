<?php

namespace App\Http\Controllers;

use App\Http\Resources\CompanySubscriptionResource;
use App\CompanySubscription;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CompanySubscriptionController extends Controller
{
    /**
     * @var CompanySubscription
     */
    protected $companySubscription;

    /**
     * Create a new controller instance.
     *
     * @param CompanySubscription $subscription
     */
    public function __construct(CompanySubscription $subscription)
    {
        $this->companySubscription = $subscription;
    }

    /**
     * View a subscription.
     *
     * @param string $subscriptionId
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function view(string $subscriptionId)
    {
        /* @var CompanySubscription $subscription */
        $subscription = $this->companySubscription->findOrFail($subscriptionId);
        $this->authorize('view', $subscription);
        return CompanySubscriptionResource::make($subscription)
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    /**
     * Update a subscription.
     *
     * @param Request $request
     * @param string $subscriptionId
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function update(Request $request, string $subscriptionId)
    {
        /* @var CompanySubscription $subscription */
        $subscription = $this->companySubscription->findOrFail($subscriptionId);
        $this->authorize('update', $subscription);
        $data = $this->validate($request, CompanySubscription::rules('update'));
        $subscription->update($data);
        return response('', Response::HTTP_NO_CONTENT);
    }
}
