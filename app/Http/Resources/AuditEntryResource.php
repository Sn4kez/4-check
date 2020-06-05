<?php

namespace App\Http\Resources;

use App\Checkpoint;
use App\Extension;
use App\Section;
use App\Check;
use App\ChecklistEntry;

/**
 * @property string $id
 * @property \App\Checklist|\App\Section|\App\Checkpoint $parent
 * @property \App\Section|\App\Checkpoint|\App\Extension $object
 * @property string $objectType
 * @property \Illuminate\Support\Carbon $createdAt
 * @property \Illuminate\Support\Carbon $updatedAt
 * @property \Illuminate\Support\Carbon $deletedAt
 */
class AuditEntryResource extends Resource
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
        $checks = null;
        $sections = null;

        $object = $objectResource::make($this->object);

        if($this->objectType == 'section') {
            $checks = Check::where('auditId', '=', $this->auditId)->where('sectionId', '=', $object->id)->get();
            $sections = ChecklistEntry::where('objectType', '=', 'section')->where('parentId', '=', $object->id)->get();

            $sections->map(
                function($section){
                    $section['auditId'] = $this->audit->id;
                    return $section;
                }
            );
        }
        return [
            'id' => $this->id,
            'parentId' => $this->parent->id,
            'objectType' => $this->objectType,
            'object' => $object,
            'audit' => $this->auditId,
            'checks' => CheckResource::collection($checks),
            'sections' => AuditEntryResource::collection($sections),
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt,
            'deletedAt' => $this->deletedAt,
        ];
    }
}
