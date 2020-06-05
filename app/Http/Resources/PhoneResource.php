<?php

namespace App\Http\Resources;

/**
 * @property string $id
 * @property string $countryCode
 * @property string $nationalNumber
 * @property \App\PhoneType $type
 * @property \Illuminate\Support\Carbon $createdAt
 * @property \Illuminate\Support\Carbon $updatedAt
 * @property \Illuminate\Support\Carbon $deletedAt
 */
class PhoneResource extends Resource
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
            'countryCode' => $this->countryCode,
            'nationalNumber' => $this->nationalNumber,
            'type' => $this->type->id,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt,
            'deletedAt' => $this->deletedAt,
        ];
    }
}
