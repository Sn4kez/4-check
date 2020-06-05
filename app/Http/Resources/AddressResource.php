<?php

namespace App\Http\Resources;

/**
 * @property string $id
 * @property \App\Company $company
 * @property string $line1
 * @property string $line2
 * @property string $city
 * @property string $postalCode
 * @property string $province
 * @property \App\Country $country
 * @property \App\AddressType $type
 * @property \Illuminate\Support\Carbon $createdAt
 * @property \Illuminate\Support\Carbon $updatedAt
 * @property \Illuminate\Support\Carbon $deletedAt
 */
class AddressResource extends Resource
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
            'company' => $this->company->id,
            'line1' => $this->line1,
            'line2' => $this->line2,
            'city' => $this->city,
            'postalCode' => $this->postalCode,
            'province' => $this->province,
            'country' => $this->country->id,
            'type' => $this->type->id,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt,
            'deletedAt' => $this->deletedAt,
        ];
    }
}
