<?php

namespace App\Http\Resources;

use App\Check;
use App\Checklist;
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
class AuditResource extends Resource {
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request) {
        $result = Check::where('auditId', '=', $this->id)->get();
        $checklist = Checklist::find($this->checklistId);

        if (!is_null($result)) {
            $results = CheckResource::collection($result);
        }

        return [
            'id' => $this->id,
            'company' => $this->companyId,
            'user' => $this->user->id,
            'state' => $this->stateId,
            'executionDue' => $this->executionDue,
            'executionAt' => $this->executionAt,
            'checklist' => is_null($this->checklistId) ? null : $this->checklistId,
            'checklistName' => is_null($checklist) ? null : $checklist->name,
            'checklistDescription' => is_null($checklist) ? null : $checklist->description,
            'checklistPath' => is_null($checklist) ? [] : $checklist->getPaths(),
            'results' => $results,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt,
            'deletedAt' => $this->deletedAt,
        ];
    }
}
