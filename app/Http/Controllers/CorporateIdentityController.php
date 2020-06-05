<?php

namespace App\Http\Controllers;

use App\CorporateIdentity;
use App\Company;
use App\Http\Resources\CorporateIdentityResource;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Ramsey\Uuid\Uuid;
use App\Media;


class CorporateIdentityController extends Controller
{
	private $corporateIdentity;

	public function __construct (CorporateIdentity $corporateIdentity)
	{
		$this->corporateIdentity = $corporateIdentity;
	}
	/**
     * Create a new corporate identity.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */

	public function create(Request $request)
	{
		$data = $this->validate($request, CorporateIdentity::rules('create'));
        $data = Media::processRequest($data);

		$company = Company::findOrFail($data['company']);
		$corporateIdentity = new CorporateIdentity($data);
		$corporateIdentity->id = Uuid::uuid4()->toString();
		$corporateIdentity->company()->associate($company);

		$corporateIdentity->save();

		return CorporateIdentityResource::make($corporateIdentity)
            ->response()
            ->setStatusCode(Response::HTTP_CREATED);
	}

	/**
     * Update a corporate identity.
     *
     * @param Request $request
     * @param string $companyId
     * @return \Illuminate\Http\JsonResponse
     */

	public function update(Request $request, string $companyId)
	{
		$data = $this->validate($request, CorporateIdentity::rules('update'));
        $data = Media::processRequest($data);

		$corporateIdentity = CorporateIdentity::where('companyId', '=', $companyId)->firstOrFail();

		$this->authorize($corporateIdentity);

		$corporateIdentity->update($data);

		return response('', Response::HTTP_NO_CONTENT);
	}

	/**
     * Deletes a location.
     *
     * @param Request $request
     * @param string $companyId
     * @return \Illuminate\Http\JsonResponse
     */

	public function delete(Request $request, string $companyId)
	{
		$corporateIdentity = CorporateIdentity::where('companyId', '=', $companyId)->firstOrFail();

		$this->authorize($corporateIdentity);

		$corporateIdentity->delete();

		return response('', Response::HTTP_NO_CONTENT);
	}

	/**
     * View a corporate identity preferences.
     *
     * @param string $companyId
     * @return \Illuminate\Http\JsonResponse
     */

	public function view(string $companyId)
	{
		$corporateIdentity = Media::changeImageFilenameToBase64String(CorporateIdentity::where('companyId', '=', $companyId)->firstOrFail());

		$this->authorize($corporateIdentity);

		return CorporateIdentityResource::make($corporateIdentity)
            ->response()
            ->setStatusCode(Response::HTTP_OK);
	}
}
