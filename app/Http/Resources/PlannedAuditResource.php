<?php

namespace App\Http\Resources;

use App\Check;
use App\CheckResoruce;

/**
 * @property string $id
 * @property \App\Company $company
 * @property \App\User $user
 * @property \App\Checklist $checklist
 * @property \App\AuditState $state
 * @property \Illuminate\Support\Carbon $createdAt
 * @property \Illuminate\Support\Carbon $updatedAt
 * @property \Illuminate\Support\Carbon $deletedAt
 */
class PlannedAuditResource extends Resource {
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request) {
        return [
            'id' => $this->id,
            'date' => $this->date,
            'startTime' => $this->startTime,
            'endTime' => $this->endTime,
            'checklist' => is_null($this->checklist) ? null : $this->checklist->id,
            'checklistName' => is_null($this->checklist) ? null : $this->checklist->name,
            'checklistDescription' => is_null($this->checklist) ? null : $this->checklist->description,
            'checklistPath' => is_null($this->checklist) ? [] : $this->checklist->getPaths(),
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt,
            'deletedAt' => $this->deletedAt,
        ];
    }
}
