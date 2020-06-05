<?php

namespace App\Http\Resources;

use App\Checklist;
use App\ArchiveDirectory;

/**
 * @property string $id
 * @property \App\ArchiveDirectory $parent
 * @property \App\ArchiveDirectory $object
 * @property string $objectType
 * @property \Illuminate\Support\Carbon $createdAt
 * @property \Illuminate\Support\Carbon $updatedAt
 * @property \Illuminate\Support\Carbon $deletedAt
 */
class ArchiveEntryResource extends Resource
{

    const resourceMap = [
        ArchiveDirectory::class => ArchiveDirectoryResource::class,
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
