<?php

namespace App\Http\Resources;

/**
 * @property string $id
 * @property string $name
 * @property \App\Sector $sector
 * @property \App\ReportSettings $reportSettings
 * @property \App\Directory $directory
 * @property \App\CompanySubscription $companySubscription
 * @property \Illuminate\Support\Carbon $createdAt
 * @property \Illuminate\Support\Carbon $updatedAt
 * @property \Illuminate\Support\Carbon $deletedAt
 */
class CompanyResource extends Resource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'sector' => is_null($this->sector) ? null : $this->sector->id,
            'subscription' => $this->companySubscription,
            //'reportSettingsId' => is_null($this->sector) ? null : $this->reportSettings->id,
            'directoryId' => is_null($this->directory) ? null : $this->directory->id,
            'archiveId' => is_null($this->archive) ? null : $this->archive->id,
            'isActive' => is_null($this->isActive) ? null : $this->isActive,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt,
            'deletedAt' => $this->deletedAt,
        ];
    }
}
