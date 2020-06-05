<?php

namespace App\Http\Controllers;

use App\Company;
use App\Http\Resources\ReportSettingsResource;
use App\ReportSettings;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ReportSettingsController extends Controller
{
    /**
     * @var ReportSettings
     */
    protected $reportSettings;

    /**
     * @var Company
     */
    protected $company;

    /**
     * Create a new controller instance.
     *
     * @param ReportSettings $reportSettings
     * @param Company $company
     */
    public function __construct(ReportSettings $reportSettings, Company $company)
    {
        $this->reportSettings = $reportSettings;
        $this->company = $company;
    }

    /**
     * View report settings.
     *
     * @param string $reportSettingsId
     * @return \Illuminate\Http\JsonResponse
     */
    public function view(string $reportSettingsId)
    {
        /* @var ReportSettings $reportSettings */
        $reportSettings = $this->reportSettings->findOrFail($reportSettingsId);
        $this->authorize($reportSettings);
        return ReportSettingsResource::make($reportSettings)
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    /**
     * View a company's report settings.
     *
     * @param string $companyId
     * @return \Illuminate\Http\JsonResponse
     */
    public function viewCompany(string $companyId)
    {
        /* @var Company $company */
        $company = $this->company->findOrFail($companyId);
        $this->authorize('view', $company->reportSettings);
        return ReportSettingsResource::make($company->reportSettings)
            ->response()
            ->setStatusCode(Response::HTTP_OK);
    }

    /**
     * Update report settings.
     *
     * @param Request $request
     * @param string $reportSettingsId
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, string $reportSettingsId)
    {
        /* @var ReportSettings $reportSettings */
        $reportSettings = $this->reportSettings->findOrFail($reportSettingsId);
        $this->authorize($reportSettings);
        $data = $this->validate($request, ReportSettings::rules('update'));
        $reportSettings->update($data);
        return response('', Response::HTTP_NO_CONTENT);
    }

    /**
     * Update report settings.
     *
     * @param Request $request
     * @param string $companyId
     * @return \Illuminate\Http\JsonResponse
     */
    public function updateCompany(Request $request, string $companyId)
    {
        /* @var Company $company */
        $company = $this->company->findOrFail($companyId);
        $this->authorize('update', $company->reportSettings);
        $data = $this->validate($request, ReportSettings::rules('update'));
        $company->reportSettings->update($data);
        return response('', Response::HTTP_NO_CONTENT);
    }
}
