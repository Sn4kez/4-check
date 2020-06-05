<?php

namespace App\Http\Resources;

/**
 * @property string $id
 * @property boolean $showCompanyName
 * @property boolean $showCompanyAddress
 * @property boolean $showUsername
 * @property boolean $showPageNumbers
 * @property boolean $showExportDate
 * @property boolean $showVersion
 * @property string $text
 * @property \App\PhoneType $type
 * @property \Illuminate\Support\Carbon $createdAt
 * @property \Illuminate\Support\Carbon $updatedAt
 * @property \Illuminate\Support\Carbon $deletedAt
 */
class ReportSettingsResource extends Resource
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
            'showCompanyName' => $this->showCompanyName,
            'showCompanyAddress' => $this->showCompanyAddress,
            'showUsername' => $this->showUsername,
            'showPageNumbers' => $this->showPageNumbers,
            'showExportDate' => $this->showExportDate,
            'showVersion' => $this->showVersion,
            'text' => $this->text,
            'logoPosition' => $this->logoPosition,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt,
            'deletedAt' => $this->deletedAt,
        ];
    }
}
