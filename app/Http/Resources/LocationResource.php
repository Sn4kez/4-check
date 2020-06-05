<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

/**
 * @property string $id
 * @property string $name
 * @property string $description
 * @property \App\LocationType $type
 * @property \App\LocationState $state
 * @property string $parentId
 * @property string $parent
 * @property string $street
 * @property string $streetNumber
 * @property string $city
 * @property string $postalCode
 * @property string $province
 * @property \App\Country $country
 * @property \App\Company $company
 * @property $children
 * @property \Illuminate\Support\Carbon $createdAt
 * @property \Illuminate\Support\Carbon $updatedAt
 * @property \Illuminate\Support\Carbon $deletedAt
 */
class LocationResource extends Resource {
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request $request
     * @return array
     */
    public function toArray($request) {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'type' => $this->type->id,
            'typeName' => $this->type->name,
            'state' => $this->state->id,
            'street' => $this->street,
            'streetNumber' => $this->streetNumber,
            'city' => $this->city,
            'postalCode' => $this->postalCode,
            'province' => $this->province,
            'country' => $this->country->id,
            'company' => $this->company->id,
            'children' => $this->children,
            'createdAt' => $this->createdAt,
            'updatedAt' => $this->updatedAt,
            'deletedAt' => $this->deletedAt,
            'parentId' => $this->parentId,
            'parent' => !is_null($this->parentId) ? $this->parent->name : ''
        ];
    }
}
