<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\Resource;

class CorporateIdentityResource extends Resource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'company' => $this->company->id,
            'brand_primary' => $this->brand_primary,
            'brand_secondary' => $this->brand_secondary,
            'link_color' => $this->link_color,
            'image' => $this->image
        ];
    }
}
