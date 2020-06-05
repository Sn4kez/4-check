<?php

namespace App\Http\Resources;

use App\Checklist;
use App\Directory;

/**
 * @property string $id
 * @property \App\Directory $parent
 * @property \App\Directory $object
 * @property string $objectType
 * @property \Illuminate\Support\Carbon $createdAt
 * @property \Illuminate\Support\Carbon $updatedAt
 * @property \Illuminate\Support\Carbon $deletedAt
 */
class DirectoryEntryResource extends Resource
{

    const resourceMap = [
        Directory::class => DirectoryResource::class,
        Checklist::class => ChecklistResource::class,
    ];

    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request)
    {
        $objectResource = self::resourceMap[get_class($this->object)];
        return [
            'id' => $this->id,
            'parentId' => $this->parent->id,
            'objectType' => $this->objectType,
            'object' => $objectResource::make($this->object),
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt,
            'deletedAt' => $this->deletedAt,
        ];
    }
}
