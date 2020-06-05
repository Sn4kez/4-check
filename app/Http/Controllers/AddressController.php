<?php

namespace App\Http\Controllers;

use App\Address;
use App\AddressType;
use App\Company;
use App\Country;
use App\Http\Resources\AddressResource;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AddressController extends Controller
{
    /**
     * @var Address
     */
    protected $address;

    /**
     * @var Company
     */
    protected $company;

    /**
     * @var Country
     */
    protected $country;

    /**
     * @var AddressType
     */
    protected $addressType;

    /**
     * Create a new controller instance.
     *
     * @param Address $address
     * @param Company $company
     * @param Country $country
     * @param AddressType $addressType
     */
    public function __construct(Address $address, Company $company,
                                Country $country, AddressType $addressType)
    {
        $this->address = $address;
        $this->company = $company;
        $this->country = $country;
        $this->addressType = $addressType;
    }

    /**
     * Create a new address.
     *
     * @param Request $request
     * @param string $companyId
     * @return \Illuminate\Http\JsonResponse
     */
    public function create(Request $request, string $companyId)
    {
        $company = $this->company->findOrFail($companyId);
        $this->authorize('update', $company);
        $data = $this->validate($request, Address::rules('create'));
        $country = $this->country->findOrFail($data['country']);
        $type = $this->addressType->findOrFail($data['type']);
        $address = new Address($data);
        $address->company()->associate($company);
        $address->country()->associate($country);
        $address->type()->associate($type);
        $address->save();
        return AddressResource::make($address)
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
    }

    /**
     * List a company's addresses.
     *
     * @param string $companyId
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(string $companyId)
    {
        /* @var Company $company */
        $company = $this->company->findOrFail($companyId);
        $this->authorize('update', $company);
        return AddressResource::collection($company->addresses)
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    /**
     * View an address.
     *
     * @param string $addressId
     * @return \Illuminate\Http\JsonResponse
     */
    public function view(string $addressId)
    {
        /* @var Address $address */
        $address = $this->address->findOrFail($addressId);
        $this->authorize('update', $address->company);
        return AddressResource::make($address)
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    /**
     * Update an address.
     *
     * @param Request $request
     * @param string $addressId
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, string $addressId)
    {
        /* @var Address $address */
        $address = $this->address->findOrFail($addressId);
        $this->authorize($address->company);
        $data = $this->validate($request, Address::rules('update'));
        if (array_key_exists('type', $data)) {
            $type = $this->addressType->findOrFail($data['type']);
            $address->type()->associate($type);
        }
        if (array_key_exists('country', $data)) {
            $country = $this->country->findOrFail($data['country']);
            $address->country()->associate($country);
        }
        $address->update($data);
        return response('', Response::HTTP_NO_CONTENT);
    }

    /**
     * Delete an address.
     *
     * @param string $addressId
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(string $addressId)
    {
        /* @var Address $address */
        $address = $this->address->findOrFail($addressId);
        $this->authorize($address->company);
        $address->delete();
        return response('', Response::HTTP_NO_CONTENT);
    }
}
