<?php

namespace App\Http\Resources;

/**
 * @property string $id
 * @property string $name
 * @property string $description
 * @property \App\DirectoryEntry $parentEntry
 * @property \App\Company $company
 * @property \Illuminate\Support\Carbon $createdAt
 * @property \Illuminate\Support\Carbon $updatedAt
 * @property \Illuminate\Support\Carbon $deletedAt
 */
class ChecklistResource extends Resource
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
            'description' => $this->description,
            'icon' => $this->icon,
            'parentEntryId' => is_null($this->parentEntry) ? null : $this->parentEntry->id,
            'parentId' => is_null($this->parentEntry) ? null : $this->parentEntry->parent->id,
            'companyId' => is_null($this->company) ? null : $this->company->id,
            'numberQuestions' => $this->numberQuestions,
            'chooseRandom' => $this->chooseRandom,
            'needsApproval' => $this->needsApproval,
            'approvers' => [
                'users' => $this->approvingUsers,
                'groups' => $this->approvingUserGroups,
            ],
            'createdBy' => $this->createdBy,
            'lastUpdatedBy' => $this->lastUpdatedBy,
            'lastUsedBy' => $this->lastUsedBy,
            'usedAt' => $this->usedAt,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt,
            'deletedAt' => $this->deletedAt,
        ];
    }
}
