<?php

namespace App\Http\Resources;

use App\Checkpoint;
use App\Extension;
use App\Section;

/**
 * @property string $id
 * @property \App\Checklist|\App\Section|\App\Checkpoint $parent
 * @property \App\Section|\App\Checkpoint|\App\Extension $object
 * @property string $objectType
 * @property \Illuminate\Support\Carbon $createdAt
 * @property \Illuminate\Support\Carbon $updatedAt
 * @property \Illuminate\Support\Carbon $deletedAt
 */
class ChecklistEntryResource extends Resource
{
    const resourceMap = [
        Section::class => SectionResource::class,
        Checkpoint::class => CheckpointResource::class,
        Extension::class => ExtensionResource::class,
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
