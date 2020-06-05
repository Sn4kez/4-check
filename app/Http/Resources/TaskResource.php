<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

/**
 * @property string $id
 * @property string $name
 * @property string $description
 * @property boolean $giveNotice
 * @property string $image
 * @property \App\TaskType $type
 * @property \App\TaskPriority $priority
 * @property \App\TaskState $state
 * @property \App\User $createdBy
 * @property \App\User $assignedTo
 * @property \App\Location $location
 * @property \Illuminate\Support\Carbon $assignedAt
 * @property \Illuminate\Support\Carbon $doneBy
 * @property \Illuminate\Support\Carbon $createdAt
 * @property \Illuminate\Support\Carbon $updatedAt
 * @property \Illuminate\Support\Carbon $deletedAt
 */

class TaskResource extends Resource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $location = NULL;
        $type = NULL;
        $priority = NULL;
        $state = NULL;

        if(!is_null($this->location)) {
            $location = $this->location->id;
        }

        if(!is_null($this->type)) {
            $type = $this->type->id;
        }

        if(!is_null($this->priority)) {
            $priority = $this->priority->id;
        }

        if(!is_null($this->state)) {
            $state = $this->state->id;
        }

        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'giveNotice' => $this->giveNotice,
            'doneAt' => $this->doneAt,
            'type' => $type,
            'priority' => $priority,
            'state' => $state,
            'issuer' => $this->issuer->id,
            'assignee' => $this->assignee->id,
            'location' => $location,
            'company' => $this->company->id,
            'image' => $this->image,
            'assignedAt' => $this->assignedAt,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt
        ];
    }
}
