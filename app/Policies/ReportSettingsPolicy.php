<?php

namespace App\Policies;

use App\ReportSettings;
use App\User;

class ReportSettingsPolicy extends Policy
{
    /**
     * @var CompanyPolicy
     */
    protected $companyPolicy;

    /**
     * Create a new controller instance.
     *
     * @param CompanyPolicy $companyPolicy
     */
    public function __construct(CompanyPolicy $companyPolicy)
    {
        $this->companyPolicy = $companyPolicy;
    }

    /**
     * Determine whether the user can index the model.
     *
     * @param User $user
     * @param ReportSettings $settings
     * @return bool
     */
    public function index(User $user, ReportSettings $settings)
    {
        if (!$user->isAdmin()) {
            return false;
        }
        return $user->company->is($settings->company);
    }

    /**
     * Determine whether the user can view the model.
     *
     * @param User $user
     * @param ReportSettings $settings
     * @return bool
     */
    public function view(User $user, ReportSettings $settings)
    {
        if (!$user->isAdmin()) {
            return false;
        }
        return $user->company->is($settings->company);
    }

    /**
     * Determine whether the user can update the model.
     *
     * @param User $user
     * @param ReportSettings $settings
     * @return bool
     */
    public function update(User $user, ReportSettings $settings)
    {
        if (!$user->isAdmin()) {
            return false;
        }
        return $user->company->is($settings->company);
    }

    /**
     * Determine whether the user can delete the model.
     *
     * @param User $user
     * @param ReportSettings $settings
     * @return bool
     */
    public function delete(User $user, ReportSettings $settings)
    {
        if (!$user->isAdmin()) {
            return false;
        }
        return $user->company->is($settings->company);
    }
}
