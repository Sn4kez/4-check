<?php

namespace App\Http\Resources;

/**
 * @property string $id
 * @property \App\User|\App\Group $subject
 * @property string $subjectType
 * @property \App\Directory $object
 * @property string $objectType
 * @property boolean $view
 * @property boolean $index
 * @property boolean $update
 * @property boolean $delete
 * @property \Illuminate\Support\Carbon $createdAt
 * @property \Illuminate\Support\Carbon $updatedAt
 * @property \Illuminate\Support\Carbon $deletedAt
 */
class AccessGrantResource extends Resource
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
            'subjectId' => $this->subject->id,
            'subjectType' => $this->subjectType,
            'objectId' => $this->object->id,
            'objectType' => $this->objectType,
            'objectName' => $this->object->name,
            'view' => $this->view,
            'index' => $this->index,
            'update' => $this->update,
            'delete' => $this->delete,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt,
            'deletedAt' => $this->deletedAt,
        ];
    }
}
