<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class InspectionPlanResource extends Resource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'monday' => $this->monday,
            'tuesday' => $this->tuesday,
            'wednesday' => $this->wednesday,
            'thursday' => $this->thursday,
            'friday' => $this->friday,
            'saturday' => $this->saturday,
            'sunday' => $this->sunday,
            'type' => $this->type,
            'factor' => $this->factor,
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
            'startTime' => $this->startTime,
            'endTime' => $this->endTime,
            'user' => $this->user->id,
            'checklist' => $this->checklistId,
            'company' => $this->companyId,
        ];
    }
}