<?php

namespace App\Http\Resources;

/**
 * @property string $id
 * @property string $name
 * @property string $scopeId
 * @property string $scopeType
 * @property \Illuminate\Support\Carbon $createdAt
 * @property \Illuminate\Support\Carbon $updatedAt
 * @property \Illuminate\Support\Carbon $deletedAt
 */
class ScoringSchemeResource extends Resource
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
            'isListed' => $this->isListed,
            'scopeId' => $this->scopeId,
            'scopeType' => $this->scopeType,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt,
            'deletedAt' => $this->deletedAt,
        ];
    }
}
